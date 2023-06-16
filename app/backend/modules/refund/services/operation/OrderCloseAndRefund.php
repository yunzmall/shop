<?php
/**
 * Created by PhpStorm.
 * User: blank
 * Date: 2022/11/22
 * Time: 15:20
 */

namespace app\backend\modules\refund\services\operation;

use app\common\models\refund\RefundProcessLog;
use app\common\modules\refund\RefundOrderFactory;
use app\frontend\modules\refund\services\RefundService;

class OrderCloseAndRefund extends \app\frontend\modules\refund\services\operation\RefundApply
{
    protected $port_type = 'backend';

    protected function updateBefore()
    {

        //售后记录必须是这笔订单所属会员的
        if (!isset($this->uid) || $this->uid != $this->order->uid) {
            $this->uid  = $this->order->uid;
        }

        $refundApplyData = [
            'order_id' => $this->order->id,
            'refund_type' => static::REFUND_TYPE_REFUND_MONEY,
            'content' => request()->input('reson', ''),
            'reason' => '订单关闭并退款',
            'images' => [],
        ];


        $refundedPrice = \app\common\models\refund\RefundApply::getAfterSales($this->order->id)->get();

        $refundOrder = $this->relatedPluginOrder();

        //申请退款金额
        $refundApplyData['apply_price'] = $this->getRefundApplyPrice();
        //退款运费金额
        $refundApplyData['freight_price'] =  max(bcsub($refundOrder->getOrderFreightPrice(), $refundedPrice->sum('freight_price'),2),0);
        //退款其他金额
        $refundApplyData['other_price'] =  max(bcsub($refundOrder->getOrderOtherPrice(), $refundedPrice->sum('other_price'),2),0);


        //实际退款金额 = 订单实付金额 - 已退款金额
        $refundApplyData['price'] = max(bcsub($this->order->price,$refundedPrice->sum('price'),2),0);


        $refundApplyData['part_refund'] = static::ORDER_CLOSE;

        $refundApplyData['refund_sn'] = RefundService::createOrderRN();

        $this->fill($refundApplyData);

    }


    protected function writeLog()
    {
        $detail = [
            '售后类型：退款',
            '退款金额：'.$this->price,
            $this->freight_price?'运费:'. $this->freight_price :'',
            $this->other_price?'其他费用:'. $this->other_price :'',
            '售后原因：'.$this->reason,
            '说明：'.$this->content,
        ];
        $processLog = RefundProcessLog::logInstance($this, RefundProcessLog::OPERATOR_SHOP);
        $processLog->setAttribute('operate_type', RefundProcessLog::OPERATE_APPLY_SHOP);
        $processLog->saveLog($detail, request()->input());
    }
}