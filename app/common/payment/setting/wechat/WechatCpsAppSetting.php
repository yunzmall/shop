<?php
/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 2021/8/26
 * Time: 14:02
 */

namespace app\common\payment\setting\wechat;


use app\common\payment\setting\BasePaymentSetting;

class WechatCpsAppSetting extends BasePaymentSetting
{
	public function canUse()
	{
		return false;
	}

	public function getWeight()
	{
		return 999;
	}
}