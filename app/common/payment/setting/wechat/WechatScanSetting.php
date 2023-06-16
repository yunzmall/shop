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

class WechatScanSetting extends BasePaymentSetting
{
	public function canUse()
	{
		return request()->input('type') == 9 || request()->is_store_pos;
	}

	public function getWeight()
	{
		return 999;
	}
}