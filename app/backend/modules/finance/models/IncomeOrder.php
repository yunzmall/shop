<?php
/**
 * Created by PhpStorm.
 * Author:
 * Date: 2017/4/5
 * Time: 下午7:36
 */

namespace app\backend\modules\finance\models;


class IncomeOrder extends \app\common\models\finance\IncomeOrder
{
    protected $appends = ['status_name', 'pay_type_name'];

    
}