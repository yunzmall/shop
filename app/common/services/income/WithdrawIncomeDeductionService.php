<?php
/**
 * Created by PhpStorm.
 * User: Merlin
 * Date: 2020/10/9
 * Time: 16:25
 */

namespace app\common\services\income;

use app\common\models\income\WithdrawIncomeDeductionLove;
use Yunshop\Love\Common\Services\LoveChangeService;

class WithdrawIncomeDeductionService
{
     public static function insert($withdraw_model,$need_deduction_love_data = [],$deductionLove = 0)
     {
         if (!$withdraw_model->type_id) {
             return true;
         }
         $income_ids =  array_filter(explode(',',$withdraw_model->type_id));
         $deductionData = [];
         if ($need_deduction_love_data && $deductionLove) {
             foreach ($income_ids as $income_id) {
                 $deductionData[] = [
                     'uniacid'     => \YunShop::app()->uniacid,
                     'member_id'   => $withdraw_model->member_id,
                     'withdraw_id' => $withdraw_model->id,
                     'income_id'   => $income_id,
                     'status'      => 1,//已扣除
                     'created_at'  => time(),
                     'updated_at'  => time(),
                     'need_deduction_love_rate' => $need_deduction_love_data['rate'],
                     'need_deduction_love_type' => $need_deduction_love_data['love_sign']
                 ];
             }

             if ($deductionData) {
                 //扣除爱心值
                 (new LoveChangeService($need_deduction_love_data['love_sign']))->withdrawIncomeDeduction([
                     'member_id'    => $withdraw_model->member_id,
                     'change_value' => $deductionLove,
                     'operator'     => 0,
                     'operator_id'  => 0,
                     'remark'       => '收入提现扣除' . $deductionLove,
                     'relation'     => ''
                 ]);

                 if (count($deductionData) > 5000) {
                     $listDeductionData = collect($deductionData)->chunk(5000)->toArray();
                     foreach ($listDeductionData as $item) {
                         WithdrawIncomeDeductionLove::insert($item);
                     }
                     return true;
                 } else {
                     if (WithdrawIncomeDeductionLove::insert($deductionData)) {
                         return true;
                     }
                 }
             }
         }

         return false;
     }
}