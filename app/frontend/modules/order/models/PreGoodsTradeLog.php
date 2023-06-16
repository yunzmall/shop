<?php
/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 2023-01-11
 * Time: 14:43
 */

namespace app\frontend\modules\order\models;


use app\backend\modules\goods\models\GoodsTradeSet;
use app\common\models\order\GoodsTradeLog;
use Illuminate\Support\Carbon;

class PreGoodsTradeLog extends GoodsTradeLog
{
    protected $order;

    /**
     * @param PreOrder $order
     */
    public function setOrder(PreOrder $order)
    {
        $this->order = $order;
        $goods_trade_collect = $this->newCollection();
        foreach ($this->order->orderGoods as $good) {
            $goods_trade_set = GoodsTradeSet::where('goods_id', $good->goods_id)->first();
            if (!$goods_trade_set || !$goods_trade_set->arrived_day || !app('plugins')->isEnabled('address-code')) {
                continue;
            }
            $arrived_day = $goods_trade_set->arrived_day;
            $arrived_word = $goods_trade_set->arrived_word;
            if ($arrived_day > 1) {
                $arrived_day -= 1;
                $time_format = Carbon::createFromTimestamp(time())->addDays($arrived_day)->format('Y-m-d');
            } else {
                $time_format = Carbon::createFromTimestamp(time())->format('Y-m-d');
            }
            $time_format .= " {$goods_trade_set->arrived_time}:00";
            $timestamp = strtotime($time_format);
            if ($timestamp < time()) {
                $timestamp += 86400;
            }
            $show_time = ltrim(date('m', $timestamp), '0').'月';
            $show_time .= ltrim(date('d', $timestamp), '0').'日';
            $show_time .= $goods_trade_set->arrived_time;
            $show_time_word = str_replace('[送达时间]', $show_time, $arrived_word);
            $pre_goods_trade_log = new PreGoodsTradeLog([
                'goods_id' => $good->goods_id,
                'show_time_word' => $show_time_word,
            ]);
            $goods_trade_collect->push($pre_goods_trade_log);
        }
        if (!$goods_trade_collect->isEmpty()) {
            $this->order->setRelation('goodsTradeLog', $goods_trade_collect);
        }
    }
}