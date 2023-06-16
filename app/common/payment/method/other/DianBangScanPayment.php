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
use app\common\payment\setting\other\DianBangScanSetting;

class DianBangScanPayment extends BasePayment
{
	public $code = 'DianBangScan';
	
	public function __construct(DianBangScanSetting $paymentSetting)
	{
		$this->setSetting($paymentSetting);
	}
}