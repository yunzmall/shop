<?php
/**
 * 订单支付后事件
 * Created by PhpStorm.
 * Author:
 * Date: 2017/3/3
 * Time: 上午11:44
 */

namespace app\common\events\order;

use app\common\models\Order;

class OrderMiniNoticeListEvent extends \app\common\events\Event
{
    protected $order;
    protected $small_type;
    protected $list;

    public function __construct($list, $small_type, $order_id)
    {
        $this->list = $list;
        $this->order = $order_id ? Order::uniacid()->find($order_id) : null;
        $this->small_type = $small_type;
    }


    public function getOrder()
    {
        return $this->order;
    }

    public function getSmallType()
    {
        return $this->small_type;
    }

    public function getList()
    {
        return $this->list;
    }

    public function setList($list)
    {
        $this->list = $list;
    }


}