<?php
/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 2022/3/31
 * Time: 09:11
 */

namespace app\common\payment\setting\other;

use app\common\facades\Setting;
use app\common\payment\setting\BasePaymentSetting;

class LeshuaWechatSetting extends BasePaymentSetting
{
    public function canUse()
    {
        return (request()->input('type') != 5 || (request()->cps_h5 && \Setting::get('leshua-pay.set.app_enable')))
            && request()->input('type') != 7
            && app('plugins')->isEnabled('leshua-pay')
            && \Setting::get('leshua-pay.set.plugin_enable')
            && \Setting::get('leshua-pay.set.wechat_enable');
    }


}