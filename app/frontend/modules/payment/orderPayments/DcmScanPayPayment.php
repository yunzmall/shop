<?php


namespace app\frontend\modules\payment\orderPayments;


class DcmScanPayPayment extends BasePayment
{
    public function canUse()
    {
        //商家pos才支持扫码支付
        if (!\Setting::get('plugin.dcm-scan-pay.switch')) {
            return false;
        }
        return parent::canUse();
    }
}