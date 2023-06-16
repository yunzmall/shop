<?php
/**
 * Created by PhpStorm.
 *
 * 
 *
 * Date: 2021/9/6
 * Time: 15:15
 */

namespace app\frontend\modules\order\operations\member;


use app\frontend\modules\order\operations\OrderOperation;


class CloseReason extends OrderOperation
{
    public function getApi()
    {
        return 'order.operation.close-reason';
    }
    public function getName()
    {
        return '关闭原因';
    }

    public function getValue()
    {
        return 'close_reason';
    }

    public function enable()
    {
        if ($this->order->close_reason) {
            return true;
        }
        return false;
    }
}