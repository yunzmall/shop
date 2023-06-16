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
use app\common\payment\setting\converge\ConvergeSeparateAlipaySetting;

class ConvergeSeparateAlipayPayment extends BasePayment
{
	public $code = 'convergeAliPaySeparate';
	
	public function __construct(ConvergeSeparateAlipaySetting $paymentSetting)
	{
		$this->setSetting($paymentSetting);
	}
}