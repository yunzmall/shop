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
use app\common\payment\setting\alipay\AlipayJsapiSetting;

class AlipayJsapiPayment extends BasePayment
{
	public $code = 'AlipayJsapi';
	
	public function __construct(AlipayJsapiSetting $paymentSetting)
	{
		$this->setSetting($paymentSetting);
	}
}