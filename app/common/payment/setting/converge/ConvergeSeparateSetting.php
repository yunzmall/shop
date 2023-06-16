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

class ConvergeSeparateSetting extends BasePaymentSetting
{
	public function canUse()
	{
		return app('plugins')->isEnabled('converge-alloc-funds')
			&& \Setting::get('plugin.ConvergeAllocFunds_set.converge_pay_status')
			&& \Setting::get('plugin.ConvergeAllocFunds_set.wechat.wechat_status');
		;
	}
}