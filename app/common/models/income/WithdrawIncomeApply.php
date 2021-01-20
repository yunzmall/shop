<?php
/**
 * Created by PhpStorm.
 * User: Merlin
 * Date: 2020/10/9
 * Time: 16:26
 */

namespace app\common\models\income;


use app\common\models\BaseModel;

class WithdrawIncomeApply extends BaseModel
{
    public $table = 'yz_withdraw_income_apply';

    protected $guarded= [];

    protected $appends = [];
}