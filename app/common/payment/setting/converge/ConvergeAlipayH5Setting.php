<?php
/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 2022/6/15
 * Time: 17:59
 */

namespace app\common\payment\setting\converge;

use app\common\payment\setting\BasePaymentSetting;

class ConvergeAlipayH5Setting  extends BasePaymentSetting
{
    public function canUse()
    {
        return false;

//        return !in_array(request()->input('type'),[1,2])
//            && app('plugins')->isEnabled('converge_pay')
//            && \Setting::get('plugin.convergePay_set')['alipay']['alipay_h5_status'];
    }

    public function getWeight()
    {
        return 899;
    }
}