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
use app\common\payment\setting\wechat\WechatNativeSetting;

class WechatNativePayment extends BasePayment
{
	public $code = 'wechatNative';
	
	public function __construct(WechatNativeSetting $paymentSetting)
	{
		$this->setSetting($paymentSetting);
	}
}