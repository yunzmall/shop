<?php
/**
 * Created by PhpStorm.
 * 
 *
 *
 * Date: 2022/5/20
 * Time: 16:04
 */

namespace app\common\models\order;


use app\common\models\BaseModel;

class ManualRefundLog extends BaseModel
{

    protected $table = 'yz_order_manual_refund_log';

    protected $guarded = ['id'];

    protected $hidden = [
        'updated_at'
    ];


    public static function saveLog($order_id, $operator = 0)
    {

        $operator_id = $operator ? \YunShop::app()->getMemberId() : \YunShop::app()->uid;

        $createData = [
            'order_id' => $order_id,
            'operator_id' => $operator_id?:0,
            'operator' => $operator,
        ];

        return self::create($createData);
    }
}