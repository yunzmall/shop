<?php
/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 2021/7/9
 * Time: 11:33
 */

namespace app\common\events\order;


use app\common\events\Event;

class AfterOrderPaidRedirectEvent extends Event
{
	private $orders;
	private $order_pay_id;

	public function __construct($orders,$order_pay_id)
	{
		$this->orders = $orders;
		$this->order_pay_id = $order_pay_id;
	}

	public function getOrders()
	{
		return $this->orders;
	}

	public function getOrderPayId()
	{
		return $this->order_pay_id;
	}

}