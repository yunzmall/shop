<?php
/**
 * Created by PhpStorm.
 * 
 *
 *
 * Date: 2023/3/30
 * Time: 17:05
 */

namespace app\common\payment\method\alipay;

use app\common\payment\method\BasePayment;
use app\common\payment\setting\alipay\AlipayPeriodDeductSetting;

class AlipayPeriodDeductPayment extends BasePayment
{
    public $code = 'alipayPeriodDeduct';

    public function __construct(AlipayPeriodDeductSetting $paymentSetting)
    {
        $this->setSetting($paymentSetting);
    }
}