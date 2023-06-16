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
use app\common\payment\setting\wechat\WechatMinSetting;

class WechatMinPayment extends BasePayment
{
	public $code = 'wechatMinPay';
	
	public function __construct(WechatMinSetting $paymentSetting)
	{
		$this->setSetting($paymentSetting);
	}
}