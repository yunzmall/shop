<?php
/**
 * Created by PhpStorm.
 *
 * 
 *
 * Date: 2022/5/24
 * Time: 10:55
 */

namespace app\common\payment\method\converge;

use app\common\payment\method\BasePayment;
use app\common\payment\setting\converge\ConvergeUnionPaySetting;


class ConvergeUnionPayPayment extends BasePayment
{
    public $code = 'convergeUnionPay';

    public function __construct(ConvergeUnionPaySetting $paymentSetting)
    {
        $this->setSetting($paymentSetting);
    }
}