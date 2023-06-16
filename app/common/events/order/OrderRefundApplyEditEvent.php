<?php
/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 2022/4/2
 * Time: 13:54
 */

namespace app\common\events\order;


use app\common\events\Event;
use app\common\models\refund\RefundApply;

class OrderRefundApplyEditEvent  extends Event
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