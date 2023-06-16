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
use app\common\payment\setting\other\AnotherSetting;

class AnotherPayment extends BasePayment
{
	public $code = 'anotherPay';
	
	public function __construct(AnotherSetting $paymentSetting)
	{
		$this->setSetting($paymentSetting);
	}
}