<?php
/**
 * Created by PhpStorm.
 * Author: 芸众商城 www.yunzshop.com
 * Date: 2017/4/11
 * Time: 下午6:27
 */

namespace app\common\services\finance;

use app\common\facades\Setting;
use app\common\models\Order;
use app\common\models\OrderGoods;
use Yunshop\StoreCashier\common\models\CashierGoods;
use Yunshop\StoreCashier\common\models\StoreGoods;
use Yunshop\StoreCashier\common\models\StoreSetting;

class CalculationPointService
{
    /**
     * @param OrderGoods $orderGoods
     * @return array
     */
    public static function calculationPointByGoods($orderGoods)
    {
        $point_set = Setting::get('point.set');


        $order = Order::find($orderGoods->order_id);

        $order_set = $order->orderSettings->where('key', 'point')->first();

        if ($order_set && $order_set->value['set']['give_point']) {
            $point_set['give_point'] = $order_set->value['set']['give_point'] . '%';
        }


        $point_data = [];
        //todo 如果等于0  不赠送积分
        if (isset($orderGoods->hasOneGoods->hasOneSale) && $orderGoods->hasOneGoods->hasOneSale->point !== '' && intval($orderGoods->hasOneGoods->hasOneSale->point) === 0) {
            return $point_data;
        }


        //todo 如果不等于空，按商品设置赠送积分，否则按统一设置赠送积分
        if (isset($orderGoods->hasOneGoods->hasOneSale) && !empty($orderGoods->hasOneGoods->hasOneSale->point)) {
            if (strexists($orderGoods->hasOneGoods->hasOneSale->point, '%')) {
                $point_data['point'] = floatval(str_replace('%', '', $orderGoods->hasOneGoods->hasOneSale->point) / 100 * static::goodsProfit($point_set, $order, $orderGoods));
            } else {
                $point_data['point'] = $orderGoods->hasOneGoods->hasOneSale->point * $orderGoods->total;
            }
            $point_data['remark'] = '购买商品[' . $orderGoods->hasOneGoods->title . '(比例:' . $orderGoods->hasOneGoods->hasOneSale->point . ')]赠送[' . $point_data['point'] . ']积分！';
        } else if (!empty($point_set['give_point'] && $point_set['give_point'])) {
            if (strexists($point_set['give_point'], '%')) {
                $point_data['point'] = floatval(str_replace('%', '', $point_set['give_point']) / 100 * static::goodsProfit($point_set, $order, $orderGoods));
            } else {
                $point_data['point'] = $point_set['give_point'] * $orderGoods->total;
            }
            $point_data['remark'] = "购买商品[统一设置(比例:" . $point_set['give_point'] . ")]赠送[{$point_data['point']}]积分！";
        }
        \Log::debug("个人会员奖励积分kk：", $point_data);
        return $point_data;
    }

    //订单商品利润
    private static function goodsProfit($point_set, $order, $orderGoods)
    {
        if ($point_set['give_type'] == 1) {
            if (app('plugins')->isEnabled('store-cashier') && in_array($order->plugin_id, [31, 32])) {
                return static::storeProfit($orderGoods);
            }
            return static::generalProfit($orderGoods);
        }
        return $orderGoods->payment_amount;
    }

    //门店收银台订单利润计算
    private static function storeProfit($orderGoods)
    {
        $cashier_good = CashierGoods::select('id', 'goods_id', 'shop_commission')->where('goods_id', $orderGoods->goods_id)->first();
        $store_good = StoreGoods::select('id', 'store_id', 'goods_id')->where('goods_id', $orderGoods->goods_id)->first();
        if ($cashier_good) {
            $profit = proportionMath($orderGoods->payment_amount, $cashier_good->shop_commission);
        } elseif ($store_good) {
            $store_setting = StoreSetting::where('store_id', $store_good->store_id)->where('key', 'store')->first();
            $shop_commission = (integer)$store_setting->value['shop_commission'];
            $profit = proportionMath($orderGoods->payment_amount, $shop_commission);
        } else {
            $profit = 0;
        }
        return $profit;
    }

    //普通订单利润计算
    private static function generalProfit($orderGoods)
    {
        $profit = $orderGoods->payment_amount - $orderGoods->goods_cost_price;

        return $profit > 0 ? $profit : 0;
    }

    public static function calculationPointByOrder($order_model)
    {
        $point_set = Setting::get('point.set');
        $point_data = [];
        if (isset($point_set['enoughs'])) {
            foreach (collect($point_set['enoughs'])->sortBy('enough') as $enough) {
                $orderPrice = $order_model->price - $order_model->dispatch_price - $order_model->fee_amount;
                if ($orderPrice >= $enough['enough'] && $enough['give'] > 0) {
                    $point_price = $enough['enough'];
                    $point_data['point'] = $enough['give'];
                    $point_data['remark'] = '订单[' . $order_model->order_sn . ']消费满[' . $enough['enough'] . ']元赠送[' . $enough['give'] . ']积分';
                    if ($point_set['point_award_type'] == 1) {
                        $point_data['point'] = $orderPrice * $enough['give'] / 100;
                        $point_data['remark'] = '订单[' . $order_model->order_sn . ']消费满[' . $enough['enough'] . ']元赠送[' . $enough['give'] . '%]积分';
                    }
                }
            }
        }
        if (!empty($point_set['enough_money']) && !empty($point_set['enough_point'])) {
            $orderPrice = $order_model->price - $order_model->dispatch_price - $order_model->fee_amount;
            if ($orderPrice >= $point_set['enough_money'] && $point_set['enough_point'] > 0 && $point_set['enough_money'] > $point_price) {
                $point_data['point'] = $point_set['enough_point'];
                $point_data['remark'] = '订单[' . $order_model->order_sn . ']消费满[' . $point_set['enough_money'] . ']元赠送[' . $point_data['point'] . ']积分';

                if ($point_set['point_award_type'] == 1) {
                    $point_data['point'] = $orderPrice * $point_set['enough_point'] / 100;
                    $point_data['remark'] = '订单[' . $order_model->order_sn . ']消费满[' . $point_set['enough_money'] . ']元赠送[' . $point_data['point'] . '%]积分';
                }
            }
        }

        return $point_data;
    }
}