<?php
/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 2022/4/2
 * Time: 18:47
 */

namespace app\common\payment\method\other;

use app\common\payment\method\BasePayment;
use app\common\payment\setting\other\LeshuaPosSetting;

class LeshuaPosPayment extends BasePayment
{
    public $code = 'leshuaPos';

    public function __construct(LeshuaPosSetting $paymentSetting)
    {
        $this->setSetting($paymentSetting);
    }
}