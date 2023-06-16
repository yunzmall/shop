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

class CodeSciencePayYuSetting extends BasePaymentSetting
{
    public function canUse()
    {
        return request()->input('type') == 5
            && app('plugins')->isEnabled('code-science-pay')
            && Setting::get('code-science-pay.set.plugin_enable');
    }
}