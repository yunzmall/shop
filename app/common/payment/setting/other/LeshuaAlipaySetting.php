<?php
/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 2022/3/31
 * Time: 09:10
 */
namespace app\common\payment\setting\other;

use app\common\payment\setting\BasePaymentSetting;

class LeshuaAlipaySetting extends BasePaymentSetting
{
    public function canUse()
    {
        return request()->input('type') != 2
            && app('plugins')->isEnabled('leshua-pay')
            && \Setting::get('leshua-pay.set.plugin_enable')
            && \Setting::get('leshua-pay.set.alipay_enable');
    }
}