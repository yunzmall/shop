<?php
/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 2021/8/26
 * Time: 14:02
 */

namespace app\common\payment\method\converge;

use app\common\payment\method\BasePayment;
use app\common\payment\setting\converge\ConvergeWechatSetting;

class ConvergeWechatPayment extends BasePayment
{
	public $code = 'convergePayWechat';
	
	public function __construct(ConvergeWechatSetting $paymentSetting)
	{
		$this->setSetting($paymentSetting);
	}
}