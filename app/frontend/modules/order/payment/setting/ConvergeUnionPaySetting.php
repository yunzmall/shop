<?php
/**
 * Created by PhpStorm.
 * 
 *
 *
 * Date: 2022/5/24
 * Time: 11:42
 */

namespace app\frontend\modules\order\payment\setting;


class ConvergeUnionPaySetting extends \app\common\payment\setting\converge\ConvergeUnionPaySetting
{
    public function canUse()
    {
        return in_array(request()->input('type'), [5,1])
            && app('plugins')->isEnabled('converge_pay')
            && \Setting::get('plugin.convergePay_set.converge_union_pay');
    }
}