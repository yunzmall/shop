<?php
/**
 * Created by PhpStorm.
 * Author:
 * Date: 2017/3/20
 * Time: 上午10:44
 */

namespace app\common\models;

use app\backend\models\BackendModel;

/**
 * Class PayRefundOrder
 * @package app\common\models
 * @property int status
 */
class PayRefundOrder extends BackendModel
{
    public $table = 'yz_pay_refund_order';

    /**
     * 可以被批量赋值的属性.
     *
     * @var array
     */
    protected $fillable = ['uniacid', 'member_id', 'int_order_no', 'out_order_no', 'status', 'type', 'price'];

    public static function getOrderInfo($orderno)
    {
        return self::uniacid()
            ->where('out_order_no', $orderno)
            ->orderBy('id', 'desc')
            ->first();
    }
}