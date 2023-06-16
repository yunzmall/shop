<?php
/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 2022/1/13
 * Time: 17:34
 */

namespace app\outside\modes;


use app\common\models\BaseModel;

class OutsideOrderHasManyOrder extends BaseModel
{
    public $table = 'yz_outside_order_has_many_order';

    protected $guarded = ['id'];
}
