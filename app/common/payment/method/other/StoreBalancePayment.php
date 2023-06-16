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
use app\common\payment\setting\other\BalanceSetting;
use app\common\payment\setting\other\StoreBalanceSetting;

class StoreBalancePayment extends BasePayment
{
	public $code = 'storeBalance';

	public function __construct(StoreBalanceSetting $paymentSetting)
	{
		$this->setSetting($paymentSetting);
	}
}