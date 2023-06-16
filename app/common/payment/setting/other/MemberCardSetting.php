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

class MemberCardSetting extends BasePaymentSetting
{
	public function canUse()
	{
		return request()->input('type') != 7
			&& request()->input('type') != 53
			&& app('plugins')->isEnabled('pet')
			&& \Setting::get('plugin.pet')['is_open_pet'] == 1;
	}
}