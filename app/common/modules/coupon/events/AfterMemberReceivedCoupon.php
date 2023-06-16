<?php
/**
 * Created by PhpStorm.
 * User: shenyang
 * Date: 2018/12/28
 * Time: 12:03 AM
 */

namespace app\common\modules\coupon\events;


use app\common\events\Event;
use app\common\models\MemberCoupon;

/**
 * 用户收到优惠券后
 * Class AfterMemberReceivedCoupon
 * @package app\common\modules\coupon\events
 */
class AfterMemberReceivedCoupon extends Event
{
    /**
     * @var MemberCoupon
     */
    private $memberCoupon;
    private $couponTotal;

    public function __construct(MemberCoupon $memberCoupon,$couponTotal=1)
    {
        $this->memberCoupon = $memberCoupon;
        $this->couponTotal = $couponTotal;
    }

    public function memberCoupon()
    {
        return $this->memberCoupon;
    }

    public function couponTotal()
    {
        return $this->couponTotal;
    }
}