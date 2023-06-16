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

class HkScanAlipaySetting extends BasePaymentSetting
{
	public function canUse()
	{
		return app('plugins')->isEnabled('hk-pay')
			&& \Setting::get('plugin.hk_pay_set.is_open_ali_pay') == 1;
	}
}