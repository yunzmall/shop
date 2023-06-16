<?php
/**
 * Created by PhpStorm.
 * 
 *
 *
 * Date: 2021/8/26
 * Time: 14:02
 */

namespace app\common\payment\setting\other;


use app\common\payment\setting\BasePaymentSetting;
use Yunshop\EplusPay\services\SettingService;

class EplusMiniPaySetting extends BasePaymentSetting
{
    public function canUse()
    {
        return app('plugins')->isEnabled('eplus-pay') && SettingService::usable('mini') && request()->type == 2;
    }
}