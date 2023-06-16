<?php
/**
 * Created by PhpStorm.
 * User: king
 * Date: 2018/10/22
 * Time: 下午12:00
 */

namespace app\backend\modules\point\models;


use app\common\scopes\UniacidScope;

class RechargeModel extends \app\common\models\point\RechargeModel
{
    protected $appends = ['type_name'];

    /**
     * Payment translation set.
     *
     * @var array
     */
    private static $typeComment = [
        0 => "后台充值",
    ];

    public static function boot()
    {
        parent::boot();
        self::addGlobalScope(new UniacidScope);
    }

    /**
     * Gets the value of the additional field type_name.
     *
     * @return string
     */
    public function getTypeNameAttribute()
    {
        return static::getTypeNameComment($this->attributes['type']);
    }

    /**
     * Gets the value of the additional field type_name.
     *
     * @param $attributes
     * @return string
     */
    public function getTypeNameComment($attributes)
    {
        return isset(static::$typeComment[$attributes]) ? static::$typeComment[$attributes] : "其他支付";
    }

    /**
     * @param static $query
     * @param array $search
     */
    public function scopeSearch($query, $search)
    {
        $query->searchMember($search);

        if ($search['order_sn']) $query->where('order_sn', 'like', $search['order_sn'] . '%');

        if (($search['time']['start'] > 0 && $search['time']['end'] > 0)) {
            $query->whereBetween('created_at', [$search['time']['start'], $search['time']['end']]);
        }
    }

    /**
     * @param static $query
     * @param array $search
     */
    public function scopeSearchMember($query, $search)
    {
        if ($search['member']) {
            $query->whereHas('member', function ($query) use ($search) {
                $query->search($search);
            });
        }
    }

    public function scopeWithMember($query)
    {
        return $query->with(['member' => function ($query) {
            return $query->select('uid', 'nickname', 'realname', 'mobile', 'avatar');
        }]);
    }

    /**
     * 字段规则
     *
     * @return array
     */
    public function rules()
    {
        return [
            'uniacid'   => "required",
            'member_id' => "required",
            'money'     => 'numeric|regex:/^[\-\+]?\d+(?:\.\d{1,2})?$/|max:9999999999',
            'type'      => 'required',
            'order_sn'  => 'required',
            'status'    => 'required',
            'remark'    => 'max:50'
        ];
    }
}
