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

class ConvergeQuickSetting extends BasePaymentSetting
{
	public function canUse()
	{
		return app('plugins')->isEnabled('converge_pay')
			&& \Setting::get('plugin.convergePay_set.quick_pay.is_open')
			&& \Setting::get('plugin.convergePay_set.quick_pay.private_key')
			&& \Setting::get('plugin.convergePay_set.quick_pay.platform_public_key');
	}
}