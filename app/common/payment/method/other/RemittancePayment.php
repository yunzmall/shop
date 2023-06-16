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
use app\common\payment\setting\other\RemittanceSetting;

class RemittancePayment extends BasePayment
{
	public $code = 'remittance';
	
	public function __construct(RemittanceSetting $paymentSetting)
	{
		$this->setSetting($paymentSetting);
	}
}