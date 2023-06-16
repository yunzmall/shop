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
use app\common\payment\setting\other\AggregateWechatSetting;

class AggregateWechatPayment extends BasePayment
{
	public $code = 'wechatAggregatePay';

	public function __construct(AggregateWechatSetting $paymentSetting)
	{
		$this->setSetting($paymentSetting);
	}
}