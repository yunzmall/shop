<?php
/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 2022/2/14
 * Time: 13:55
 */

namespace app\common\events\order;


use app\common\models\refund\RefundApply;
use app\common\events\Event;

/**
 * 退换货用户寄回
 * Class AfterOrderRefundResendEvent
 * @package app\common\events\order
 */
class AfterOrderRefundSendBackEvent extends Event
{
    protected $refundModel;

    public function __construct(RefundApply $refundModel)
    {
        $this->refundModel = $refundModel;
    }

    public function getModel()
    {
        return $this->refundModel;
    }

    public function getOrderModel()
    {
        return $this->refundModel->order;
    }
}