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
use app\common\payment\setting\other\CodeSciencePayYuSetting;

class CodeSciencePayYuPayment extends BasePayment
{
    public $code = 'codeSciencePayYu';

    public function __construct(CodeSciencePayYuSetting $paymentSetting)
    {
        $this->setSetting($paymentSetting);
    }
}