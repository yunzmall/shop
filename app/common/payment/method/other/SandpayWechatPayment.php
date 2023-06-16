<?php
/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 2021/12/17
 * Time: 19:03
 */

namespace app\common\payment\method\other;

use app\common\payment\method\BasePayment;
use app\common\payment\setting\other\SandpayWechatSetting;

class SandpayWechatPayment extends BasePayment
{
    public $code = 'sandpayWechat';

    public function __construct(SandpayWechatSetting $paymentSetting)
    {
        $this->setSetting($paymentSetting);
    }
}