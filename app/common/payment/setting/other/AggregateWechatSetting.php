<?php
/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 2021/8/31
 * Time: 17:50
 */

namespace app\common\payment\setting\other;


use app\common\payment\setting\BasePaymentSetting;

class AggregateWechatSetting extends BasePaymentSetting
{
	public function canUse()
	{
		return false;
	}
}