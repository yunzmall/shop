<?php
/**
 * Created by PhpStorm.
 * Name: 芸众商城系统
 * Author: 广州市芸众信息科技有限公司
 * Profile: 广州市芸众信息科技有限公司位于国际商贸中心的广州，专注于移动电子商务生态系统打造，拥有芸众社交电商系统、区块链数字资产管理系统、供应链管理系统、电子合同等产品/服务。官网 ：www.yunzmall.com  www.yunzshop.com
 * Date: 2021/8/17
 * Time: 16:38
 */
namespace app\common\middleware;

use \Closure;
class Install
{
	public function handle($request, Closure $next)
	{
		$path = 'addons/yun_shop';
		$file = $path .  '/api.php';
		if (!file_exists($file)) {
			!is_dir($path) && mkdir($path, 0777, true);
			$api_data = file_get_contents('api.php');
			file_put_contents($file, $api_data);
		}
		return $next($request);
	}
}