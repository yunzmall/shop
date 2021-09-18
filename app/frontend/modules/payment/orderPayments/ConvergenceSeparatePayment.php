<?php


namespace app\frontend\modules\payment\orderPayments;


class ConvergenceSeparatePayment extends BasePayment
{
    public function canUse()
    {

        if (parent::canUse() && \YunShop::plugin()->get('converge-alloc-funds') && \Setting::get('plugin.ConvergeAllocFunds_set.converge_pay_status')=="1" && \Setting::get('plugin.ConvergeAllocFunds_set.wechat.wechat_status') && parent::canUse()) return true;

    }
}