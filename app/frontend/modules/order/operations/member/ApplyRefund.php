<?php
/**
 * Created by PhpStorm.
 * User: shenyang
 * Date: 2018/8/2
 * Time: 下午5:51
 */

namespace app\frontend\modules\order\operations\member;


use app\backend\modules\goods\models\GoodsTradeSet;
use app\frontend\models\OrderGoods;
use app\frontend\modules\order\operations\OrderOperation;
use Illuminate\Support\Carbon;

class ApplyRefund extends OrderOperation
{
    public function getApi()
    {
        return 'refund.apply.store';
    }
    public function getValue()
    {
        return static::REFUND;
    }
    public function getName()
    {
        return '申请售后';
    }
    public function enable()
    {
        //商城关闭退款按钮
        if (!\Setting::get('shop.trade.refund_status')) {
            return false;
        }
        //商品开启不可退款
        if ($this->order->no_refund) {
            return false;
        }
        $can_refund = $this->order->canRefund();
        $order_goods = OrderGoods::where('order_id', $this->order->id)->get();
        if ($can_refund && $order_goods->count() == 1) {
            $goods_trade = GoodsTradeSet::where('goods_id', $order_goods[0]->goods_id)->first();
            if ($goods_trade && $goods_trade->hide_status) {
                $begin_hide_day = $goods_trade->begin_hide_day;
                if ($begin_hide_day > 1) {
                    $begin_hide_day -= 1;
                    $begin_time = $this->order->pay_time->addDays($begin_hide_day)->format('Y-m-d');
                } else {
                    $begin_time = $this->order->pay_time->format('Y-m-d');
                }
                $begin_time .= " {$goods_trade->begin_hide_time}:00";
                $begin_timestamp = strtotime($begin_time);
                $end_hide_day = $goods_trade->end_hide_day;
                if ($end_hide_day) {
                    $end_time = Carbon::createFromTimestamp($begin_timestamp)->addDays(1)->format('Y-m-d');
                } else {
                    $end_time = Carbon::createFromTimestamp($begin_timestamp)->format('Y-m-d');
                }
                $end_time .= " {$goods_trade->end_hide_time}:00";
                $end_timestamp = strtotime($end_time);
                if ($begin_timestamp < time() && $end_timestamp > time()) {
                    return false;
                }
            }
        }
        return $can_refund;
    }

}