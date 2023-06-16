<?php
/**
 * Created by PhpStorm.
 *
 *
 *
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
		//中间件
		$middleware = $this->route->getMiddleware();
		$current_route->action['middleware'] = array_merge($current_route->action['middleware'],$middleware);
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