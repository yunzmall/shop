<?php
/**
 * Created by PhpStorm.
 * User: shenyang
 * Date: 2018/11/23
 * Time: 11:01 AM
 */

namespace app\common\modules\order;


use app\common\models\Order;
use app\framework\Database\Eloquent\Collection;
use app\frontend\modules\coupon\services\CouponService;
use app\frontend\modules\coupon\services\models\Coupon;
use app\frontend\modules\order\models\PreOrder;

class OrderCollection extends Collection
{
    public function getMemberCoupons()
    {
        $memberCoupons = $this->map(function (PreOrder $order) {
            //blank not discount
            if ($order->isDiscountDisable()) {
                return collect([]);
            }
            $couponService = new CouponService($order);
            $coupons = $couponService->getOptionalCoupons();
            $memberCoupons = $coupons->map(function (Coupon $coupon) {
                $coupon->getMemberCoupon()->belongsToCoupon->setDateFormat('Y-m-d');
                $result = $coupon->getMemberCoupon();
                $result->expired_at =  $coupon->getExpiredAt();
                return $result;
            });
            return $memberCoupons;
        })->collapse()->unique('id')->values(); //分单时去除重复的会员优惠卷记录

        $memberCoupons->sortByDesc(function($coupon) {
            $sort = $coupon->coupon_id*100000 + substr($coupon->expired_at,-6) ;
            return $sort;
        });
        return $memberCoupons;
    }
}