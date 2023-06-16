<?php
/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 2021/12/17
 * Time: 19:07
 */

namespace app\common\payment\setting\other;

use app\common\payment\setting\BasePaymentSetting;

class SandpayAlipaySetting extends BasePaymentSetting
{
    public function canUse()
    {
        return request()->input('type') != 2
            && app('plugins')->isEnabled('sandpay')
            && \Setting::get('sandpay.set.plugin_enable')
            && \Setting::get('sandpay.set.alipay.enable');
    }
}