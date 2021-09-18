<?php


namespace app\frontend\modules\payment\paymentSettings\shop;


class XfpayAlipaySetting extends BaseSetting
{
    public function canUse()
    {
        return \Setting::get('plugin.xfpay_set.xfpay.pay_type.alipay.enabled');
    }

    public function exist()
    {
        return \Setting::get('plugin.xfpay_set') !== null;
    }
}