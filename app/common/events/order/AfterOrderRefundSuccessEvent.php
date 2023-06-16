<?php
/**
 * Created by PhpStorm.
 * User: CJVV
 * Date: 2021/3/17
 * Time: 16:36
 */
namespace app\common\events\order;

use app\common\models\refund\RefundApply;
use app\common\events\Event;
/**
 * 退款成功
 * Class AfterOrderRefundSuccessEvent
 * @package app\common\events\order
 */
class AfterOrderRefundSuccessEvent  extends Event
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

    public function getRefundGoods()
    {
        return $this->refundModel->refundOrderGoods;
    }

}