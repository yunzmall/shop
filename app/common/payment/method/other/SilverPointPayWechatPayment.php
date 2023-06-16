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
use app\common\payment\setting\other\SilverPointWechatSetting;

class SilverPointPayWechatPayment extends BasePayment
{
    public $code = 'silverPointWechat';

    public function __construct(SilverPointWechatSetting $paymentSetting)
    {
        $this->setSetting($paymentSetting);
    }
}