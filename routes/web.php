<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
//微擎路由
Route::any('/', function () {
    //支付回调
    if (strpos(request()->getRequestUri(), '/payment/') !== false) {
        preg_match('#(.*)/payment/(\w+)/(\w+).php(.*?)#', request()->getRequestUri(), $match);
        if (isset($match[2])) {
            $namespace = 'app\\payment\\controllers\\' . ucfirst($match[2]) . 'Controller';
            $modules = [];
            $controllerName = ucfirst($match[2]);
            $action = $match[3];
            $currentRoutes = [];
            Yunshop::run($namespace, $modules, $controllerName, $action, $currentRoutes);
        }
        return;
    }

    //api
    if (strpos(request()->getRequestUri(), '/addons/') !== false &&
        strpos(request()->getRequestUri(), '/api.php') !== false
    ) {
        $shop = Setting::get('shop.shop');
        if (!is_null($shop) && isset($shop['close']) && 1 == $shop['close']) {
            throw new \app\common\exceptions\AppException('站点已关闭', -1);
        }
        return YunShop::parseRoute(request()->input('route'));
        return;
    }

    //后台
    if (strpos(request()->getRequestUri(), '/web/') !== false) {
        //如未设置当前公众号则加到选择公众号列表
        if (!YunShop::app()->uniacid) {
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
        if (YunShop::request()->route) {
            return YunShop::parseRoute(YunShop::request()->route);
        } else {
            $eid = YunShop::request()->eid;


            if (!empty($eid)) {
                $entry = module_entry($eid);

                switch ($entry['do']) {
                    case 'shop':
                        return redirect('?c=site&a=entry&do=shop&m=yun_shop&route=index.index');
                        break;
                    case 'member':
                        return redirect('?c=site&a=entry&do=shop&m=yun_shop&route=member.member.index');
                        break;
                    case 'order':
                        return redirect('?c=site&a=entry&do=shop&m=yun_shop&route=order.order-list.index');
                        break;
                    case 'finance':
                        return redirect('?c=site&a=entry&do=shop&m=yun_shop&route=finance.withdraw-set.see');
                        break;
                    case 'plugins':
                        return redirect('?c=site&a=entry&do=shop&m=yun_shop&route=plugins.get-plugin-data');
                        break;
                    case 'system':
                        return redirect('?c=site&a=entry&do=shop&m=yun_shop&route=setting.shop.index');
                        break;
                    default:
                        return redirect('?c=site&a=entry&do=shop&m=yun_shop&route=index.index');
                }
            }
        }
    }
    return;
});
