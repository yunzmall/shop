<?php

namespace app\frontend\modules\withdraw\services;

use app\common\facades\Setting;

class WithdrawApplySetService extends WithdrawApplySetBaseService
{
    public function checkPayWay($payWay): bool
    {
        $withdraw_set = Setting::get('withdraw.income');
        foreach ($withdraw_set as $key => $item) {
            if ($key == $payWay && !$item) {
                return false;
//                return $this->errorJson('该提现方式已关闭!');
            }
        }
        return true;
    }
}