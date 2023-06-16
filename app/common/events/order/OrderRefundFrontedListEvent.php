<?php
/**
 * 订单支付后事件
 * Created by PhpStorm.
 * Author:
 * Date: 2017/3/3
 * Time: 上午11:44
 */

namespace app\common\events\order;

class OrderRefundFrontedListEvent extends \app\common\events\Event
{

    protected $query;

    public function __construct($query)
    {
        $this->query = $query;
    }


    public function getQuery()
    {
        return $this->query;
    }

    public function setQuery($query)
    {
        $this->query = $query;
    }


}