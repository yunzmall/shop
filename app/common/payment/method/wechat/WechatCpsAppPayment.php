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

class WechatCpsAppPayment extends BasePayment
{
	public $code = 'wechatCpsAppPay';
	
	public function __construct(WechatCpsAppSetting $paymentSetting)
	{
		$this->setSetting($paymentSetting);
	}
}