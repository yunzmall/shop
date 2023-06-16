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
use app\common\payment\setting\alipay\AlipayAppSetting;

class AlipayAppPayment extends BasePayment
{
	public $code = 'alipayApp';
	
	public function __construct(AlipayAppSetting $paymentSetting)
	{
		$this->setSetting($paymentSetting);
	}
}