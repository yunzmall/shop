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
use app\common\payment\setting\converge\ConvergeSeparateSetting;

class ConvergeSeparatePayment extends BasePayment
{
	public $code = 'convergePaySeparate';
	
	public function __construct(ConvergeSeparateSetting $paymentSetting)
	{
		$this->setSetting($paymentSetting);
	}
}