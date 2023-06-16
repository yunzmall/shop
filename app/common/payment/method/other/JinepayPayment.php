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
use app\common\payment\setting\other\JinepaySetting;

class JinepayPayment extends BasePayment
{
    public $code = 'jinepayH5';

    public function __construct(JinepaySetting $paymentSetting)
    {
        $this->setSetting($paymentSetting);
    }
}