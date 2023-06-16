<?php
/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 2021/12/22
 * Time: 14:22
 */

namespace app\backend\modules\refund\services\operation;


use app\backend\modules\refund\services\RefundMessageService;
use app\common\events\order\AfterOrderRefundRejectEvent;
use app\common\models\refund\RefundApply;
use app\common\models\refund\RefundGoodsLog;
use app\common\models\refund\RefundProcessLog;

/**
 * 驳回退款申请
 * Class RefundReject
 * @package app\backend\modules\refund\services\operation
 */
class RefundReject extends RefundOperation
{
    protected $statusBeforeChange = [self::WAIT_CHECK,self::WAIT_RETURN_GOODS,self::WAIT_RECEIVE_RETURN_GOODS];


    protected $name = '驳回';

    protected $statusAfterChanged = self::REJECT;

    protected function afterEventClass()
    {
        return new AfterOrderRefundRejectEvent($this);
    }

    protected function updateBefore()
    {
        $this->setAttribute('reject_reason', $this->getRequest()->input('reject_reason'));
        $this->setAttribute('status', self::REJECT);
        $this->setAttribute('reject_time', time());
    }

    protected function updateAfter()
    {
        $this->cancelRefund();
        //取消申请删除记录
        $this->delRefundOrderGoodsLog();
    }

    protected function writeLog()
    {
        $detail = [
            '驳回原因：'.$this->reject_reason,
        ];
        $processLog = RefundProcessLog::logInstance($this, RefundProcessLog::OPERATOR_SHOP);
        $processLog->setAttribute('operate_type', RefundProcessLog::OPERATE_REJECT);
        $processLog->saveLog($detail);
    }


    protected function sendMessage()
    {
        RefundMessageService::rejectMessage($this);//通知买家

        if (app('plugins')->isEnabled('instation-message')) {
            //开启了站内消息插件
            event(new \Yunshop\InstationMessage\event\RejectOrderRefundEvent($this));
        }
    }
}