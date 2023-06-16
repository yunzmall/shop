<?php
/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 2021/8/3
 * Time: 11:13
 */

namespace app\common\events\order;


use app\common\events\Event;

//催发货事件
class AfterOrderExpeditingDeliveryEvent extends Event
{
    protected $order_id;

    public function __construct($order_id)
    {
        $this->order_id = $order_id;
    }

    public function getOrderId()
    {
        return $this->order_id;
    }
}