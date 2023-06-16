<?php
/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 2022/6/29
 * Time: 15:08
 */

namespace app\common\services\payment;


use app\common\services\Pay;
use app\common\services\WechatPay;

class CloudPackagingWechatAppPay extends Pay
{
    /**
     * @param $data
     * @param $payType
     * @return bool|mixed
     */
    public function doPay($data)
    {
        return true;
    }

    public function doRefund($out_trade_no, $totalmoney, $refundmoney)
    {
        return (new WechatPay())->doRefund($out_trade_no, $totalmoney, $refundmoney);
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