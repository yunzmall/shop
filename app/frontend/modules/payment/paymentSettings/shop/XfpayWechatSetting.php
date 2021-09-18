<?php


namespace app\frontend\modules\payment\paymentSettings\shop;


class XfpayWechatSetting extends BaseSetting
{
    public function canUse()
    {
        return \Setting::get('plugin.xfpay_set.xfpay.pay_type.wechat.enabled');
    }

    public function exist()
    {
        return \Setting::get('plugin.xfpay_set') !== null;
    }
}