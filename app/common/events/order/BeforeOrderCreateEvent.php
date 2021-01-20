<?php


namespace app\common\events\order;


use app\common\events\Event;
use app\frontend\modules\order\models\PreOrder;


class BeforeOrderCreateEvent extends Event
{
    /**
     * @var PreOrder
     */
    protected $order;

    /**
     * CreatedOrderEvent constructor.
     * @param PreOrder $order
     */
    public function __construct($order)
    {
        $this->order = $order;
    }
    /**
     * (监听者)获取订单model
     * @return PreOrder
     */
    public function getOrder(){
        return $this->order;
    }
}