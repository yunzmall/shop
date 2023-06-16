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

class LaKaLaWechatSetting extends BasePaymentSetting
{
    public function canUse()
    {
        // 暂时不接入微信公众号.
        return request()->input('type') != 5
            && app('plugins')->isEnabled('lakala_pay')
            && \Setting::get('lakala_pay.set.plugin_enable')
            && \Setting::get('lakala_pay.set.wechat_enable');
    }
}