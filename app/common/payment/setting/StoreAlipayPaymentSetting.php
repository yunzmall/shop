<?php
/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 2021/8/26
 * Time: 20:02
 */

namespace app\common\payment\setting;


class StoreAlipayPaymentSetting extends AlipayPaymentSetting
{
	public function canUse()
	{
		return true;
	}
}