<?php

namespace app\frontend\modules\order;

class OrderTaxFeePriceNode extends OrderPriceNode
{
    public function getKey()//暂时一个地方用，如果要多个用需要改成优惠节点的那种模式，因为用到了getPriceBefore($this->getCode()）
    {
        return 'orderTaxFee';
    }

    public function getPrice()
    {
        //最终的计算才要用到截断 任务管理：#11094
        return $this->order->getPriceBefore($this->getKey()) + $this->order->getOrderTaxFeeManager()->getAmount();
    }
}