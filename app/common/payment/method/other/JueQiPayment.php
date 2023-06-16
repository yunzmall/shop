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
use app\common\payment\setting\other\JueQiSetting;

class JueQiPayment extends BasePayment
{
	public $code = 'jueqi-pay';
	
	public function __construct(JueQiSetting $paymentSetting)
	{
		$this->setSetting($paymentSetting);
	}
}