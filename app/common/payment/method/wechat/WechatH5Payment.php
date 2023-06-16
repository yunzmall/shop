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
use app\common\payment\setting\wechat\WechatH5Setting;

class WechatH5Payment extends BasePayment
{
	public $code = 'wechatH5';
	
	public function __construct(WechatH5Setting $paymentSetting)
	{
		$this->setSetting($paymentSetting);
	}
}