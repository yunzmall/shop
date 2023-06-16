<?php
/**
 * 订单包裹发货后事件
 * Created by PhpStorm.
 * Author:
 * Date: 2017/3/3
 * Time: 上午11:44
 */

namespace app\common\events\order;

use app\common\events\Event;
use app\common\models\Order;

class AfterOrderPackageSentEvent  extends Event
{

    protected $orderModel;
    /**
     * @var Order
     */
    protected $order;

    /**
     * CreatedOrderEvent constructor.
     * @param Order $order
     */
    public function __construct($order)
    {

        $this->order = $order;
    }
    /**
     * (监听者)获取订单model
     * @return Order
     */
    public function getOrderModel(){
        return $this->order;
    }

    /**
     * @return Order
     */
    public function getOrder()
    {
        return $this->order;
    }
}