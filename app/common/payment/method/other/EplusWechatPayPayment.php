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
use app\common\payment\setting\other\EplusWechatPaySetting;

class EplusWechatPayPayment extends BasePayment
{
	public $code = 'EplusWechatPay';
	
	public function __construct(EplusWechatPaySetting $paymentSetting)
	{
		$this->setSetting($paymentSetting);
	}
}