<?php
/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 2021/8/31
 * Time: 17:50
 */

namespace app\common\payment\method\other;


use app\common\payment\method\BasePayment;
use app\common\payment\setting\other\AggregateAlipaySetting;

class AggregateAlipayPayment extends BasePayment
{
	public $code = 'alipayAggregatePay';

	public function __construct(AggregateAlipaySetting $paymentSetting)
	{
		$this->setSetting($paymentSetting);
	}
}