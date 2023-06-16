<?php
/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 2021/7/1
 * Time: 10:27
 */

namespace app\common\events\order;

use app\common\models\Order;
use app\common\models\OrderPay;

class OrderPayValidateEvent extends CreatedOrderStatusChangedEvent
{
    /**
     * @var OrderPay
     */
    protected $orderPay;

    /**
     * CreatedOrderEvent constructor.
     * @param Order $order
     * @param OrderPay
     */
    public function __construct($order,$orderPay)
    {
        parent::__construct($order);
        $this->orderPay = $orderPay;
    }

    /**
     * @return OrderPay
     */
    public function getOrderPay()
    {
        return $this->orderPay;
    }
}