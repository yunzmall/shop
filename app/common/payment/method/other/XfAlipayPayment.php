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
use app\common\payment\setting\other\XfAlipaySetting;
use app\common\payment\setting\other\YopAlipaySetting;

class XfAlipayPayment extends BasePayment
{
	public $code = 'xfpayAlipay';
	
	public function __construct(XfAlipaySetting $paymentSetting)
	{
		$this->setSetting($paymentSetting);
	}
}