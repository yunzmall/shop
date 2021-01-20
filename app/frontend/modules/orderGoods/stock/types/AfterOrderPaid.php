<?php

namespace app\frontend\modules\orderGoods\stock\types;

use app\common\models\OrderPayOrder;

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
}
