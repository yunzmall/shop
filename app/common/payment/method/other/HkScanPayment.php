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
use app\common\payment\setting\other\HkScanSetting;

class HkScanPayment extends BasePayment
{
	public $code = 'HkScanPay';
	
	public function __construct(HkScanSetting $paymentSetting)
	{
		$this->setSetting($paymentSetting);
	}
}