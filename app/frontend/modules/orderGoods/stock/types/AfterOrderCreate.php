<?php

namespace app\frontend\modules\orderGoods\stock\types;

use app\common\exceptions\AppException;
use app\common\models\Order;
use app\common\models\OrderPayOrder;
use Illuminate\Support\Facades\Redis;

class AfterOrderCreate extends StockType
{
    public function enough()
    {
        return $this->source()->stockEnough($this->orderGoods()->total);
    }

    public function withholdRecord()
    {
        if (!is_null(Redis::zrank($this->goodsStock->withholdKey(), $this->orderGoods()->id))) {
            // 如果存在订单商品对应的锁定记录,则返回
            return true;
        }
        // 添加库存预扣记录
        Redis::zadd($this->goodsStock->withholdKey(), time(), $this->orderGoods()->id);
        Redis::sadd($this->goodsStock->keyOfWithholdKeySet(), $this->goodsStock->withholdKey());
    }

    public function withhold()
    {
        if (!is_null(Redis::zrank($this->goodsStock->withholdKey(), $this->orderGoods()->id))) {
            // 如果存在订单商品对应的锁定记录,则返回
            return true;
        }
        if (!$this->enough()) {
            throw new AppException("商品{$this->orderGoods()->title}库存不足{$this->orderGoods()->total}件");
        }
        if($this->orderGoods()->id){
            $this->withholdRecord();
        }
        // 商品库存预扣
        return $this->source()->goodsStock()->withhold($this->orderGoods()->total);
    }

    public function shouldRollback()
    {
        if ($this->orderGoods()->order->status == Order::WAIT_PAY) {
            // 待支付的不返还预扣库存
            return false;
        }
        return true;
    }

    /**
     * 退回预扣库存
     * @return bool
     */
    public function rollback()
    {
        if (!$this->shouldRollback()) {
            return true;
        }
        if ($this->orderGoods()->order == Order::CLOSE && $this->orderGoods()->order->pay_time > 0) {
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
    }


    public function reduce()
    {
        // 退回预扣库存
        $rollback = $this->rollback();
        if (!$rollback) {
            return false;
        }
        // 减少库存数量
        return $this->source()->goodsStock()->reduce($this->orderGoods()->total);
    }

    public function shouldWithhold()
    {
        if (!OrderPayOrder::where('order_id', $this->orderGoods()->order->id)->count()) {
            return true;
        }
        return false;
    }


}