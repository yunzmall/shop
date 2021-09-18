<?php
/**
 * Created by PhpStorm.
 * Name: 芸众商城系统
 * Author: 广州市芸众信息科技有限公司
 * Profile: 广州市芸众信息科技有限公司位于国际商贸中心的广州，专注于移动电子商务生态系统打造，拥有芸众社交电商系统、区块链数字资产管理系统、供应链管理系统、电子合同等产品/服务。官网 ：www.yunzmall.com  www.yunzshop.com
 * Date: 2021/8/20
 * Time: 14:15
 */

namespace app\common\route;

use app\common\route\Contracts\ShopRoute as ShopRouteContracts;
use Illuminate\Support\Str;

class ShopRouteDirector
{
	public $route;
	public $routes;
	public function __construct(ShopRouteContracts $shou_route)
	{
		$this->route = $shou_route;
		$this->routes = request()->input('route');
	}

	public function setRoute()
	{
		list($class_name,$action) = $this->match();
		request()->setRoute($this->routes);
		$routes = app('routes')->getRoutesByMethod();
		$current_route = $routes[request()->method()][$this->route->path];
		$current_route->action['uses'] = $class_name.'@'.$action;
		$current_route->action['controller'] = $class_name.'@'.$action;
		$current_route->action['namespace'] = $class_name;
		return true;
	}

	private function match()
	{
		$routes = explode('.', $this->routes);
		$first = array_shift($routes);
		if ($first == 'plugin') {
			//插件路由
			$route = $this->route->pluginMatch($routes,$first);
		} else {
			//商城路由
			$route = $this->route->shopMatch($routes,$first);
		}
		list($classname,$action) = $route;
		if (empty($action)) {
			$action = $action ?: 'index';
			$this->routes .=  '.index';
		}
		$action = strpos($action, '-') === false ? $action : Str::camel($action);
		return [$classname,$action];

	}






}