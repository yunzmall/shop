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
use app\common\payment\setting\other\YopAlipaySetting;

class YopAlipayPayment extends BasePayment
{
	public $code = 'yopAlipay';
	
	public function __construct(YopAlipaySetting $paymentSetting)
	{
		$this->setSetting($paymentSetting);
	}
}