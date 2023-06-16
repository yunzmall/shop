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

class ConvergeAlipaySetting extends BasePaymentSetting
{
	public function canUse()
	{
		return request()->input('type') != 2
			&& app('plugins')->isEnabled('converge_pay')
			&& \Setting::get('plugin.convergePay_set.alipay.alipay_status');
	}

	public function getWeight()
	{
		return 899;
	}
}