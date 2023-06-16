<?php
/**
 * Created by PhpStorm.
 * User: 17812
 * Date: 2020/8/28
 * Time: 11:45
 */

namespace app\common\models\coupon;


use app\common\models\BaseModel;
use app\common\models\Coupon;
use app\common\models\Member;

class CouponUseLog extends BaseModel
{
    /**
     * @var string
     */
    protected $table = "yz_coupon_use_log";

    /**
     * @var array
     */
    protected $guarded = [''];

    const TYPE_SHOPPING = 1;
    const TYPE_TRANSFER = 2;
    const TYPE_WRITE_OFF = 3;
    const TYPE_SHARE = 4;
    const TYPE_CART_SHARE = 5;
    const TYPE_BACKEND_DEL = 6;

    /**
     * @var array
     */
    protected $appends = ['type_name'];

    /**
     * @var array
     */
    public static $TypeComment = [
        self::TYPE_SHOPPING => '购物使用',
        self::TYPE_TRANSFER => '会员转赠',
        self::TYPE_WRITE_OFF => '核销',
        self::TYPE_SHARE => '分享',
        self::TYPE_CART_SHARE => '购物车分享',
        self::TYPE_CART_SHARE => '购物车分享',
        self::TYPE_BACKEND_DEL => '后台作废',
    ];

    public function getTypeNameAttribute()
    {
        return self::$TypeComment[$this->attributes['type']] ?: '异常';
    }

    public static function getRecords($search){
        $merModel=self::uniacid()->with(['belongsToMember','hasOneCoupon']);

        if (!empty($search['member_id'])) {
            $merModel->whereHas('belongsToMember', function ($query) use ($search) {
                return $query->where('member_id', $search['member_id']);
            });
        }

        if (!empty($search['member'])) {
            $merModel->whereHas('belongsToMember', function ($query) use ($search) {
                return $query->searchLike($search['member']);
            });
        }

        if (!empty($search['coupon_name'])) {
            $merModel->whereHas('hasOneCoupon', function ($query) use ($search) {
                return $query->where('name', 'like', '%'.$search['coupon_name'].'%');
            });
        }

        if (!empty($search['use_type'])) {
            $merModel->where('type',$search['use_type']);
        }

        if ($search['is_time']) {
            if ($search['time']) {
                $range = [strtotime($search['time']['start']), strtotime($search['time']['end'])];
                $merModel->whereBetween('created_at', $range);
            }
        }

        $merModel->orderBy('created_at', 'desc');

        return $merModel;

    }


    public function belongsToMember()
    {
        return $this->belongsTo(Member::class, 'member_id', 'uid');
    }

    public function hasOneCoupon()
    {
        return $this->hasOne(Coupon::class, 'id', 'coupon_id');
    }
}