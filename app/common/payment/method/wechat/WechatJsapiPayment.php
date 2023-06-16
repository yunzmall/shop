<?php
/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 2021/8/26
 * Time: 14:02
 */

namespace app\common\payment\method\wechat;


use app\common\payment\method\BasePayment;
use app\common\payment\setting\wechat\WechatJsapiSetting;

class WechatJsapiPayment extends BasePayment
{
	public $code = 'WechatJsapi';
	
	public function __construct(WechatJsapiSetting $paymentSetting)
	{
		$this->setSetting($paymentSetting);
	}
}