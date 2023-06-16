<?php
/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 2022/12/27
 * Time: 10:50
 */
namespace app\common\payment\setting\other;

use app\common\facades\Setting;
use app\common\payment\setting\BasePaymentSetting;

class AuthPaySetting extends BasePaymentSetting
{
    public function canUse()
    {
        $pay_id = [1, 5];
        return in_array(request()->input('type'), $pay_id)
            && app('plugins')->isEnabled('sub-auth-payment')
            && Setting::get('sub-auth-payment.set.plugin_enable');
    }
}