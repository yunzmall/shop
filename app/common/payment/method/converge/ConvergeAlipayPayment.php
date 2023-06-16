<?php
/**
 * Created by PhpStorm.
 * 
 *
 *
 * Date: 2021/8/26
 * Time: 14:02
 */

namespace app\common\payment\method\converge;

use app\common\payment\method\BasePayment;
use app\common\payment\setting\converge\ConvergeAlipaySetting;

class ConvergeAlipayPayment extends BasePayment
{
	public $code = 'convergePayAlipay';
	
	public function __construct(ConvergeAlipaySetting $paymentSetting)
	{
		$this->setSetting($paymentSetting);
	}
}