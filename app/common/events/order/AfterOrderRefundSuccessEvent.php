<?php
/**
 * Created by PhpStorm.
 * User: CJVV
 * Date: 2021/3/17
 * Time: 16:36
 */

namespace app\common\events\order;

/**
 * 退款成功
 * Class AfterOrderRefundSuccessEvent
 * @package app\common\events\order
 */
class AfterOrderRefundSuccessEvent
{
    protected $orderModel;

    public function __construct($orderModel)
    {
        $this->orderModel= $orderModel;
    }

    public function getModel()
    {
        return $this->orderModel;
    }

}