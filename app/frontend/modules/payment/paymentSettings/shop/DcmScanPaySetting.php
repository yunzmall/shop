<?php


namespace app\frontend\modules\payment\paymentSettings\shop;


class DcmScanPaySetting extends BaseSetting
{
    public function canUse()
    {

        return \Setting::get('plugin.dcm-scan-pay.switch');
    }

    public function exist()
    {
        if(\YunShop::request()->type == 2 && \YunShop::request()->store_id){
            return false;
        }
        if (request()->route != 'order.merge-pay') {
            return false;
        }
        return \Setting::get('plugin.dcm-scan-pay.switch');
    }
}