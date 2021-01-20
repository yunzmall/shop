<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/8/21
 * Time: 10:25
 */

namespace app\frontend\modules\order;


class OrderServiceFeeNode extends OrderPriceNode
{
    public function getKey()
    {
        return 'orderServiceFee';
    }

    public function getPrice()
    {
        return $this->order->getPriceBefore($this->getKey()) + $this->order->getOrderServiceFeeManager()->getAmount();
    }
}