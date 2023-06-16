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
use app\common\payment\setting\other\YopWechatSetting;

class YopWechatPayment extends BasePayment
{
	public $code = 'yop';
	
	public function __construct(YopWechatSetting $paymentSetting)
	{
		$this->setSetting($paymentSetting);
	}
}