<?php
/**
 * Created by PhpStorm.
 *
 * 
 *
 * Date: 2021/12/24
 * Time: 18:28
 */

namespace app\frontend\modules\refund\services\operation;


use app\backend\modules\refund\services\operation\RefundOperation;
use app\common\events\order\BeforeOrderRefundChangeEvent;
use app\common\events\order\OrderRefundApplyEditEvent;
use app\common\models\refund\RefundProcessLog;

class RefundEditApply extends RefundOperation
{
//    protected $statusAfterChanged = self::WAIT_CHECK;
    protected $name = '修改申请';

    protected $editData;

    protected function afterEventClass()
    {
        return new OrderRefundApplyEditEvent($this);
    }

    protected function updateBefore()
    {
        $refundApplyData = $this->getRequest()->only([
            'reason', 'content', 'refund_type','receive_status','refund_way_type','freight_price','other_price',
        ]);

        event(new BeforeOrderRefundChangeEvent($this,$refundApplyData));

        if (is_array($this->getRequest()->input('images'))) {
            $refundApplyData['images'] = $this->getRequest()->input('images');
        } else {
            $refundApplyData['images'] = $this->getRequest()->input('images') ? json_decode($this->getRequest()->input('images'), true):[];
        }

        if (isset($refundApplyData['freight_price'])) {
            $this->freight_price = $refundApplyData['freight_price'];
        }

        if (isset($refundApplyData['other_price'])) {
            $this->other_price = $refundApplyData['other_price'];
        }

        //退款总金额,换货售后退款金额为0
        $price = bcadd($this->apply_price, ($this->other_price +  $this->freight_price),2);
        $this->price = min($this->order->price, $price);


        $this->editData = $refundApplyData;

        $this->fill($refundApplyData);
        $this->backWay()->init($this);  //走退货方式验证
    }

    protected function updateAfter()
    {
        $this->setBackWay();    //保存退货方式内容
    }

    protected function writeLog()
    {
        $detail = [
            '售后类型：'.  $this->getRefundTypeName()[$this->refund_type],
            $this->refund_type == static::REFUND_TYPE_EXCHANGE_GOODS ? '': '退款金额：'.$this->price,
            $this->editData['freight_price']?'修改运费:'.$this->editData['freight_price'] :'',
            $this->editData['other_price']?'修改其他费用:'.$this->editData['other_price'] :'',
            '售后原因：'.$this->reason,
            '说明：'.$this->content,
        ];
        $processLog = RefundProcessLog::logInstance($this, RefundProcessLog::OPERATOR_MEMBER);
        $processLog->setAttribute('operate_type', RefundProcessLog::OPERATE_CHANGE_APPLY);
        $processLog->saveLog($detail);
    }
}