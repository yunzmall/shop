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
use app\common\services\password\PasswordService;

class BalanceSetting extends BasePaymentSetting
{
	public function canUse()
	{
		return \Setting::get('shop.pay.credit') && !\Setting::get('finance.balance.balance_deduct');
	}

	public function getName()
	{
		return \Setting::get('shop.shop.credit')?:parent::getName();
	}

	public function getWeight()
	{
		return 1001;
	}

	public function needPassword()
	{
		return (new PasswordService())->isNeed('balance', 'pay');
	}
}