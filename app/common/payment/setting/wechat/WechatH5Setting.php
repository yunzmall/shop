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

class WechatH5Setting extends BasePaymentSetting
{
	public function canUse()
	{
		return request()->input('type') != 2
			&& request()->input('type') != 7
			&& (request()->input('type') != 1 || \Setting::get('shop.member')['wechat_login_mode'] == '1')
			&& \Setting::get('shop.pay.weixin')
			&& \Setting::get('shop.pay.wechat_h5');
	}

	public function getWeight()
	{
		return 999;
	}
}