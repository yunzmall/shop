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
use app\common\payment\setting\other\PayPalSetting;

class PayPalPayment extends BasePayment
{
	public $code = 'payPal';
	
	public function __construct(PayPalSetting $paymentSetting)
	{
		$this->setSetting($paymentSetting);
	}
}