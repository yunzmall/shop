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
use app\common\payment\setting\other\SilverPointPayAlipaySetting;

class SilverPointPayAlipayPayment extends BasePayment
{
    public $code = 'silverPointAlipay';

    public function __construct(SilverPointPayAlipaySetting $paymentSetting)
    {
        $this->setSetting($paymentSetting);
    }
}