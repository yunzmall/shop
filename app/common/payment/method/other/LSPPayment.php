<?php

namespace app\common\payment\method\other;

use app\common\payment\method\BasePayment;
use app\common\payment\setting\other\LSPSetting;

class LSPPayment extends BasePayment
{
    public $code = 'LSP';

    public function __construct(LSPSetting $paymentSetting)
    {
        $this->setSetting($paymentSetting);
    }
}