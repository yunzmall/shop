<?php
/**
 * Created by PhpStorm.
 *
 * 
 *
 * Date: 2021/12/22
 * Time: 15:08
 */

namespace app\backend\modules\refund\services\operation;

use app\backend\modules\refund\services\RefundMessageService;
use app\common\events\order\AfterOrderRefundedEvent;
use app\common\events\order\AfterOrderRefundExchangeEvent;
use app\common\events\order\AfterOrderRefundSuccessEvent;
use app\common\models\Order;
use app\common\models\refund\RefundProcessLog;
use app\frontend\modules\order\services\OrderService;

/**
 * 换货完成后台关闭
 * Class RefundClose
 * @package app\backend\modules\refund\services\operation
 */
class RefundClose extends RefundOperation
{
    protected $statusBeforeChange = [
        self::WAIT_CHECK,
        self::WAIT_RETURN_GOODS,
        self::WAIT_RECEIVE_RETURN_GOODS,
        self::WAIT_RESEND_GOODS,
        self::WAIT_RECEIVE_RESEND_GOODS,
        self::WAIT_REFUND,
    ];
    //    protected $statusAfterChanged = self::CLOSE;
    protected $statusAfterChanged = self::COMPLETE;
    protected $name = '换货关闭';
    protected $timeField = 'refund_time';

    protected function afterEventClass()
    {
        return new AfterOrderRefundExchangeEvent($this);
    }

    protected function updateBefore()
    {
        // TODO: Implement updateBefore() method.
    }


    /**
     * @throws \app\common\exceptions\AppException
     */
    protected function updateAfter()
    {
        if ($this->order->status == Order::WAIT_SEND) {
            OrderService::orderSend(['order_id' => $this->order_id]);
            OrderService::orderReceive(['order_id' => $this->order_id]);
        } else if ($this->order->status == Order::WAIT_RECEIVE) {
            OrderService::orderReceive(['order_id' => $this->order_id]);
        }

        $this->updateOrderGoodsRefundStatus();
    }


    //必须要触发完退款事件，才订单关闭
    protected function triggerEventAfter()
    {
        $this->cancelRefund();
    }

    protected function writeLog()
    {
        $detail = [
            $this->getRefundTypeName()[$this->refund_type].'完成',
           '商家关闭换货',
        ];
        $processLog = RefundProcessLog::logInstance($this, RefundProcessLog::OPERATOR_SHOP);
        $processLog->setAttribute('operate_type', RefundProcessLog::OPERATE_REFUND_COMPLETE);
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