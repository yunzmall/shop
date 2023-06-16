<?php
/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 2021/12/22
 * Time: 15:20
 */

namespace app\backend\modules\refund\services\operation;


use app\common\events\order\AfterOrderRefundResendEvent;
use app\common\models\refund\RefundProcessLog;
use app\common\models\refund\ResendExpress;
use app\common\repositories\ExpressCompany;

class RefundResend extends RefundOperation
{
    protected $statusAfterChanged = self::WAIT_RECEIVE_RESEND_GOODS;
    protected $name = '商家发货';
    protected $timeField = 'send_time';

    protected $resendExpress;

    protected function afterEventClass()
    {
        return new AfterOrderRefundResendEvent($this);
    }

    protected function updateBefore()
    {
        $expressData = $this->getRequest()->only('express_code', 'express_sn');

        $expressData['express_company_name'] = array_get(ExpressCompany::create()->where('value', $expressData['express_code'])->first(), 'name', '其他快递');

        
        $order_goods = $this->order->orderGoods;
        if ($order_goods) {
            foreach ($order_goods as $goods) {
                $refundGoods = $this->refundOrderGoods->where('order_goods_id', $goods['id'])->first();
                if ($refundGoods) {
                    $expressData['pack_goods'][] = [
                        'order_goods_id' => $refundGoods->order_goods_id,
                        'title' => $refundGoods->goods_title,
                        'goods_option_title' => $refundGoods->goods_option_title,
                        'thumb' => $refundGoods->goods_thumb,
                        'total' => $refundGoods->total,
                    ];
                    $refundGoods->fill(['send_num'=> 0])->save();
                }
            }
        }


        $expressData['refund_id'] = $this->id;
        $this->resendExpress =  ResendExpress::create($expressData);
    }


    protected function updateAfter()
    {

    }

    protected function writeLog()
    {
        $detail = [
            '快递公司：'.$this->resendExpress->express_company_name,
            '快递单号：'.$this->resendExpress->express_sn,
        ];
        $processLog = RefundProcessLog::logInstance($this, RefundProcessLog::OPERATOR_SHOP);
        $processLog->setAttribute('operate_type', RefundProcessLog::OPERATE_SHOP_RESEND);
        $processLog->saveLog($detail);
    }
}