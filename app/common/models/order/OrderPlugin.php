<?php
/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 2021/9/17
 * Time: 14:14
 */

namespace app\common\models\order;

use app\common\models\BaseModel;

class OrderPlugin extends BaseModel
{
    public $table = 'yz_order_plugin';
    protected $fillable = [];
    protected $guarded = [];
}