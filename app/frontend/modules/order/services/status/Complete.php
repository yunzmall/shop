<?php
/**
 * Created by PhpStorm.
 * Author:  
 * Date: 2017/3/2
 * Time: 下午4:55
 */

namespace app\frontend\modules\order\services\status;


use app\common\models\DispatchType;
use app\common\models\Order;

class Complete extends Status
{
    private $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function getStatusName()
    {
        return '交易完成';
    }

}