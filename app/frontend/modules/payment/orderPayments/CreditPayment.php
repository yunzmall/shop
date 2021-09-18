<?php
/**
 * Created by PhpStorm.
 * User: shenyang
 * Date: 2017/11/2
 * Time: 下午1:40
 */

namespace app\frontend\modules\payment\orderPayments;


use app\common\models\PayType;
use app\frontend\modules\payment\paymentSettings\OrderPaymentSettingCollection;

class CreditPayment extends BasePayment
{
    public function amountEnough()
    {
        return $this->orderPay->amount >= 0;
    }
    public function canUse()
    {
        //使用余额抵扣的订单不能使用余额支付
        if ($this->useBalanceDeduction()) {
            return false;
        }

        return parent::canUse() && $this->orderPay;
    }

    protected function useBalanceDeduction()
    {
        if ($this->orderPay) {

            return \Setting::get('finance.balance.balance_deduct')?true:false;

//            return $this->orderPay->orders->contains(function ($order) {
//                $isUse = false;
//                if ($order->deductions) {
//                    foreach ($order->deductions as $key => $deduction) {
//                        if ($deduction['code'] == 'balance') {
//                            $isUse = true;
//                            break;
//                        }
//                    }
//                }
//
//                return $isUse;
//            });
        }

        return false;
    }
}