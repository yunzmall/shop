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
use app\common\payment\setting\wechat\WechatSetting;

class WechatPayment extends BasePayment
{
	public $code = 'wechatPay';
	
	public function __construct(WechatSetting $paymentSetting)
	{
		$this->setSetting($paymentSetting);
	}
}