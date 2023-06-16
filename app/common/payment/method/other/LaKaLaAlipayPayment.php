<?php
/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 2022/2/17
 * Time: 16:59
 */
namespace app\common\payment\method\other;

use app\common\payment\method\BasePayment;
use app\common\payment\setting\other\LaKaLaAlipaySetting;

class LaKaLaAlipayPayment extends BasePayment
{
    public $code = 'lakalaAlipay';

    public function __construct(LaKaLaAlipaySetting $paymentSetting)
    {
        $this->setSetting($paymentSetting);
    }
}