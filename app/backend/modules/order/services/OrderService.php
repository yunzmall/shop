<?php
/**
 * Created by PhpStorm.
 * Author:
 * Date: 2017/5/2
 * Time: 下午4:28
 */

namespace app\backend\modules\order\services;


use app\backend\modules\order\models\Order;
use app\common\events\order\AfterOrderCanceledEvent;
use app\common\exceptions\AdminException;

class OrderService
{
    public static function close($order)
    {

        $order->status = Order::CLOSE;
        $order->cancel_time = time();
        $result = $order->save();
        event(new AfterOrderCanceledEvent($order));
        return $result;
    }

    public static function cancelRefund($order)
    {
        $order->refund_id = 0;
        $result = $order->save();
        return $result;
    }
}