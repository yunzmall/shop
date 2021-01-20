<?php
/****************************************************************
 * Author:  king -- LiBaoJia
 * Date:    2020/7/9 2:25 PM
 * Email:   livsyitian@163.com
 * QQ:      995265288
 * IDE:     PhpStorm
 * User:    芸众商城 www.yunzshop.com
 ****************************************************************/


namespace app\common\models\point;


use app\common\models\BaseModel;
use app\common\observers\point\BindMobileAwardObserver;

class BindMobileAward extends BaseModel
{
    protected $table = 'yz_bind_mobile_award_point';

    protected $guarded = [];

    public static function boot()
    {
        parent::boot();
        self::observe(new BindMobileAwardObserver());
    }

    /**
     * @param int $memberId
     *
     * @return bool
     */
    public static function isAwarded($memberId)
    {
        return !!static::where('member_id', $memberId)->first();
    }

    /**
     * @param int $memberId
     * @param float $point
     *
     * @return bool
     */
    public static function awardMember($memberId, $point)
    {
        return (new static())->fill([
            'uniacid'   => \YunShop::app()->uniacid,
            'point'     => $point,
            'member_id' => $memberId
        ])->save();
    }
}
