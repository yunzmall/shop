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

		$memberCoupons = $memberCoupons->map(function ($coupon) {
			$coupon->belongsToCoupon->addHidden('uniacid');
			$coupon->belongsToCoupon->addHidden('cat_id');
			$coupon->belongsToCoupon->addHidden('get_type');
			$coupon->belongsToCoupon->addHidden('level_limit');
			$coupon->belongsToCoupon->addHidden('get_max');
			$coupon->belongsToCoupon->addHidden('get_limit_max');
			$coupon->belongsToCoupon->addHidden('get_limit_type');
			$coupon->belongsToCoupon->addHidden('use_type');
			$coupon->belongsToCoupon->addHidden('return_type');
			$coupon->belongsToCoupon->addHidden('bgcolor');
			$coupon->belongsToCoupon->addHidden('coupon_type');
			$coupon->belongsToCoupon->addHidden('time_limit');
			$coupon->belongsToCoupon->addHidden('time_days');
			$coupon->belongsToCoupon->addHidden('time_start');
			$coupon->belongsToCoupon->addHidden('time_end');
			$coupon->belongsToCoupon->addHidden('back_type');
			$coupon->belongsToCoupon->addHidden('back_money');
			$coupon->belongsToCoupon->addHidden('back_credit');
			$coupon->belongsToCoupon->addHidden('back_redpack');
			$coupon->belongsToCoupon->addHidden('back_when');
			$coupon->belongsToCoupon->addHidden('thumb');
			$coupon->belongsToCoupon->addHidden('desc');
			$coupon->belongsToCoupon->addHidden('resp_desc');
			$coupon->belongsToCoupon->addHidden('resp_thumb');
			$coupon->belongsToCoupon->addHidden('resp_title');
			$coupon->belongsToCoupon->addHidden('resp_url');
			$coupon->belongsToCoupon->addHidden('credit');
			$coupon->belongsToCoupon->addHidden('usecredit2');
			$coupon->belongsToCoupon->addHidden('remark');
			$coupon->belongsToCoupon->addHidden('descnoset');
			$coupon->belongsToCoupon->addHidden('display_order');
			$coupon->belongsToCoupon->addHidden('supplier_uid');
			$coupon->belongsToCoupon->addHidden('getcashier');
			$coupon->belongsToCoupon->addHidden('cashiersids');
			$coupon->belongsToCoupon->addHidden('cashiersnames');
			$coupon->belongsToCoupon->addHidden('category_ids');
			$coupon->belongsToCoupon->addHidden('categorynames');
			$coupon->belongsToCoupon->addHidden('goods_names');
			$coupon->belongsToCoupon->addHidden('storenames');
			$coupon->belongsToCoupon->addHidden('member_tags_ids');
			$coupon->belongsToCoupon->addHidden('member_tags_names');
			$coupon->belongsToCoupon->addHidden('getstore');
			$coupon->belongsToCoupon->addHidden('getsupplier');
			$coupon->belongsToCoupon->addHidden('supplierids');
			$coupon->belongsToCoupon->addHidden('suppliernames');
			$coupon->belongsToCoupon->addHidden('createtime');
			$coupon->belongsToCoupon->addHidden('created_at');
			$coupon->belongsToCoupon->addHidden('updated_at');
			$coupon->belongsToCoupon->addHidden('deleted_at');
			$coupon->belongsToCoupon->addHidden('is_complex');
			$coupon->belongsToCoupon->addHidden('plugin_id');
			$coupon->belongsToCoupon->addHidden('use_conditions');
			$coupon->belongsToCoupon->addHidden('is_integral_exchange_coupon');
			$coupon->belongsToCoupon->addHidden('exchange_coupon_integral');
			$coupon->belongsToCoupon->addHidden('content');
			$coupon->belongsToCoupon->addHidden('coupon_type_name');

			return $coupon;
		});

        return $memberCoupons;
    }
}