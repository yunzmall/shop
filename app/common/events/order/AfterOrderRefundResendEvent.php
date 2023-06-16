<?php
/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 2022/2/14
 * Time: 13:56
 */

namespace app\common\events\order;

use app\common\models\refund\RefundApply;
use app\common\events\Event;

/**
 * 换货商家发货
 * Class AfterOrderRefundResendEvent
 * @package app\common\events\order
 */
class AfterOrderRefundResendEvent extends Event
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