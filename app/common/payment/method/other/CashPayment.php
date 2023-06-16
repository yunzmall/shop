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
use app\common\payment\setting\other\CashSetting;

class CashPayment extends BasePayment
{
	public $code = 'cashPay';
	
	public function __construct(CashSetting $paymentSetting)
	{
		$this->setSetting($paymentSetting);
	}
}