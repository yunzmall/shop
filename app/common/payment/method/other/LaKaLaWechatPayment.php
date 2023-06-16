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
use app\common\payment\setting\other\LaKaLaWechatSetting;

class LaKaLaWechatPayment extends BasePayment
{
    public $code = 'lakalaWechatPay';

    public function __construct(LaKaLaWechatSetting $paymentSetting)
    {
        $this->setSetting($paymentSetting);
    }
}