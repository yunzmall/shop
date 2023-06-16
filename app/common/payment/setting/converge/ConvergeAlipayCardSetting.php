<?php
/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 2021/8/26
 * Time: 14:02
 */

namespace app\common\payment\setting\converge;

use app\common\payment\setting\BasePaymentSetting;

class ConvergeAlipayCardSetting extends BasePaymentSetting
{
    public function canUse()
    {
        if (!(request()->is_shop_pos || request()->is_store_pos)
            || !app('plugins')->isEnabled('converge_pay')
            || !\Setting::get('plugin.convergePay_set.alipay.alipay_status')
            || !\Setting::get('plugin.convergePay_set.alipay.alipay_card_status')
        ) {
            return false;
        }
        return true;
    }

    public function getWeight()
    {
        return 899;
    }
}