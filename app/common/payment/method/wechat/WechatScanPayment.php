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
use app\common\payment\setting\wechat\WechatScanSetting;

class WechatScanPayment extends BasePayment
{
	public $code = 'WechatScan';
	
	public function __construct(WechatScanSetting $paymentSetting)
	{
		$this->setSetting($paymentSetting);
	}
}