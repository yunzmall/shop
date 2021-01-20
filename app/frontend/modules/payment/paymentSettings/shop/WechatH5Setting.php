<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/8/25
 * Time: 14:38
 */

namespace app\frontend\modules\payment\paymentSettings\shop;



class WechatH5Setting extends BaseSetting
{
    public function canUse()
    {

        // 开启微信通用支付和开启微信支付总开关,并且访问端不是app
        return \Setting::get('shop.pay.weixin') && \Setting::get('shop.pay.wechat_h5');
    }

    public function exist()
    {

        return \Setting::get('shop.pay.weixin') !== null || \Setting::get('shop.pay.wechat_h5') !== null;
    }
}