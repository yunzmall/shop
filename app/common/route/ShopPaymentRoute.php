<?php
/**
 * Created by PhpStorm.
 *
 *
 *
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