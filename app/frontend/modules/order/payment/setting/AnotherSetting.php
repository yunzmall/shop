<?php
/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 2021/9/3
 * Time: 18:49
 */

namespace app\frontend\modules\order\payment\setting;

use app\common\payment\setting\other\AnotherSetting as BaseAnotherSetting;

class AnotherSetting extends BaseAnotherSetting
{
	public function canUse()
	{
		return request()->input('type') != 7
			&& \Setting::get('shop.pay.another')
			&& (!request()->input('pid') || request()->input('pid') == \YunShop::app()->getMemberId());
	}
}