<?php
/**
 * 订单取消后事件
 * Created by PhpStorm.
 * Author:
 * Date: 2017/3/3
 * Time: 上午11:44
 */

namespace app\common\events\order;

class BeforeOrderSendEvent extends BeforeCreatedOrderStatusChangeEvent
{

    protected $params;

    public function __construct($order, $params = [])
    {
        if ($params) {
            $this->params = $params;
        } elseif ($order->params) {
            $this->params = $order->params;
        }
        parent::__construct($order);
    }


    public function getParams()
    {
        return $this->params;
    }

}