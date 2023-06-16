<?php
/**
 * Created by PhpStorm.
 * User: CJVV
 * Date: 2021/3/17
 * Time: 16:36
 */

namespace app\common\events\order;

use app\common\events\Event;

/**
 * 订单退款价格修改前事件
 * Class AfterOrderRefundRejectEvent
 * @package app\common\events\order
 */
class OrderRefundApplyDataEvent extends Event
{
    protected $return_data;

    public function __construct($return_data)
    {
        $this->return_data = $return_data;
    }

    public function getData()
    {
        return $this->return_data;
    }

    public function setData($return_data)
    {
        $this->return_data = $return_data;
    }

}