<?php


namespace app\frontend\modules\payment\orderPayments;


class XfpayWechatPayment extends BasePayment
{
    public function canUse()
    {
        //app也显示支付
        return parent::canUse() && \YunShop::request()->type != 5 && \YunShop::plugin()->get('xfpay');
    }
}