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
use app\common\payment\setting\other\TouTiaoAlipaySetting;

class TouTiaoAlipayPayment extends BasePayment
{
	public $code = 'toutiaoPayAlipay';
	
	public function __construct(TouTiaoAlipaySetting $paymentSetting)
	{
		$this->setSetting($paymentSetting);
	}
}