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
use app\common\payment\setting\other\ConfirmSetting;

class ConfirmPayment extends BasePayment
{
	public $code = 'confirmPay';
	
	public function __construct(ConfirmSetting $paymentSetting)
	{
		$this->setSetting($paymentSetting);
	}
}