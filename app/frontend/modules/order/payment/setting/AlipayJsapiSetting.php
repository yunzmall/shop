<?php
/**
 * Created by PhpStorm.
 * 
 *
 *
 * Date: 2021/12/2
 * Time: 14:42
 */

namespace app\frontend\modules\order\payment\setting;


class AlipayJsapiSetting extends \app\common\payment\setting\alipay\AlipayJsapiSetting
{
    public function canUse()
    {
        $face_setting = \Setting::get('plugin.face-payment');
        return parent::canUse() && !$face_setting['shop_button']['alipay'];
    }
}