<?php
/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 2021/8/20
 * Time: 14:23
 */

namespace app\common\route;


use app\common\middleware\AdminPermission;
use app\common\services\Check;
use Illuminate\Support\Str;

class ShopWeiQingBackendRoute extends AbstractShopRoute
{
	public $namespace = 'app\\backend';

	protected $middleware = [AdminPermission::class];

	public function __construct($path)
	{
		parent::__construct($path);
		if (!\YunShop::app()->uniacid) {
			return redirect('?c=account&a=display');
		}
		$yz_module = \app\common\models\Modules::getModuleInfo('yun_shop');
		if (config('app.env') != 'dev' && !\app\common\models\WqVersionLog::verifyExist($yz_module->version)) {
			$filesystem = app(\Illuminate\Filesystem\Filesystem::class);
			$update = new \app\common\services\AutoUpdate(null, null, 300);
			\Log::debug('----CLI----');
			$plugins_dir = $update->getDirsByPath('plugins', $filesystem);
			if (!empty($plugins_dir)) {
				\Artisan::call('update:version', ['version' => $plugins_dir]);
			}
			\app\common\models\WqVersionLog::createLog($yz_module->version);
		}
		//解析商城路由
		if (!request()->route) {
			$eid = request()->eid;
			if (!empty($eid)) {
				$entry = module_entry($eid);
				switch ($entry['do']) {
					case 'shop':
						redirect('?c=site&a=entry&do=shop&m=yun_shop&route=index.index')->send();
						exit;
					case 'member':
						redirect('?c=site&a=entry&do=shop&m=yun_shop&route=member.member.index')->send();
						exit;
					case 'order':
						redirect('?c=site&a=entry&do=shop&m=yun_shop&route=order.order-list.index')->send();
						exit;
					case 'finance':
						redirect('?c=site&a=entry&do=shop&m=yun_shop&route=finance.withdraw-set.see')->send();
						exit;
					case 'plugins':
						redirect('?c=site&a=entry&do=shop&m=yun_shop&route=plugins.get-plugin-data')->send();
						exit;
					case 'system':
						redirect('?c=site&a=entry&do=shop&m=yun_shop&route=setting.shop.index')->send();
						exit;
					default:
						redirect('?c=site&a=entry&do=shop&m=yun_shop&route=index.index')->send();
						exit;
				}
			}
		}
		//验证密钥
		Check::setKey();
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