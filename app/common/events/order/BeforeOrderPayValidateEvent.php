<?php
/**
 * 订单取消后事件
 * Created by PhpStorm.
 * Author:
 * Date: 2017/3/3
 * Time: 上午11:44
 */

namespace app\common\events\order;

use app\common\models\Order;
use app\frontend\modules\orderPay\models\PreOrderPay;

class BeforeOrderPayValidateEvent extends CreatedOrderStatusChangedEvent
{
    /**
     * @var PreOrderPay
     */
    protected $orderPay;

    /**
     * CreatedOrderEvent constructor.
     * @param Order $order
     * @param PreOrderPay
     */
    public function __construct($order,$orderPay)
    {
        parent::__construct($order);
        $this->orderPay = $orderPay;
    }

    /**
     * @return PreOrderPay
     */
    public function getOrderPay()
    {
        return $this->orderPay;
    }
}