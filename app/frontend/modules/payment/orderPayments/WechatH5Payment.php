<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/8/25
 * Time: 14:41
 */

namespace app\frontend\modules\payment\orderPayments;


class WechatH5Payment extends WebPayment
{
    public function canUse()
    {
        return \YunShop::request()->type != 2 && parent::canUse() && \YunShop::request()->wechat_app_pay_type != 'cps';
    }
}