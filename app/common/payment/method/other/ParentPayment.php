<?php
/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 2021/9/10
 * Time: 18:33
 */

namespace app\common\payment\method\other;


use app\common\payment\method\BasePayment;
use app\common\payment\setting\other\ParentSetting;

class ParentPayment extends BasePayment
{
	public $code = 'parentPayment';

	public function __construct(ParentSetting $paymentSetting)
	{
		$this->setSetting($paymentSetting);
	}
}