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
use app\common\payment\setting\other\StoreSetting;

class StorePayment extends BasePayment
{
	public $code = 'store';
	
	public function __construct(StoreSetting $paymentSetting)
	{
		$this->setSetting($paymentSetting);
	}
}