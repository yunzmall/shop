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

class PayPalSetting extends BasePaymentSetting
{
	public function canUse()
	{
		return request()->input('type')
			&& app('plugins')->isEnabled('pay-pal')
			&& !is_null(\Setting::get('plugin.pay_pal'))
			&& \Setting::get('plugin.pay_pal')['is_open'] != 1;
	}
}