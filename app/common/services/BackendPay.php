<?php
/**
 * Created by PhpStorm.
 * Author:
 * Date: 2017/3/24
 * Time: 下午2:29
 */

namespace app\common\services;


class BackendPay extends Pay
{
    public function __construct()
    {
    }

    public function doPay($data = [])
    {
        return true;
    }

    function doRefund($out_trade_no, $totalmoney, $refundmoney)
    {
        return true;
        // TODO: Implement doRefund() method.
        return true;
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