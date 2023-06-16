<?php
/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 2022/12/1
 * Time: 13:55
 */

namespace app\common\payment\method\other;

use app\common\payment\method\BasePayment;
use app\common\payment\setting\other\AuthPaySetting;

class AuthPayPayment extends BasePayment
{
    public $code = 'authPay';

    public function __construct(AuthPaySetting $paymentSetting)
    {
        $this->setSetting($paymentSetting);
    }
}