<?php
/**
 * Created by PhpStorm.
 * User: CJVV
 * Date: 2021/3/17
 * Time: 16:36
 */

namespace app\common\events\order;

use app\common\events\Event;

/**
 * 订单退款价格修改前事件
 * Class AfterOrderRefundRejectEvent
 * @package app\common\events\order
 */
class BeforeOrderRefundChangePriceEvent extends Event
{
    protected $refund;
    protected $change_data;

    public function __construct($refund, $change_data)
    {
        $this->refund = $refund;
        $this->change_data = $change_data;
    }

    public function getModel()
    {
        return $this->refund;
    }

    public function getChangeData()
    {
        return $this->change_data;
    }

}