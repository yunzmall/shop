<?php
/**
 * Created by PhpStorm.
 * User: CJVV
 * Date: 2021/3/17
 * Time: 16:36
 */

namespace app\common\events\order;

/**
 * 退款驳回
 * Class AfterOrderRefundRejectEvent
 * @package app\common\events\order
 */
class AfterOrderRefundRejectEvent
{
    protected $refund;

    public function __construct($refund)
    {
        $this->refund= $refund;
    }

    public function getModel()
    {
        return $this->refund;
    }

}