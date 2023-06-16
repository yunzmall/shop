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

class EplusAliPaySetting extends BasePaymentSetting
{
    public function canUse()
    {
        return app('plugins')->isEnabled('eplus-pay') && SettingService::usable('alipay') && in_array(request()->type, [5, 15]);
    }
}