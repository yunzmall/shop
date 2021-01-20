<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/8/21
 * Time: 10:30
 */

namespace app\common\models\order;


use app\common\models\BaseModel;

class OrderServiceFee extends BaseModel
{
    protected $table = 'yz_order_service_fee';

    protected $fillable = [];
    protected $guarded = ['id'];

    protected $attributes = [];
}