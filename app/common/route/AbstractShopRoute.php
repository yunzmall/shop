<?php
/**
 * Created by PhpStorm.
 * Name: 芸众商城系统
 * Author: 广州市芸众信息科技有限公司
 * Profile: 广州市芸众信息科技有限公司位于国际商贸中心的广州，专注于移动电子商务生态系统打造，拥有芸众社交电商系统、区块链数字资产管理系统、供应链管理系统、电子合同等产品/服务。官网 ：www.yunzmall.com  www.yunzshop.com
 * Date: 2021/8/20
 * Time: 16:03
 */

namespace app\common\route;

use app\common\exceptions\NotFoundException;
use app\common\route\Contracts\ShopRoute as ShopRouteContracts;
use Illuminate\Support\Str;

abstract class AbstractShopRoute implements ShopRouteContracts
{
	public $path = '';
	public function __construct($path)
	{
		$this->path = $path;
	}

	public function pluginMatch($routes)
	{
		$class_name = '';
		$action = '';
		$plugin = array_shift($routes);
		$plugin_app = app('plugins')->getEnablePlugin($plugin);
		if (empty($plugin_app)) {
			throw new NotFoundException();
		}
		$namespace = $plugin_app->namespace;
		foreach ($routes as $route) {
			if ($class_name) {
				$action = $route;
				break;
			}
			$controller = ucfirst(Str::camel($route)).'Controller';
			if (class_exists($namespace.'\\'.$controller)) {
				$class_name = $namespace.'\\'.$controller;
			} else {
				$namespace .= '\\'.$route;
			}
		}
		return [$class_name,$action];
	}


	abstract public function shopMatch($routes,$first);
}