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
use app\common\payment\setting\wechat\WechatAppSetting;

class WechatAppPayment extends BasePayment
{
	public $code = 'wechatApp';
	
	public function __construct(WechatAppSetting $paymentSetting)
	{
		$this->setSetting($paymentSetting);
	}
}