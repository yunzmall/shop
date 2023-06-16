<?php
/**
 * Created by PhpStorm.
 *
 *
 *
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