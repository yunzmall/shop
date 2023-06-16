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
use app\common\payment\setting\wechat\WechatFaceSetting;

class WechatFacePayment extends BasePayment
{
	public $code = 'WechatFace';
	
	public function __construct(WechatFaceSetting $paymentSetting)
	{
		$this->setSetting($paymentSetting);
	}
}