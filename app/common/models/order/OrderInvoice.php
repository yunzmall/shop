<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/9/24
 * Time: 10:15
 */

namespace app\common\models\order;


use app\backend\modules\order\models\VueOrder;
use app\common\models\BaseModel;
use app\common\models\Order;

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

    public function hasOneOrder()
    {
        return $this->hasOne(Order::class, 'id', 'order_id');
    }

}