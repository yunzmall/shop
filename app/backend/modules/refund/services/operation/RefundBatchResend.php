<?php
/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 2021/12/27
 * Time: 19:20
 */

namespace app\backend\modules\refund\services\operation;


use app\common\models\refund\RefundGoodsLog;
use app\common\models\refund\RefundProcessLog;
use app\common\models\refund\ResendExpress;
use app\common\repositories\ExpressCompany;

class RefundBatchResend extends RefundOperation
{
    protected $statusBeforeChange = [self::WAIT_RETURN_GOODS,self::WAIT_RECEIVE_RETURN_GOODS,self::WAIT_RESEND_GOODS];
    protected $statusAfterChanged = self::WAIT_RESEND_GOODS;
    protected $name = '商家分批发货';
    protected $timeField = 'send_time';

    protected $resendExpress;

    protected function updateBefore()
    {
        $expressData = $this->getRequest()->only('express_company_name', 'express_sn');

        $expressData['company_code'] = $this->getRequest()->input('express_company_code');

        $expressData['express_company_name'] = array_get(ExpressCompany::create()->where('value', $expressData['express_company_code'])->first(), 'name', '其他快递');

        $order_goods = $this->getRequest()->input('pack_goods');
        if ($order_goods) {
            foreach ($order_goods as $goods) {
                $refundGoods = $this->refundOrderGoods->where('order_goods_id', $goods['id'])->first();
                if ($refundGoods) {
                    $expressData['pack_goods'][] = [
                        'order_goods_id' => $refundGoods->order_goods_id,
                        'title' => $refundGoods->goods_title,
                        'goods_option_title' => $refundGoods->goods_option_title,
                        'thumb' => $refundGoods->goods_thumb,
                        'total' => $goods['num'],
                    ];
                    $refundGoods->fill(['send_num'=> max($refundGoods->send_num - $goods['num'],0)])->save();
                }
            }
        }

        $resendExpress = new ResendExpress($expressData);

        $this->resendExpress()->save($resendExpress);

        $this->resendExpress = $resendExpress;
    }


    protected function updateAfter()
    {
        $number = RefundGoodsLog::where('refund_id', $this->id)->get()->sum('send_num');

        //没有商品发货完成
        if ($number <= 0) {
            $this->status = self::WAIT_RECEIVE_RESEND_GOODS;
            $this->save();
        }
    }

    protected function writeLog()
    {
        $detail = [
            '快递公司：'.$this->resendExpress->express_company_name,
            '快递单号：'.$this->resendExpress->express_sn,
        ];
        $processLog = RefundProcessLog::logInstance($this, RefundProcessLog::OPERATOR_SHOP);
        $processLog->setAttribute('operate_type', RefundProcessLog::OPERATE_SHOP_BATCH_RESEND);
        $processLog->saveLog($detail);
    }
}