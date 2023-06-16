<?php
/**
 * Created by PhpStorm.
 * User: blank
 * Date: 2022/4/14
 * Time: 18:43
 */

namespace app\common\models\order;


use app\common\models\BaseModel;

class OrderFreightDeduction extends BaseModel
{
    public $table = 'yz_order_freight_deduction';
    protected $fillable = [];
    protected $guarded = ['id'];

    public function save(array $options = [])
    {
        return parent::save($options);
    }
}