<?php
/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 2022/4/2
 * Time: 18:49
 */

namespace app\common\payment\setting\other;

use app\common\payment\setting\BasePaymentSetting;

class LeshuaPosSetting extends BasePaymentSetting
{
    public function canUse()
    {
        return app('plugins')->isEnabled('leshua-pay')
            && \Setting::get('leshua-pay.set.plugin_enable')
            && \Setting::get('leshua-pay.set.pos_enable')
            && (request()->is_shop_pos || request()->is_store_pos);
    }
}