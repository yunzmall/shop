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

class OrderCollection extends Collection
{
    public function getMemberCoupons()
    {

        $memberCoupons = $this->map(function (Order $order) {
            $couponService = new CouponService($order);
            $coupons = $couponService->getOptionalCoupons();

            $memberCoupons = $coupons->map(function (Coupon $coupon) {

                $coupon->getMemberCoupon()->belongsToCoupon->setDateFormat('Y-m-d');
                $result = $coupon->getMemberCoupon();

                $result->expired_at =  $coupon->getExpiredAt();
                return $result;
            });
            return $memberCoupons;
        })->collapse();



       $memberCoupons->sortByDesc(function($coupon){

            $sort = $coupon->coupon_id*100000 + substr($coupon->expired_at,-6) ;

            return $sort;
        });



        return $memberCoupons;

    }
}