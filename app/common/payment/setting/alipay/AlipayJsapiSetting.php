<?php
/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 2021/8/26
 * Time: 14:02
 */

namespace app\common\payment\setting\alipay;


use app\common\payment\setting\BasePaymentSetting;

class AlipayJsapiSetting extends BasePaymentSetting
{
	public function canUse()
	{
		$face_setting = \Setting::get('plugin.face-payment');
		return request()->input('type') != 2
//			&& request()->input('type') != 7
			&& app('plugins')->isEnabled('face-payment')
			&& $face_setting['switch']
			&& $face_setting['method']['alipay']
			&& !$face_setting['button']['alipay']
			&& \Setting::get('shop.alipay_set');
	}

	public function getWeight()
	{
		return 899;
	}
}