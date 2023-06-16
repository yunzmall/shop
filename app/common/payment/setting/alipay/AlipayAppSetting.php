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

class AlipayAppSetting extends BasePaymentSetting
{
	public function canUse()
	{
		/**
		 * app端&&开启了支付宝支付
		 */
		return  request()->get('type') == 7 && \Setting::get('shop_app.pay.alipay');
	}


	public function getWeight()
	{
		return 899;
	}
}