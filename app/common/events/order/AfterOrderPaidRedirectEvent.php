<?php
/**
 * Created by PhpStorm.
 * Name: 芸众商城系统
 * Author: 广州市芸众信息科技有限公司
 * Profile: 广州市芸众信息科技有限公司位于国际商贸中心的广州，专注于移动电子商务生态系统打造，拥有芸众社交电商系统、区块链数字资产管理系统、供应链管理系统、电子合同等产品/服务。官网 ：www.yunzmall.com  www.yunzshop.com
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