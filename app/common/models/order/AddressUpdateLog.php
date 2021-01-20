<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/7/27
 * Time: 15:22
 */

namespace app\common\models\order;


use app\common\models\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;

class AddressUpdateLog extends BaseModel
{

    use SoftDeletes;

    protected $table = 'yz_order_address_update_log';

    protected $guarded = ['id'];


    protected $attributes = [
        'street_id' => 0,
    ];


    protected $hidden = [
        'deleted_at'
    ];
}