<?php

namespace app\frontend\modules\payment\orderPayments;

class XfpayAlipayPayment extends BasePayment
{
    public function canUse()
    {
        // 小程序不支持支付宝网页支付
        if(\YunShop::request()->type == 2){
            return false;
        }
        return parent::canUse() && \YunShop::plugin()->get('xfpay');
    }
}