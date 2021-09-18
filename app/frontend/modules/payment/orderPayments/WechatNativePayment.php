<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/12/31
 * Time: 10:31
 */

namespace app\frontend\modules\payment\orderPayments;


class WechatNativePayment extends WebPayment
{
    public function canUse()
    {
        return \YunShop::request()->type != 2 && parent::canUse();
    }
}