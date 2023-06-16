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

class YopAlipaySetting extends BasePaymentSetting
{
	public function canUse()
	{
		return request()->input('type') != 7
		    && request()->input('type') != 2
			&& app('plugins')->isEnabled('yop-pay')
			&& !is_null($set = \Yunshop\YopPay\models\YopSetting::getSet())
			&& $set['yop_alipay_pay'] != 1
			&& !empty($set['merchant_no']);
	}
}