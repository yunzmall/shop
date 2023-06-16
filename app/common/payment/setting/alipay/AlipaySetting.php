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

class AlipaySetting extends BasePaymentSetting
{
	public function canUse()
	{
		return request()->input('type') != 7 && request()->input('type') != 2 && \Setting::get('shop.pay.alipay');
	}

	public function getWeight()
	{
		return 899;
	}

}