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

class WechatSetting extends BasePaymentSetting
{
	public function canUse()
	{
		return request()->input('type') != 5
		    && request()->input('type') != 7
		    && request()->input('type') != 2
			&& \Setting::get('shop.pay.weixin')
			&& \Setting::get('shop.pay.weixin_pay')
            && \Setting::get('shop.member')['wechat_login_mode'] != '1';
	}

	public function getWeight()
	{
		return 999;
	}
}