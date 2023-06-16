<?php
/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 2022/6/30
 * Time: 15:16
 */
namespace app\common\payment\method\other;

use app\common\payment\method\BasePayment;
use app\common\payment\setting\other\SilverPointPayUnionSetting;

class SilverPointPayUnionPayPayment extends BasePayment
{
    public $code = 'silverPointUnionPay';

    public function __construct(SilverPointPayUnionSetting $paymentSetting)
    {
        $this->setSetting($paymentSetting);
    }
}