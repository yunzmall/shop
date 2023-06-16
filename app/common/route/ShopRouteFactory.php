<?php
/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 2021/8/25
 * Time: 20:23
 */

namespace app\common\route;


use app\common\route\Contracts\ShopRoute as ShopRouteContracts;

class ShopRouteFactory
{
	public static function create($path)
	{
		switch ($path) {
			case 'admin/shop':
				// 独立后台路由
				return new ShopBackendRoute($path);

			case '/':
				// 支付路由
				if (strpos(request()->getRequestUri(), '/payment/') !== false) {
					return new ShopPaymentRoute($path);
				}
				// 微擎后台路由
				elseif (strpos(request()->getRequestUri(), '/web/index.php') !== false) {
					return new ShopWeiQingBackendRoute($path);
				}
//                //对外开放接口
//                elseif (strpos(request()->getRequestUri(), '/outside.php') !== false) {
//                   return  new ShopOutsideRoute($path);
//                }
				// 微擎消息回复
				elseif (strpos(request()->getRequestUri(), '/addons/') === false &&
					strpos(request()->getRequestUri(), '/api.php') !== false) {
					return false;
				}
				// 前台路由
				else {
					return new ShopFrontendRoute($path);
				}
			default:
				return false;
		}
	}
}