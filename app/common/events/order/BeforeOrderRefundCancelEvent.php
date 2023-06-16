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
 * 订单退款申请数据修改前事件
 * Class AfterOrderRefundRejectEvent
 * @package app\common\events\order
 */
class BeforeOrderRefundCancelEvent extends Event
{
    protected $refund;

    public function __construct($refund)
    {
        $this->refund = $refund;
    }

    public function getModel()
    {
        return $this->refund;
    }

}