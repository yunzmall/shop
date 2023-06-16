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

use app\common\payment\setting\BasePaymentSetting;
use app\common\facades\Setting;

class LSPWalletSetting extends BasePaymentSetting
{
    public function canUse()
    {
        $type_list = [5,18];
        return in_array(request()->input('type'), $type_list)
            && app('plugins')->isEnabled('love-speed-pool')
            && Setting::get('plugin.love_speed_pool.wallet_pay');
    }

    public function getName()
    {
        return Setting::get('plugin.love_speed_pool.diy_wallet_pay_name') ?: parent::getName();
    }
}