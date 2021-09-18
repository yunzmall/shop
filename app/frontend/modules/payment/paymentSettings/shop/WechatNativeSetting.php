<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/12/31
 * Time: 10:32
 */

namespace app\frontend\modules\payment\paymentSettings\shop;


class WechatNativeSetting  extends BaseSetting
{
    public function canUse()
    {

        // 开启微信通用支付和开启微信支付总开关,并且访问端不是app
        return \Setting::get('shop.pay.weixin') && \Setting::get('shop.pay.wechat_native');
    }

    public function exist()
    {

        return \Setting::get('shop.pay.weixin') !== null || \Setting::get('shop.pay.wechat_native') !== null;
    }
}