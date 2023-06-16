<?php

namespace app\frontend\modules\orderGoods\stock\types;

use app\common\models\Order;
use app\common\models\OrderPayOrder;
use Illuminate\Support\Facades\Redis;

class AfterOrderPaid extends AfterOrderCreate
{
    public function shouldWithhold()
    {
        if (OrderPayOrder::where('order_id', $this->orderGoods()->order->id)->count()) {
            return true;
        }
        return false;
    }
    public function shouldRollback()
    {
        return true;
    }

    public function rollback()
    {
        if (!$this->shouldRollback()) {
            return true;
        }
        if ($this->orderGoods()->order->status == Order::CLOSE && strtotime($this->orderGoods()->order->pay_time) > 0) {
            // 支付过的订单退款时不返还库存
            return true;
        }
        $rank = Redis::zrank($this->goodsStock->withholdKey(), $this->orderGoods()->id);
        // 存在预扣记录
        if (!is_null($rank) && $rank >= 0) {
            // 移除记录
            Redis::zrem($this->goodsStock->withholdKey(), $this->orderGoods()->id);
            if ($rank == 0) {
                // 如果是最后一个记录,从key集合中删除
                Redis::srem($this->goodsStock->keyOfWithholdKeySet(), $this->goodsStock->withholdKey());
            }
            // 减少数量
            $this->source()->goodsStock()->rollback($this->orderGoods()->total);
            return true;
        }

        return false;
    }

    public function createReduce()
    {
        return false;
    }

    public function reduce()
    {
        //是否满足减库存条件
        if (!$this->satisfyReduce()) {
            return false;
        }

        // 减少数据库库存数量
        $result = $this->source()->goodsStock()->reduce($this->orderGoods()->total);

        // 退回预扣库存
        $rank = Redis::zrank($this->goodsStock->withholdKey(), $this->orderGoods()->id);
        // 存在预扣记录
        if (!is_null($rank) && $rank >= 0) {
            // 移除记录
            Redis::zrem($this->goodsStock->withholdKey(), $this->orderGoods()->id);
            if ($rank == 0) {
                // 如果是最后一个记录,从key集合中删除
                Redis::srem($this->goodsStock->keyOfWithholdKeySet(), $this->goodsStock->withholdKey());
            }
            // 减少数量
            $this->source()->goodsStock()->rollback($this->orderGoods()->total);
        }

        return $result;
    }


}
