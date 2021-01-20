<?php


namespace app\frontend\modules\payment\paymentSettings\shop;


class HkScanAliPaySetting extends BaseSetting
{
    public function canUse()
    {
        if(\Setting::get('plugin.hk_pay_set.is_open_ali_pay') != 1 ){
            return false;
        }
        return true;
    }

    public function exist()
    {
        return true;
    }
}