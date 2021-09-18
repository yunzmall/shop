<?php

namespace app\frontend\modules\coupon\models;


use app\common\exceptions\AppException;
use app\common\exceptions\ShopException;

class MemberCoupon extends \app\common\models\MemberCoupon
{
    public $table = 'yz_member_coupon';

    const USED = 1;
    const NOT_USED = 0;

    //获取指定用户名下的优惠券
    public static function getCouponsOfMember($memberId,$search_type = '')
    {
        $coupons = static::uniacid()->with(['belongsToCoupon' => function ($query) {
            return $query->select(['id', 'name', 'coupon_method', 'deduct', 'discount', 'enough', 'use_type', 'category_ids', 'categorynames',
                'goods_ids', 'goods_names', 'storeids', 'storenames', 'time_limit', 'time_days', 'time_start', 'time_end', 'total',
                'money', 'credit', 'plugin_id', 'use_conditions']);
        }]);
        $coupons->whereHas('belongsToCoupon', function ($q) use ($search_type) {
            switch ($search_type) {
                case Coupon::TYPE_SHOP:
                    $q->where('use_type', Coupon::COUPON_SHOP_USE);
                    break;
                case Coupon::TYPE_STORE:
                    if (app('plugins')->isEnabled('store-cashier')) {
                        $q->whereIn('use_type', [Coupon::COUPON_STORE_USE, Coupon::COUPON_SINGLE_STORE_USE]);
                    }
                    break;
                case Coupon::TYPE_HOTEL:
                    if (app('plugins')->isEnabled('hotel')) {
                        $q->whereIn('use_type', [Coupon::COUPON_ONE_HOTEL_USE, Coupon::COUPON_MORE_HOTEL_USE]);
                    }
                    break;
                case Coupon::TYPE_GOODS:
                    $q->where('use_type', Coupon::COUPON_GOODS_USE);
                    break;
                case Coupon::TYPE_CATE:
                    $q->where('use_type', Coupon::COUPON_CATEGORY_USE);
                    break;
                case Coupon::TYPE_EXCHANGE:
                    $q->where('use_type', Coupon::COUPON_EXCHANGE_USE);
                    break;
                case Coupon::TYPE_GOOD_AND_STORE:
                    $q->where('use_type', Coupon::COUPON_GOODS_AND_STORE_USE);
                    break;
                case Coupon::TYPE_MONEY_OFF:
                    $q->where('coupon_method', Coupon::COUPON_MONEY_OFF);
                    break;
                case Coupon::TYPE_DISCOUNT:
                    $q->where('coupon_method', Coupon::COUPON_DISCOUNT);
                    break;
                case Coupon::TYPE_OVERDUE:
                    $q->where('near_expiration', 1);
                    break;
            }
            if (!app('plugins')->isEnabled('store-cashier')) {
                $q->whereNotIn('use_type', [Coupon::COUPON_STORE_USE, Coupon::COUPON_SINGLE_STORE_USE]);
            }
            if (!app('plugins')->isEnabled('hotel')) {
                $q->whereNotIn('use_type', [Coupon::COUPON_ONE_HOTEL_USE, Coupon::COUPON_MORE_HOTEL_USE]);
            }
        });
        $coupons->where('uid', $memberId)
            ->select(['id', 'coupon_id', 'used', 'use_time', 'get_time','near_expiration'])
            ->orderBy('get_time', 'desc');
        return $coupons;
    }

    public static function getExchange($memberId, $pluginId)
    {
        $coupons = static::uniacid()
            ->whereHas('belongsToCoupon',function ($query) use($pluginId) {
                return $query->where('plugin_id',$pluginId)->where('use_type',8);
            })
            ->with(['belongsToCoupon' => function ($query) {
                return $query->select(['id', 'name', 'coupon_method', 'deduct', 'discount', 'enough', 'use_type',
                    'goods_ids', 'goods_names','time_limit', 'time_days', 'time_start', 'time_end', 'total',
                    'money', 'credit', 'plugin_id']);
            }])
            ->where('used', '=', 0)
            ->where('is_member_deleted', 0)
            ->where('uid', $memberId)
            ->select(['id', 'coupon_id', 'used','use_time', 'get_time'])
            ->orderBy('get_time', 'desc');
        return $coupons;
    }


}
