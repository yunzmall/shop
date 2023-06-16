<?php
/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 2022/6/21
 * Time: 17:07
 */

namespace app\frontend\modules\order\payment\setting;


class ConvergeAlipayH5Setting extends \app\common\payment\setting\converge\ConvergeAlipayH5Setting
{
    public function canUse()
    {
        return !in_array(request()->input('type'),[1,2])
            && app('plugins')->isEnabled('converge_pay')
            && \Setting::get('plugin.convergePay_set')['alipay']['alipay_h5_status'];
    }
}