<?php
/**
 * Created by PhpStorm.
 * Name: 芸众商城系统
 * Author: 广州市芸众信息科技有限公司
 * Profile: 广州市芸众信息科技有限公司位于国际商贸中心的广州，专注于移动电子商务生态系统打造，拥有芸众社交电商系统、区块链数字资产管理系统、供应链管理系统、电子合同等产品/服务。官网 ：www.yunzmall.com  www.yunzshop.com
 * Date: 2021/8/20
 * Time: 14:24
 */

namespace app\common\route;


use app\common\exceptions\NotFoundException;

class ShopPaymentRoute extends AbstractShopRoute
{
	private $namespace = 'app\\payment';

	public function shopMatch($routes,$first)
	{
		preg_match('#(.*)/payment/(\w+)/(\w+).php(.*?)#', request()->getRequestUri(), $match);
		if (isset($match[2])) {
			$class_name      = $this->namespace.'\\controllers\\' . ucfirst($match[2]) . 'Controller';
			$action         = $match[3];
			return [$class_name,$action];
		} else {
			throw new NotFoundException();
		}
	}
}