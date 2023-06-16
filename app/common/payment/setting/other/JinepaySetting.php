<?php
/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 2022/12/1
 * Time: 13:56
 */

namespace app\common\payment\setting\other;

use app\common\facades\Setting;
use app\common\payment\setting\BasePaymentSetting;

class JinepaySetting extends BasePaymentSetting
{
    public function canUse()
    {
        return request()->input('type') == 5
            && app('plugins')->isEnabled('jinepay')
            && Setting::get('jinepay.set.plugin_enable');
    }
}