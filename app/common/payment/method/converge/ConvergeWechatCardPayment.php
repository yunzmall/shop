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
use app\common\payment\setting\converge\ConvergeWechatCardSetting;
use app\common\payment\setting\converge\ConvergeWechatSetting;

class ConvergeWechatCardPayment extends BasePayment
{
	public $code = 'convergeWechatCardPay';

	public function __construct(ConvergeWechatCardSetting $paymentSetting)
	{
		$this->setSetting($paymentSetting);
	}
}