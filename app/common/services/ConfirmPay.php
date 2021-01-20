<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/7/27
 * Time: 16:25
 */

namespace app\common\services;

use app\common\exceptions\AppException;
use app\common\models\PayOrder;

class ConfirmPay extends Pay
{
    public function doPay($params = [])
    {

        if (bccomp($params['amount'], 0, 2) !== 0) {
            throw new AppException('金额大于0不能确认');
        }



        $operation = '确认支付 订单号：' . $params['order_no'];
        $this->log($params['extra']['type'], '确认支付', $params['amount'], $operation, $params['order_no'], Pay::ORDER_STATUS_NON, \YunShop::app()->getMemberId());

        self::payRequestDataLog($params['order_no'], $params['extra']['type'], '确认支付', json_encode($params));

        $pay_order_model = PayOrder::uniacid()->where('out_order_no', $params['order_no'])->first();

        if ($pay_order_model) {
            $pay_order_model->status = 2;
            $pay_order_model->trade_no = $params['trade_no'];
            $pay_order_model->third_type = '确认支付';
            $pay_order_model->save();
        }

        return true;

    }

    public function doRefund($out_trade_no, $totalmoney, $refundmoney)
    {
       if (bccomp($refundmoney, 0, 2) === 0) {
            return true;
       }
       \Log::debug('------确认支付退款-错误-----',[$out_trade_no, $totalmoney, $refundmoney]);
       return false;
    }

    public function doWithdraw($member_id, $out_trade_no, $money, $desc, $type)
    {
        // TODO: Implement doWithdraw() method.
    }

    public function buildRequestSign()
    {
        // TODO: Implement buildRequestSign() method.
    }
}