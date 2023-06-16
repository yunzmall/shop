<?php
/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 2021/8/26
 * Time: 14:02
 */

namespace app\common\payment\setting\alipay;


use app\common\payment\setting\BasePaymentSetting;

class AlipayFaceSetting extends BasePaymentSetting
{
	public function canUse()
	{
		return request()->get('type') == 9;
	}

	public function getWeight()
	{
		return 899;
	}
}