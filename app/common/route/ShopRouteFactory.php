<?php
/**
 * Created by PhpStorm.
 * Name: 芸众商城系统
 * Author: 广州市芸众信息科技有限公司
 * Profile: 广州市芸众信息科技有限公司位于国际商贸中心的广州，专注于移动电子商务生态系统打造，拥有芸众社交电商系统、区块链数字资产管理系统、供应链管理系统、电子合同等产品/服务。官网 ：www.yunzmall.com  www.yunzshop.com
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
				// 前台路由
				else {
					return new ShopFrontendRoute($path);
				}
			default:
				return false;
		}
	}
}