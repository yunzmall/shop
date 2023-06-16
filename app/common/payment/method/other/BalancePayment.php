<?php
/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 2021/8/26
 * Time: 14:02
 */

namespace app\common\payment\method\other;


use app\common\payment\method\BasePayment;
use app\common\payment\setting\other\BalanceSetting;

class BalancePayment extends BasePayment
{
	public $code = 'balance';

	public function __construct(BalanceSetting $paymentSetting)
	{
		$this->setSetting($paymentSetting);
	}
}