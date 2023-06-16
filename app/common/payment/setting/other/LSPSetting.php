<?php

namespace app\common\payment\setting\other;

use app\common\payment\setting\BasePaymentSetting;

class LSPSetting extends BasePaymentSetting
{
    public function canUse()
    {
        // 没有加速池插件 或 基础设置没有开启通证支付 或 基础设置开启了通证抵扣
        if (!app('plugins')->isEnabled('love-speed-pool') || \Setting::get('plugin.love_speed_pool.pay') != 1 || \Setting::get('plugin.love_speed_pool.is_tz_deduction') == 1) {
            return false;
        }
        $ljzInfo = \Setting::get('plugin.love_speed_pool_ljz');
        // 商户注册: 没有注册 或 没有设置余额通证
//        if (!$ljzInfo['ljz_uid'] || !$ljzInfo['blance_currency']) {
        if (!$ljzInfo['blance_currency']) {
            return false;
        }
        return true;
    }

    public function needPassword()
    {
        return false;
    }

    public function getWeight()
    {
        return 1002;
    }

    public function getName()
    {
        $name = "通证支付";
        $setting = \Setting::get('plugin.love_speed_pool');
        if ($setting['diy_pay_name']) {
            $name = $setting['diy_pay_name'];
        }
        return $name;
    }
}