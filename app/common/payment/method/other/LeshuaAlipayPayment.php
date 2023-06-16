<?php
/**
 * Created by PhpStorm.
 *
 * 
 *
 * Date: 2022/3/31
 * Time: 09:13
 */

namespace app\common\payment\method\other;

use app\common\payment\method\BasePayment;
use app\common\payment\setting\other\LeshuaAlipaySetting;

class LeshuaAlipayPayment extends BasePayment
{
    public $code = 'leshuaAlipay';

    public function __construct(LeshuaAlipaySetting $paymentSetting)
    {
        $this->setSetting($paymentSetting);
    }
}
