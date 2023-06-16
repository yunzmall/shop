<?php
/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 2022/3/31
 * Time: 09:14
 */

namespace app\common\payment\method\other;

use app\common\payment\method\BasePayment;
use app\common\payment\setting\other\LeshuaWechatSetting;

class LeshuaWechatPayment extends BasePayment
{
    public $code = 'leshuaWechat';

    public function __construct(LeshuaWechatSetting $paymentSetting)
    {
        $this->setSetting($paymentSetting);
    }
}