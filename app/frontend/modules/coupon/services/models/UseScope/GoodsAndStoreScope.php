<?php
/**
 * Created by PhpStorm.
 * User: weifeng
 * Date: 2021-04-20
 * Time: 10:06
 *
 *    .--,       .--,
 *   ( (  \.---./  ) )
 *    '.__/o   o\__.'
 *       {=  ^  =}
 *        >  -  <
 *       /       \
 *      //       \\
 *     //|   .   |\\
 *     "'\       /'"_.-~^`'-.
 *        \  _  /--'         `
 *      ___)( )(___
 *     (((__) (__)))     梦之所想,心之所向.
 */

namespace app\frontend\modules\coupon\services\models\UseScope;


use app\common\exceptions\AppException;
use app\common\models\Goods;
use app\common\models\Store;
use app\common\modules\orderGoods\models\PreOrderGoods;
use app\frontend\modules\orderGoods\models\PreOrderGoodsCollection;
use Yunshop\StoreCashier\common\models\CashierGoods;
use Yunshop\StoreCashier\common\models\StoreGoods;

class GoodsAndStoreScope extends CouponUseScope
{
    public function _getOrderGoodsOfUsedCoupon()
    {
        $order_goods = new PreOrderGoodsCollection();
        if (!app('plugins')->isEnabled('store-cashier')) {
            return $order_goods;
        }
        $order_goods = $this->coupon->getPreOrder()->orderGoods->filter(function ($orderGoods) {
            $use_conditions = unserialize($this->coupon->getMemberCoupon()->belongsToCoupon->use_conditions);
            if (empty($use_conditions)) {
                return false;
            }
            if ($use_conditions['is_all_store'] == 1) {
                $store_ids = Store::uniacid()->pluck('id')->all();
            } else {
                $store_ids = $use_conditions['store_ids'];
            }
            $appoint_store_good_ids = StoreGoods::whereIn('store_id', $store_ids)->pluck('goods_id')->all(); //指定门店（商品id）
            $cashier_good_ids = Store::uniacid()->whereIn('id', $store_ids)->pluck('cashier_id')->all(); //收银台id（商品id）
            if ($use_conditions['is_all_good'] == 1) {
                $appoint_good_ids = Goods::uniacid()->where('plugin_id', 0)->pluck('id')->all();
            } else {
                $appoint_good_ids = $use_conditions['good_ids']; //指定商品id
            }
            $all_good_ids = array_merge($appoint_store_good_ids, $appoint_good_ids, $cashier_good_ids);
            return in_array($orderGoods->goods_id, $all_good_ids);
        });
        if ($order_goods->unique('is_plugin')->count() > 1) {
            throw new AppException('自营商品与第三方商品不能共用一张优惠券');
        }
        return $order_goods;
    }
}