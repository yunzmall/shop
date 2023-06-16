<?php
/**
 * Created by PhpStorm.
 * User: CJVV
 * Date: 2021/3/17
 * Time: 16:36
 */

namespace app\common\events\order;

use app\common\events\Event;
use app\common\models\refund\RefundApply;

/**
 * 退款申请取消
 * Class AfterOrderRefundCancelEvent
 * @package app\common\events\order
 */
class AfterOrderRefundCancelEvent extends Event
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