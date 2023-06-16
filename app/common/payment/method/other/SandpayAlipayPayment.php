<?php
/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 2021/12/17
 * Time: 19:04
 */

namespace app\common\payment\method\other;

use app\common\payment\method\BasePayment;
use app\common\payment\setting\other\SandpayAlipaySetting;

class SandpayAlipayPayment extends BasePayment
{
    public $code = 'sandpayAlipay';

    public function __construct(SandpayAlipaySetting $paymentSetting)
    {
        $this->setSetting($paymentSetting);
    }
}