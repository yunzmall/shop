<?php
/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 2021/10/18
 * Time: 17:33
 */
namespace app\common\middleware;

use app\common\services\PermissionService;
use Closure;
Class AdminPermission
{
	public function handle($request, Closure $next)
	{
		$controller = $request->route()->getController();
		if (!$controller->getIsPublic()) {
			PermissionService::validate();
		}
		return $next($request);
	}
}