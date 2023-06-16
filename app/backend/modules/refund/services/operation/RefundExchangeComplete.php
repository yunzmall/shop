<?php
/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 2021/12/22
 * Time: 15:30
 */

namespace app\backend\modules\refund\services\operation;

use app\common\events\order\AfterOrderRefundExchangeEvent;
use app\common\models\Order;
use app\common\models\refund\RefundProcessLog;
use app\frontend\modules\order\services\OrderService;

/**
 * 换货确认收货
 * Class RefundExchangeComplete
 * @package app\backend\modules\refund\services\operation
 */
class RefundExchangeComplete extends RefundOperation
{
    protected $statusBeforeChange = [self::WAIT_RECEIVE_RESEND_GOODS];
    protected $statusAfterChanged = self::COMPLETE;
    protected $name = '换货收货';
    protected $timeField = 'refund_time'; //用户退货时间


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
            '用户确认收货'
        ];
        $processLog = RefundProcessLog::logInstance($this, RefundProcessLog::OPERATOR_MEMBER);
        $processLog->setAttribute('operate_type', RefundProcessLog::OPERATE_REFUND_COMPLETE);
        $processLog->saveLog($detail);
    }
}