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

class XfWechatSetting extends BasePaymentSetting
{
	public function canUse()
	{
		return request()->input('type') != 5
			&& app('plugins')->isEnabled('xfpay')
			&& \Setting::get('plugin.xfpay_set.xfpay.pay_type.wechat.enabled');
	}
}