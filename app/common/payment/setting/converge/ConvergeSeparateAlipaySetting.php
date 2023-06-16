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

class ConvergeSeparateAlipaySetting extends BasePaymentSetting
{
	public function canUse()
	{
		return request()->input('type') != 2
			&& app('plugins')->isEnabled('converge-alloc-funds')
			&& \Setting::get('plugin.ConvergeAllocFunds_set.converge_pay_status') == "1"
			&& \Setting::get('plugin.ConvergeAllocFunds_set.alipay.alipay_status')  == "1";

	}
}