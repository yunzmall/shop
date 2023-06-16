<?php
/**
 * Created by PhpStorm.
 * 
 *
 *
 * Date: 2021/12/17
 * Time: 19:05
 */

namespace app\common\payment\setting\other;

use app\common\payment\setting\BasePaymentSetting;

class SandpayWechatSetting extends BasePaymentSetting
{
    public function canUse()
    {
        $type_list = [1, 7];
        return in_array(request()->input('type'), $type_list)
            && app('plugins')->isEnabled('sandpay')
            && \Setting::get('sandpay.set.plugin_enable')
            && \Setting::get('sandpay.set.wechat.enable');
    }
}