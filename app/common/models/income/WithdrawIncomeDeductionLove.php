<?php
/**
 * Created by PhpStorm.
 * User: Merlin
 * Date: 2020/10/9
 * Time: 16:26
 */

namespace app\common\models\income;


use app\common\models\BaseModel;
use app\common\models\Income;

class WithdrawIncomeDeductionLove extends BaseModel
{
    public $table = 'yz_withdraw_income_deduction_love';

    const STATUS_DEDUCTION = 1;//扣除
    const STATUS_DEDUCTION_REFUND = -1;//扣除退还

    protected $guarded= [];

    protected $appends = [];

    public function hasOneMemberIncome()
    {
        return $this->hasOne(Income::class, 'id', 'income_id');
    }
}