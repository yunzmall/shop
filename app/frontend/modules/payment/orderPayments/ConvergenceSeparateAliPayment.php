<?php


namespace app\frontend\modules\payment\orderPayments;


class ConvergenceSeparateAliPayment extends BasePayment
{
    public function canUse()
    {
        
        if(\YunShop::request()->type == 2){
            return false;
        }
        //app也显示支付
        return parent::canUse() &&  \YunShop::plugin()->get('converge-alloc-funds') && \YunShop::plugin()->get('converge-alloc-funds') && \Setting::get('plugin.ConvergeAllocFunds_set.converge_pay_status')=="1" && "1"==\Setting::get('plugin.ConvergeAllocFunds_set.alipay.alipay_status') ;
    }
}