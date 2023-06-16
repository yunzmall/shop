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


use app\common\payment\setting\BasePaymentSetting;

class WechatJsapiSetting extends BasePaymentSetting
{
	public function canUse()
	{
		$face_setting = \Setting::get('plugin.face-payment');
        $wechatSet = \Setting::get('shop.wechat_set');
		return
            (request()->input('type') != 5 || $wechatSet['wechat_web_state']) &&
            request()->input('type') != 7
			&& app('plugins')->isEnabled('face-payment')
			&& $face_setting['switch']
			&& $face_setting['method']['weixin']
			&& !$face_setting['button']['wechat']
			&& \Setting::get('shop.wechat_set');
	}


	public function getWeight()
	{
		return 999;
	}
}