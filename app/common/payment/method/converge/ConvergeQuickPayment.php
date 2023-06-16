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
use app\common\payment\setting\converge\ConvergeQuickSetting;

class ConvergeQuickPayment extends BasePayment
{
	public $code = 'convergeQuickPay';
	
	public function __construct(ConvergeQuickSetting $paymentSetting)
	{
		$this->setSetting($paymentSetting);
	}
}