<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/7/27
 * Time: 16:35
 */

namespace app\frontend\modules\payment\orderPayments;


class ConfirmPayPayment extends BasePayment
{

    public function amountEnough()
    {
        return bccomp($this->orderPay->amount, 0,2) == 0;
    }

    public function canUse()
    {
        return parent::canUse();
    }

    private function equalZero()
    {
        if (bccomp($this->orderPay->orders->sum('price'), 0, 2) == 0) {
            return true;
        }

        return false;
    }
}