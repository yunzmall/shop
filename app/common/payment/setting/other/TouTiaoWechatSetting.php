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

class TouTiaoWechatSetting extends BasePaymentSetting
{
	public function canUse()
	{
		return request()->input('type') == 11 && \Setting::get('plugin.toutiao-mini.wx_switch');
	}
}