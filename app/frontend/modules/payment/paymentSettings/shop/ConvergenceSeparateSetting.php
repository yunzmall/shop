<?php


namespace app\frontend\modules\payment\paymentSettings\shop;


class ConvergenceSeparateSetting extends BaseSetting
{
    public function canUse()
    {
        $set = \Setting::get('plugin.ConvergeAllocFunds_set.converge_pay_status');

        return $set;
    }

    public function exist()
    {
        return \Setting::get('plugin.ConvergeAllocFunds_set') !== null;
    }
}