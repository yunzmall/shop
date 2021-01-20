<?php


namespace app\frontend\modules\payment\orderPayments;


class HkScanPayment extends BasePayment
{
    public function canUse()
    {
        if(\Setting::get('plugin.hk_pay_set.is_open_pay') != 1 ){
            return false;
        }

        return parent::canUse();
    }
}