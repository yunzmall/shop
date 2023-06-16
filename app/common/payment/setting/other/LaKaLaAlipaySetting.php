<?php
/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 2022/2/17
 * Time: 17:07
 */
namespace app\common\payment\setting\other;

use app\common\payment\setting\BasePaymentSetting;

class LaKaLaAlipaySetting extends BasePaymentSetting
{
    public function canUse()
    {
        return request()->input('type') != 2
            && app('plugins')->isEnabled('lakala_pay')
            && \Setting::get('lakala_pay.set.plugin_enable')
            && \Setting::get('lakala_pay.set.alipay_enable');
    }
}