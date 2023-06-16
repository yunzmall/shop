<?php
/**
 * Created by PhpStorm.
 *
 * 
 *
 * Date: 2021/12/22
 * Time: 15:11
 */

namespace app\backend\modules\refund\services\operation;


use app\common\events\order\AfterOrderRefundCancelEvent;
use app\common\events\order\BeforeOrderRefundCancelEvent;
use app\common\models\refund\RefundGoodsLog;
use app\common\models\refund\RefundProcessLog;

class RefundCancel extends RefundOperation
{
    protected $statusBeforeChange = [self::WAIT_CHECK];
    protected $statusAfterChanged = self::CANCEL;
    protected $name = '取消';

    protected function updateBefore()
    {
        event(new BeforeOrderRefundCancelEvent($this));
    }

    protected function updateAfter()
    {
        $this->order->cancelRefund();

        //取消申请删除记录
        $this->delRefundOrderGoodsLog();
    }


    protected function writeLog()
    {
        $detail = [
            '用户关闭申请',
        ];
        $processLog = RefundProcessLog::logInstance($this, RefundProcessLog::OPERATOR_MEMBER);
        $processLog->setAttribute('operate_type', RefundProcessLog::OPERATE_APPLY);
        $processLog->saveLog($detail);
    }

    protected function afterEventClass()
    {
        return new AfterOrderRefundCancelEvent($this);
    }
}