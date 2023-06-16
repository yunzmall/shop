<?php
/**
 * Created by PhpStorm.
 * User: CJVV
 * Date: 2021/3/17
 * Time: 16:36
 */

namespace app\common\events\coupon;

/**
 * 优惠券到期
 * Class CouponExpireEvent
 * @package app\common\events\order
 */
class CouponExpireEvent
{
    protected $time_end;
    protected $couponId;
    protected $memberId;

    public function __construct($couponId,$memberId,$time_end)
    {
        $this->couponId = $couponId;
        $this->memberId = $memberId;
        $this->time_end = $time_end;
    }

    public function getMember()
    {
        return $this->memberId;
    }

    public function getCoupon()
    {
        return $this->couponId;
    }

    public function getExpireTime()
    {
        return $this->time_end;
    }

}