<?php
/**
 * Created by PhpStorm.
 * User: Merlin
 * Date: 2020/10/9
 * Time: 16:25
 */

namespace app\common\services\income;


use app\common\models\income\WithdrawIncomeApply;

class WithdrawIncomeApplyService
{
    const APPLY_AUDIT = 1;
    const APPLY_REBUT = 2;
    const APPLY_INVALID = -1;

     public static function insert($withdraw_model)
     {
         if (!$withdraw_model->type_id) {
             return true;
         }
         $income_ids =  array_filter(explode(',',$withdraw_model->type_id));
         $data = [];
         foreach ($income_ids as $income_id) {
              $data[] = [
                  'uniacid'     => \YunShop::app()->uniacid,
                  'member_id'   => $withdraw_model->member_id,
                  'withdraw_id' => $withdraw_model->id,
                  'income_id'   => $income_id,
                  'status'      =>  0,
                  'created_at'  => time(),
                  'updated_at'  => time(),
              ];
         }
         if (WithdrawIncomeApply::insert($data)) {
             return true;
         }
         return false;
     }

     public static function apply($withdraw_model)
     {
         $audit_ids = $withdraw_model->audit_ids;
         $rebut_ids = $withdraw_model->rebut_ids;
         $invalid_ids = $withdraw_model->invalid_ids;
         if (!empty($audit_ids)) {
			 WithdrawIncomeApply::where('withdraw_id', $withdraw_model->id)->whereIn('income_id', $audit_ids)->update(['status' => self::APPLY_AUDIT]);
		 }
         if (!empty($rebut_ids)) {
			 WithdrawIncomeApply::where('withdraw_id', $withdraw_model->id)->whereIn('income_id', $rebut_ids)->update(['status' => self::APPLY_REBUT]);
		 }
         if (!empty($invalid_ids)) {
			 WithdrawIncomeApply::where('withdraw_id', $withdraw_model->id)->whereIn('income_id', $invalid_ids)->update(['status' => self::APPLY_INVALID]);
		 }
		 return true;
     }

     public static function rebut($withdraw_model)
     {
         WithdrawIncomeApply::where('withdraw_id',$withdraw_model->id)->where('status',self::APPLY_AUDIT)->update(['status'=>self::APPLY_REBUT]);
         return true;
     }
}