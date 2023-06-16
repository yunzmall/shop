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
use app\common\payment\setting\alipay\AlipayFaceSetting;

class AlipayFacePayment extends BasePayment
{
	public $code = 'AlipayFace';
	
	public function __construct(AlipayFaceSetting $paymentSetting)
	{
		$this->setSetting($paymentSetting);
	}
}