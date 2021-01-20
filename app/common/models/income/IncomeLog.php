<?php
/**
 * Created by PhpStorm.
 * User: Merlin
 * Date: 2020/9/29
 * Time: 17:42
 */

namespace app\common\models\income;


use app\common\models\BaseModel;

class IncomeLog extends BaseModel
{
    public $table = 'yz_income_log';

    protected $guarded= [];

    protected $appends = [];
}