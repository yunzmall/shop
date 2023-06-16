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

class DianBangScanSetting extends BasePaymentSetting
{
	public function canUse()
	{
		return request()->input('type') != 7
			&&	app('plugins')->isEnabled('dian-bang-scan')
			&& \Setting::get('plugin.dian-bang-scan')['switch'] == 1;
	}
}