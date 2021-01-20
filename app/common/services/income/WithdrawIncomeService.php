<?php
/**
 * Created by PhpStorm.
 * User: Merlin
 * Date: 2020/10/12
 * Time: 17:55
 */

namespace app\common\services\income;



use app\common\models\income\WithdrawIncome;
use app\common\models\income\WithdrawIncomeApply;

class WithdrawIncomeService
{
    public static function insert($withdraw_model)
    {
        $withdraw_id = $withdraw_model->id;
        if (!$withdraw_id) {
            return;
        }
        $income_ids = WithdrawIncomeApply::where('withdraw_id',$withdraw_id)->where('status',WithdrawIncomeApplyService::APPLY_AUDIT)->pluck('income_id')->toArray();
        $data = [];
        foreach ($income_ids as $income_id) {
            $data[] = [
                'uniacid'     => \YunShop::app()->uniacid,
                'member_id'   => $withdraw_model->member_id,
                'withdraw_id' => $withdraw_model->id,
                'income_id'   => $income_id,
                'created_at'  => time(),
                'updated_at'  => time(),
            ];
        }
        if (!empty($data) && WithdrawIncome::insert($data)) {
            return true;
        }
        return false;
    }

    public static function delete($withdraw_model)
    {
        $withdraw_id = $withdraw_model->id;
        if (!$withdraw_id) {
            return false;
        }
        if (WithdrawIncome::where('withdraw_id',$withdraw_id)->delete()) {
            return true;
        }
        return false;
    }
}