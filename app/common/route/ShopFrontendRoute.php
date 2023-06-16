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


use app\common\exceptions\AppException;
use app\common\exceptions\UniAccountNotFoundException;
use app\common\middleware\BasicInformation;
use Illuminate\Support\Str;

class ShopFrontendRoute extends AbstractShopRoute
{
	public $namespace = 'app\\frontend';

	protected $middleware = [BasicInformation::class];

	public function __construct($path)
	{
		parent::__construct($path);
		$shop = \Setting::get('shop.shop');
		if ($shop['close'] == 1) {
			throw new AppException('站点已关闭', -1);
		}
	}

	public function shopMatch($routes, $first)
	{
		$namespace = $this->namespace;
		$class_name = '';
		$action = '';
		if (class_exists($namespace.'\\controllers\\'.ucfirst(Str::camel($first)).'Controller')) {
			$class_name = $namespace.'\\controllers\\'.ucfirst(Str::camel($first)).'Controller';
			$action = array_shift($routes);
		} else {
			$namespace .= '\\modules\\'.$first;
			$namespace_module = $namespace;
			foreach ($routes as $route) {
				if ($class_name) {
					$action = $route;
					break;
				}
				$controller = ucfirst(Str::camel($route)).'Controller';
				if (class_exists($namespace.'\\controllers\\'.$controller)) {
					$class_name = $namespace.'\\controllers\\'.$controller;
				} elseif (class_exists($namespace_module.'\\controllers\\'.$controller)) {
					$class_name = $namespace_module.'\\controllers\\'.$controller;
				} else {
					$namespace .= '\\'.$route;
					$namespace_module .= '\\modules\\'.$route;
				}

			}
		}
		return [$class_name,$action];
	}
}