<?php
/**
 * Created by PhpStorm.
 * 
 *
 *
 * Date: 2023-01-11
 * Time: 14:42
 */

namespace app\common\models\order;


use app\common\models\BaseModel;

class GoodsTradeLog extends BaseModel
{
    public $table = 'yz_goods_trade_log';
    protected $guarded = [''];
}