<?php
/**
 * Created by PhpStorm.
 * User: Merlin
 * Date: 2020/10/12
 * Time: 17:56
 */

namespace app\common\models\income;


use app\common\models\BaseModel;

class WithdrawIncome extends BaseModel
{
    public $table = 'yz_withdraw_income';

    protected $guarded= [];

    protected $appends = [];
}