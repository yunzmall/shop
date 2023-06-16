<?php
/**
 * 订单支付后事件
 * Created by PhpStorm.
 * Author:
 * Date: 2017/3/3
 * Time: 上午11:44
 */

namespace app\common\events\order;

class HiddenOrderCommentEvent extends \app\common\events\Event
{

    protected $order_goods;
    protected $is_hidden;

    public function __construct($order_goods)
    {
        $this->order_goods = $order_goods;
        $this->is_hidden = 0;
    }


    public function getOrderGoods()
    {
        return $this->order_goods;
    }

    public function setHidden()
    {
        $this->is_hidden = 1;
    }

    public function isShow()
    {
        return $this->is_hidden ? false : true;
    }


}