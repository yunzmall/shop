<?php
/**
 * Created by PhpStorm.
 * Author:
 * Date: 2018/12/18
 * Time: 上午11:32
 */

namespace app\common\models;
use app\common\models\PayType;


class PayTypeGroup extends BaseModel
{
    public $table = 'yz_pay_type_group';

    public function hasManyPayType()
    {
        return $this->hasMany(PayType::class,'group_id','id');
    }

}