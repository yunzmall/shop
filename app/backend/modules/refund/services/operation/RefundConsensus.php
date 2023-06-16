<?php
/**
 * Created by PhpStorm.
 *
 * 
 *
 * Date: 2021/12/22
 * Time: 14:50
 */

namespace app\backend\modules\refund\services\operation;


use app\backend\modules\refund\services\RefundMessageService;
use app\common\events\order\AfterOrderRefundedEvent;
use app\common\events\order\AfterOrderRefundSuccessEvent;
use app\common\models\refund\RefundProcessLog;

/**
 * 手动退款
 * Class RefundConsensus
 * @package app\backend\modules\refund\services\operation
 */
class RefundConsensus extends RefundOperation
{

    protected $statusBeforeChange = [
        self::WAIT_CHECK,
        self::WAIT_RETURN_GOODS,
        self::WAIT_RECEIVE_RETURN_GOODS,
        self::WAIT_RESEND_GOODS,
        self::WAIT_RECEIVE_RESEND_GOODS,
        self::WAIT_REFUND,
    ];
    protected $statusAfterChanged = self::CONSENSUS;
    protected $name = '手动退款';
    protected $timeField = 'refund_time';

    protected function afterEventClass()
    {
        //event(new AfterOrderRefundedEvent($this->order));
        return new AfterOrderRefundSuccessEvent($this);
    }

    protected function updateBefore()
    {
        // TODO: Implement updateBefore() method.
    }

    protected function updateAfter()
    {
        $this->updateOrderGoodsRefundStatus();
    }

    //必须要触发完退款事件，才订单关闭
    protected function triggerEventAfter()
    {
        //更新订单支付记录状态
        if ($this->order->hasOneOrderPay) {
            $this->order->hasOneOrderPay->refund();
        }

        if ($this->isPartRefund()) {
            $this->cancelRefund();
        } else {
            $this->closeOrder();
        }
    }

    protected function writeLog()
    {
        $detail = [
            $this->getRefundTypeName()[$this->refund_type].'完成',
        ];
        $processLog = RefundProcessLog::logInstance($this, RefundProcessLog::OPERATOR_SHOP);
        $processLog->setAttribute('operate_type', RefundProcessLog::OPERATE_REFUND_CONSENSUS);
        $processLog->saveLog($detail);
    }

    protected function sendMessage()
    {
        RefundMessageService::passMessage($this);//通知买家

        if (app('plugins')->isEnabled('instation-message')) {
            event(new \Yunshop\InstationMessage\event\OrderRefundSuccessEvent($this));
        }
    }
}