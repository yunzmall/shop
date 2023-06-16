<?php
/**
 * Created by PhpStorm.
 *
 * 
 *
 * Date: 2023/2/28
 * Time: 13:47
 */

namespace app\common\payment\setting\other;

use app\common\payment\setting\BasePaymentSetting;
use Yunshop\Freelogin\common\service\CommonService;

class ThirdPartyMiniSetting extends BasePaymentSetting
{
    public function canUse()
    {
        return app('plugins')->isEnabled('freelogin') && CommonService::verifyPay();
    }
}