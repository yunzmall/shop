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
use app\common\payment\setting\other\HkScanAlipaySetting;

class HkScanAlipayPayment extends BasePayment
{
	public $code = 'HkScanAlipay';
	
	public function __construct(HkScanAlipaySetting $paymentSetting)
	{
		$this->setSetting($paymentSetting);
	}
}