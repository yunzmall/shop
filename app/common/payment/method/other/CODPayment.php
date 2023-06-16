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
use app\common\payment\setting\other\CODSetting;

class CODPayment extends BasePayment
{
	public $code = 'COD';
	
	public function __construct(CODSetting $paymentSetting)
	{
		$this->setSetting($paymentSetting);
	}
}