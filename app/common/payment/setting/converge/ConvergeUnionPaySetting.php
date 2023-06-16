<?php
/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 2022/5/24
 * Time: 10:52
 */

namespace app\common\payment\setting\converge;

use app\common\payment\setting\BasePaymentSetting;

class ConvergeUnionPaySetting extends BasePaymentSetting
{
    public function canUse()
    {
       return false;
    }
}