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
use app\common\payment\setting\other\DcmScanSetting;

class DcmScanPayment extends BasePayment
{
	public $code = 'dcmScanPay';
	
	public function __construct(DcmScanSetting $paymentSetting)
	{
		$this->setSetting($paymentSetting);
	}
}