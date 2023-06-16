<?php
/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 2021/8/26
 * Time: 14:02
 */

namespace app\common\payment\setting\wechat;


use app\common\facades\Setting;
use app\common\payment\setting\BasePaymentSetting;
use Yunshop\ShopPos\services\SettingService;

class WechatMicroSetting extends BasePaymentSetting
{
    public function canUse()
    {
        if (Setting::get('shop.pay.wechat_micro') == 1 && request()->is_shop_pos) {
            return true;
        }
        return false;
    }

    public function getWeight()
    {
        return 999;
    }
}