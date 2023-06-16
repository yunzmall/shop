<?php

namespace app\common\models;

use app\common\traits\CreateOrderSnTrait;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class MemberCoupon
 * @package app\common\models
 * @property Coupon belongsToCoupon
 * @property int used
 * @property int coupon_id
 * @property Carbon get_time
 * @property int id
 * @property int uid
 * @property Member member
 */
class MemberCoupon extends BaseModel
{
    use CreateOrderSnTrait;
    use SoftDeletes;

    public $table = 'yz_member_coupon';

    public $timestamps = false;

    public $dates = ['deleted_at'];


    protected $casts = ['get_time' => 'date'];

    protected $guarded = [];

    protected $appends = ['time_start', 'time_end', 'timestamp_end'];
    public $selected;
    protected $hidden = ['uniacid', 'get_type', 'send_uid', 'order_sn', 'back', 'back_time', 'deleted_at'];


    /**
     * 定义字段名
     * @return array
     */
    public function atributeNames()
    { //todo typo
        return [
            'uniacid' => '公众号 ID',
            'uid' => '用户 ID',
            'coupon_id' => '优惠券 ID',
            'get_type' => '获取优惠券的方式',
            'used' => '是否已经使用',
            'use_time' => '使用优惠券的时间',
            'get_time' => '获取优惠券的时间',
            'send_uid' => '手动发放优惠券的操作人员的 uid',
            'order_sn' => '使用优惠券的订单号',
            'back' => '返现',
            'back_time' => '返现时间',
        ];
    }

    public function getTimeStartAttribute()
    {
        if ($this->belongsToCoupon->time_limit == false) {
            $result = $this->get_time;
        } else {
            $result = $this->belongsToCoupon->time_start;
        }
        return $result->toDateString();
    }

    public function getTimeEndAttribute()
    {
        if ($this->belongsToCoupon->time_limit == false) {
            if ($this->belongsToCoupon->time_days == false) {
                $result = '不限时间';
            } else {
                $result = $this->get_time->addDays($this->belongsToCoupon->time_days);
            }
        } else {
            $result = $this->belongsToCoupon->time_end;
        }
        if ($result instanceof Carbon) {
            $result = $result->toDateString();
        }
        return $result;
    }

    public function getTimestampEndAttribute()
    {
        if ($this->belongsToCoupon->time_limit == false) {
            if ($this->belongsToCoupon->time_days == false) {
                $result = '不限时间';
            } else {
                $result = Carbon::createFromTimestamp($this->getOriginal('get_time'))->addDays($this->belongsToCoupon->time_days);
            }
        } else {
            $result = Carbon::createFromTimestamp($this->belongsToCoupon->getOriginal('time_end'));
        }
        if ($result instanceof Carbon) {
            $result = $result->toDateTimeString();
        }
        return $result;
    }

    /*
     * 字段规则
     * @return array */
    public function rules()
    {
        return [
            'uniacid' => 'required|integer',
            'uid' => 'required|integer',
            'coupon_id' => 'required|integer',
            'get_type' => 'integer|between:0,2',
            'used' => 'integer|between:0,1',
            'use_time' => 'numeric',
            'get_time' => 'required|numeric',
            'send_uid' => 'integer',
            'order_sn' => 'string',
//            'back'  => '',
            'back_time' => 'numeric',
        ];
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function member(){
        return $this->belongsTo(Member::class,'uid');
    }
    public function belongsToCoupon()
    {
        return $this->belongsTo('app\frontend\modules\coupon\models\OrderCoupon', 'coupon_id', 'id');
    }

    public function belongsToCommonCoupon()
    {
        return $this->belongsTo('\app\common\models\Coupon', 'coupon_id', 'id');
    }

    public function scopeCoupons(Builder $order_builder, $params)
    {
        $order_builder->with([
                'belongsToCoupon' => function (Builder $query) {
                    $query->where('status', 0);
                }
            ]
        )->where('used', 0);
    }

    public static function getMemberCoupon(Member $MemberModel, $param = [])
    {
        return static::with(['belongsToCoupon' => function (Builder $query) use ($param) {
            if (isset($param['coupon']['coupon_method'])) {
                //$query->where('coupon_method', $param['coupon']['coupon_method']);
            }
            return $query->where('status', 0);
        }])->where('member_id', $MemberModel->uid)->where('used', 0);
    }

    public static function getExpireCoupon()
    {
        $model = self::uniacid();
        $model->where('used', 0);
        return $model;
    }

    public function save(array $options = [])
    {
        // todo 紧急修复优惠券bug 保存和使用bug
        unset($this->valid);
        unset($this->checked);
        //dd(debug_backtrace());
        return parent::save($options); // TODO: Change the autogenerated stub
    }

    public static function search($search)
    {
        $model = self::uniacid();
       if ($search['member_id']) {
            $model->where('yz_member_coupon.uid', $search['member_id']);
        }
        if ($search['member']) {
            $model->join('mc_members', 'yz_member_coupon.uid', 'mc_members.uid')
                ->where(function ($query) use ($search) {
                    $query->where('mc_members.mobile', 'like', '%'.$search['member'].'%')
                        ->orWhere('mc_members.realname', 'like', '%'.$search['member'].'%')
                        ->orWhere('mc_members.nickname', 'like', '%'.$search['member'].'%');
                });
        }
        if ($search['coupon_name']) {
            $model->join('yz_coupon', 'yz_member_coupon.coupon_id','yz_coupon.id')
                ->where('yz_coupon.name', 'like', '%'.$search['coupon_name'].'%');
        }
        return $model;
    }
}
