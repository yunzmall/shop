<?php
/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 2023/2/28
 * Time: 13:46
 */

namespace app\common\payment\method\other;

use app\common\payment\method\BasePayment;
use app\common\payment\setting\other\ThirdPartyMiniSetting;

class ThirdPartyMiniPayment extends BasePayment
{
    public $code = 'thirdPartyMiniPay';//todo 第三方小程序执行支付，我们这走个流程，返回参数给第三方，奇葩需求

    public function __construct(ThirdPartyMiniSetting $paymentSetting)
    {
        $this->setSetting($paymentSetting);
    }
}