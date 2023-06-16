<?php
/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 2021/8/19
 * Time: 16:47
 */

namespace app\common\middleware;


use app\common\route\ShopRouteFactory;
use app\common\route\Contracts\ShopRoute as ShopRouteContracts;
class ShopRoute
{
	public function handle($request,\Closure $next)
	{
		$shop_route = ShopRouteFactory::create($request->path());
		// 添加路由
		if ($shop_route instanceof ShopRouteContracts) {
			(new \app\common\route\ShopRouteDirector($shop_route))->setRoute();
		}
		return $next($request);
	}

}