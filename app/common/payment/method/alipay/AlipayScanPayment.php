<?php
/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 2021/8/26
 * Time: 14:02
 */

namespace app\common\payment\method\alipay;


use app\common\payment\method\BasePayment;
use app\common\payment\setting\alipay\AlipayScanSetting;

class AlipayScanPayment extends BasePayment
{
	public $code = 'AlipayScan';
	
	public function __construct(AlipayScanSetting $paymentSetting)
	{
		$this->setSetting($paymentSetting);
	}
}