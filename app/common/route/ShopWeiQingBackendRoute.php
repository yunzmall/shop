<?php
/**
 * Created by PhpStorm.
 * Name: 芸众商城系统
 * Author: 广州市芸众信息科技有限公司
 * Profile: 广州市芸众信息科技有限公司位于国际商贸中心的广州，专注于移动电子商务生态系统打造，拥有芸众社交电商系统、区块链数字资产管理系统、供应链管理系统、电子合同等产品/服务。官网 ：www.yunzmall.com  www.yunzshop.com
 * Date: 2021/8/20
 * Time: 14:23
 */

namespace app\common\route;


use Illuminate\Support\Str;

class ShopWeiQingBackendRoute extends AbstractShopRoute
{
	public $namespace = 'app\\backend';

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
						return redirect('?c=site&a=entry&do=shop&m=yun_shop&route=index.index')->send();
						break;
					case 'member':
						return redirect('?c=site&a=entry&do=shop&m=yun_shop&route=member.member.index')->send();
						break;
					case 'order':
						return redirect('?c=site&a=entry&do=shop&m=yun_shop&route=order.order-list.index')->send();
						break;
					case 'finance':
						return redirect('?c=site&a=entry&do=shop&m=yun_shop&route=finance.withdraw-set.see')->send();
						break;
					case 'plugins':
						return redirect('?c=site&a=entry&do=shop&m=yun_shop&route=plugins.get-plugin-data')->send();
						break;
					case 'system':
						return redirect('?c=site&a=entry&do=shop&m=yun_shop&route=setting.shop.index')->send();
						break;
					default:
						return redirect('?c=site&a=entry&do=shop&m=yun_shop&route=index.index')->send();
				}
			}
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