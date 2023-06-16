<?php
/**
 * Created by PhpStorm.
 * 
 * 
 *
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

	protected $middleware = [];

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

	public function getMiddleware()
	{
		return $this->middleware;
	}
}