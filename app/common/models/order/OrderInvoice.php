<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/9/24
 * Time: 10:15
 */

namespace app\common\models\order;


use app\common\models\BaseModel;

/**
 * 订单发票记录
 * Class OrderInvoice
 * @package app\common\models\order
 */
class OrderInvoice extends BaseModel
{
    protected $table = 'yz_order_invoice';


    protected $guarded = ['id'];


    protected $attributes = [
        'uniacid' => 0,
    ];

}