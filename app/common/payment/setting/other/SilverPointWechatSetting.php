<?php
/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 2022/6/30
 * Time: 15:59
 */

namespace app\common\payment\setting\other;

use app\common\facades\Setting;
use app\common\payment\setting\BasePaymentSetting;

class SilverPointWechatSetting extends BasePaymentSetting
{
    public function canUse()
    {
        $type_list = [1, 7];
        return in_array(request()->input('type'), $type_list)
            && app('plugins')->isEnabled('silver-point-pay')
            && Setting::get('silver-point-pay.set.plugin_enable')
            && Setting::get('silver-point-pay.set.wechat_enable');
    }
}