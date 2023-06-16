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
use app\common\payment\setting\wechat\WechatCpsAppSetting;
use app\common\payment\setting\wechat\WechatMicroSetting;

class WechatMicroPayment extends BasePayment
{
	public $code = 'wechatMicroPay';
	
	public function __construct(WechatMicroSetting $paymentSetting)
	{
		$this->setSetting($paymentSetting);
	}
}