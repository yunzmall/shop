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
use app\common\payment\setting\converge\ConvergeAlipayCardSetting;
use app\common\payment\setting\converge\ConvergeWechatSetting;

class ConvergeAlipayCardPayment extends BasePayment
{
	public $code = 'convergeAlipayCardPay';

	public function __construct(ConvergeAlipayCardSetting $paymentSetting)
	{
		$this->setSetting($paymentSetting);
	}
}