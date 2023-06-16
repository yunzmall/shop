<?php
/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 2022/2/14
 * Time: 9:27
 */

namespace app\common\events\order;

use app\common\events\Event;
use app\common\models\refund\RefundApply;

/**
 * 订单售后换货完成事件
 * Class AfterOrderRefundExchangeEvent
 * @package app\common\events\order
 */
class AfterOrderRefundExchangeEvent  extends Event
{
    /**
     * @var RefundApply
     */
    protected $refund;

    public function __construct($refund)
    {
        $this->refund= $refund;
    }

    /**
     * @return RefundApply
     */
    public function getModel()
    {
        return $this->refund;
    }
}