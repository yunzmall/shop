<?php
/**
 * 订单取消后事件
 * Created by PhpStorm.
 * Author:
 * Date: 2017/3/3
 * Time: 上午11:44
 */

namespace app\common\events\order;

use app\common\events\Event;

class BeforeOrderMergePayEvent extends Event
{
    protected $order_pay;
    protected $order;
    protected $pay_type_id;
    public $plugin_msg;
    public $error_msg;
    protected $is_break;

    public function __construct($order_pay, $order, $pay_type_id)
    {
        $this->is_break = 0;
        $this->order_pay = $order_pay;
        $this->order = $order;
        $this->$pay_type_id = $pay_type_id;
    }


    /**
     * @param $plugin_msg
     * @param $error_msg
     * @return void
     * 打断支付 plugin_msg:插件名 error_msg：错误提示
     */
    public function setBreak($plugin_msg, $error_msg)
    {
        $this->is_break = 1;
        $this->plugin_msg = $plugin_msg;
        $this->error_msg = $error_msg;
        \Log::debug("支付前监听中断:order_pay_id:{$this->order_pay->id}|order_id:{$this->order->id}|pay_type_id:{$this->pay_type_id}");
        \Log::debug("支付前监听中断信息", [
            'plugin_msg' => $plugin_msg,
            'error_msg' => $error_msg,
        ]);
    }

    public function isBreak()
    {
        return $this->is_break;
    }

    public function getOrderPay()
    {
        return $this->order_pay;
    }

    public function getOrder()
    {
        return $this->order;
    }


    public function getPayTypeId()
    {
        return $this->pay_type_id;
    }

}