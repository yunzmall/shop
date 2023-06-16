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
use app\common\payment\setting\other\MemberCardSetting;

class MemberCardPayment extends BasePayment
{
	public $code = 'MemberCard';
	
	public function __construct(MemberCardSetting $paymentSetting)
	{
		$this->setSetting($paymentSetting);
	}
}