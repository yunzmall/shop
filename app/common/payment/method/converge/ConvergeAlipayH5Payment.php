<?php
/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 2022/6/15
 * Time: 17:58
 */

namespace app\common\payment\method\converge;

use app\common\payment\method\BasePayment;
use app\common\payment\setting\converge\ConvergeAlipayH5Setting;


class ConvergeAlipayH5Payment extends BasePayment
{
    public $code = 'convergeAlipayH5Pay';

    public function __construct(ConvergeAlipayH5Setting $paymentSetting)
    {
        $this->setSetting($paymentSetting);
    }
}