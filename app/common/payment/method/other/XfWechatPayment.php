<?php
/**
 * Created by PhpStorm.
 * 
 * 
 *
 * Date: 2021/8/26
 * Time: 14:02
 */

namespace app\common\payment\method\other;


use app\common\payment\method\BasePayment;
use app\common\payment\setting\other\XfWechatSetting;
use app\common\payment\setting\other\YopAlipaySetting;

class XfWechatPayment extends BasePayment
{
	public $code = 'xfpayWechat';
	
	public function __construct(XfWechatSetting $paymentSetting)
	{
		$this->setSetting($paymentSetting);
	}
}