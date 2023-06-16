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
use app\common\payment\setting\other\LSPWalletSetting;

class LSPWalletPayment extends BasePayment
{
    public $code = 'LSPWallet';

    public function __construct(LSPWalletSetting $paymentSetting)
    {
        $this->setSetting($paymentSetting);
    }
}