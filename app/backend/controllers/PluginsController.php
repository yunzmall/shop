<?php
/**
 * Created by PhpStorm.
 * Author:
 * Date: 10/03/2017
 * Time: 16:42
 */

namespace app\backend\controllers;


use app\backend\modules\menu\Menu;
use app\common\components\BaseController;
use app\common\exceptions\ShopException;
use app\common\helpers\Url;
use app\common\services\plugin\DeliveryDriverSet;
use app\common\services\plugin\PluginService;
use Datatables;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use app\common\services\PermissionService;
use app\common\models\user\User;
use Ixudra\Curl\Facades\Curl;


class PluginsController extends BaseController
{
    public $terminal = 'wechat|min|wap';
    private $request_domain = 'https://yun.yunzmall.com';

    public function showManage()
    {
        return view('public.admin.plugins');
    }

    public function config($name, Request $request)
    {
        $plugin = plugin($name);
        if ($plugin && $plugin->isEnabled() && $plugin->hasConfigView()) {
            return $plugin->getConfigView();
        } else {
            abort(404, trans('admin.plugins.operations.no-config-notice'));
        }
    }

    public function manage()
    {
        $name = request()->name;
        $action = request()->action;
        $plugins = app('app\common\services\PluginManager');
        $plugin = plugin($name);

        if (app()->environment() == 'production') {
            $this->proAuth($name, $action);
        }

        if ($plugin) {
            // pass the plugin title through the translator
            $plugin->title = trans($plugin->title);

            switch (request()->action) {
                case 'enable':
                    $plugins->enable($name);
                    \Artisan::call('config:cache');
                    \Cache::flush();
                    return $this->successJson('启用成功');
                case 'disable':
                    $plugins->disable($name);
                    \Artisan::call('config:cache');
                    \Cache::flush();
                    return $this->successJson('禁用成功');
                case 'delete':
                    if (!PermissionService::isFounder()) {
                        return $this->errorJson('您暂没有权限卸载插件');
                    }
                    $plugins->uninstall($name);
                    \Artisan::call('config:cache');
                    \Cache::flush();
                    return $this->successJson('卸载成功');
                default:
                    # code...
                    break;
            }
        }
    }

    public function batchMange()
    {
        $plugins = app('app\common\services\PluginManager');
        $names = explode(',', request()->names);
        $action = request()->action;

        foreach ($names as $name) {
            if (app()->environment() == 'production') {
                $this->proAuth($name, $action);
            }

            $plugin = plugin($name);
            if ($plugin) {
                $plugin->title = trans($plugin->title);
                switch (request()->action) {
                    case 'enable':
                        $plugins->enable($name);
                        break;
                    case 'disable':
                        $plugins->disable($name);
                        break;
                    case 'delete':
                        $plugins->uninstall($name);
                        break;
                    default:
                        die(json_encode(array(
                            "result" => 0,
                            "error" => "操作错误"
                        )));
                        break;
                }
            }
        }
        switch (request()->action) {
            case 'enable':
                return $this->successJson('启用成功');
            case 'disable':
                return $this->successJson('禁用成功');
            case 'delete':
                \Artisan::call('config:cache');
                \Cache::flush();
                return $this->successJson('卸载成功');
        }
    }

    /**
     * 关键字搜索
     * @param PluginService $service
     * @return \Illuminate\Http\JsonResponse
     */
    public function searchPluginList(PluginService $service)
    {
        if (!app('plugins')->isEnabled('plugins-market')) {
            return $this->errorJson('未安装插件市场插件！');
        }
        $keyword = request("keyword");
        $plugin_list = $service->ajaxPluginList($keyword);
        return $this->successJson('ok', $plugin_list);
    }

    /**
     * name 插件标识
     * version 版本
     * @param PluginService $service
     * @return \Illuminate\Http\JsonResponse|void
     * @throws ShopException
     * @throws \app\common\exceptions\AppException
     */
    public function installPlugin(PluginService $service)
    {
        $this->validate(['name' => 'required', 'version' => 'required']);
        $pluginData = [
            'name' => request('name'),
            'version' => request('version'),
        ];
        $result = $service->install($pluginData);
        if ($result) {
            return $this->successJson('安装成功！');
        }
    }

    public function getPluginData()
    {
        $url = $this->request_domain . '/plugin/plugin_install_auth';
        $domain = request()->getHttpHost();
        $installed = app('plugins')->getPlugins();
        $data = [];
        $plugins = [];
        $i = 0;

        $installed->each(function ($item, $key) use (&$data, &$i, &$plugins) {
            $plugins[] = $key;
            $data[$i] = $item->toArray();
            $data[$i]['status'] = $item->isEnabled() ? true : false;
            $data[$i]['new_version'] = 0;
            $data[$i]['permit_status'] = '未授权';
            $i++;
        });

        $content = Curl::to($url)
            ->withData(['domain' => $domain])
            ->asJsonResponse(true)
            ->get();

        //未授权插件数量
        $unPermitPlugin = 0;
        //TODO  数组合并
        foreach ($content['data'] as $k => $v) {
            foreach ($data as $key => &$value) {
                if ($k == $value['name']) {
                    $value['new_version'] = $v['version'];
                    $value['permit_status'] = $v['status'];
                }
            }
        }
        unset($value);

        foreach ($data as $key => $value) {
            if ('未授权' === $value['permit_status']) {
                $unPermitPlugin++;
            }
        }

        if (request()->ajax()) {
            return $this->searchPlugin($data, request()->search);
        }
        return view('admin.plugins', [
            'data' => json_encode($data),
            'unPermitPlugin' => $unPermitPlugin,
            'countPlugin' => count($data),
        ]);
    }

    public function getPluginList()
    {
        if (request()->ajax()) {
            $class = $this->getType();
            $data = [];
            $plugins = Menu::current()->getPluginMenus();//全部插件

            foreach ($plugins as $key => &$plugin) {
                $name = explode('.', $plugin['url'])[1];
                if (!$plugin['type'] || empty($plugin) || !can($key)) {
                    unset($plugins[$key]);
                    continue;
                }

                $terminal = app('plugins')->getPlugin($name)->terminal;
                $uni_name = app('plugins')->getPlugin($name)->name;
                $plugin['color'] = $plugin['color'] ?: $this->getType()[$plugin['type']]['color'];
                $plugin['terminal'] = $terminal ? explode('|', $terminal) : [];
                $plugin['top_show'] = app('plugins')->isTopShow($key);
                $plugin['description'] = app('plugins')->getPlugin($name)->description ?: $plugin['name'];
                $plugin['icon_url'] = file_exists(base_path('static/yunshop/plugins/list-icon/icon/' . $uni_name . '.png')) ? static_url("yunshop/plugins/list-icon/icon/{$uni_name}.png") : static_url("yunshop/plugins/list-icon/icon/default.png");
                $plugin['url'] = $plugin['url_params'] ? yzWebFullUrl($this->canAccess($key)) . "&" . $plugin['url_params'] : yzWebFullUrl($this->canAccess($key));
                $data[$plugin['type']][$key] = $plugin;
            }
            $result = [
                'plugins' => $plugins,
                'data' => $data,
                'class' => $class,
                'has_founder' => PermissionService::isFounder(),
            ];
            return $this->successJson('ok', $result);
        }
        return view('admin.pluginslist');
    }

    public static function canAccess($item)
    {
        $current_menu = Menu::current()->getPluginMenus()[$item];
        $url = $current_menu['url'];

        if (PermissionService::isFounder()) {
            return $url;
        }
        if (PermissionService::isOwner()) {
            return $url;
        }
        if (PermissionService::isManager()) {
            return $url;
        }
        if (PermissionService::checkNoPermission($item) === true) {
            return $url;
        }

        if (!isset($current_menu['child'])) {
            return $url;
        }

        $userPermission = User::userPermissionCache();
        //检测当前 key 下路由是否有权限访问
        foreach ($current_menu['child'] as $key => $value) {

            if ($value['url'] == $current_menu['url'] && in_array($key, $userPermission) && $value['menu'] == 1) {
                return $url;
                break;
            }
            continue;
        }
        //上面条件都不满足时，找第一个有权限访问的路由
        foreach ($current_menu['child'] as $key => $value) {

            if (in_array($key, $userPermission) && $value['menu'] == 1) {
                return $value['url'];
                break;
            }
            continue;
        }
        return 'index.index';
    }

    public function setTopShow()
    {
        $data = request()->input();

//        $data['action'] ?: app('plugins')->enTopShow($data['name'], 1);
        if ($data['action']) {
            app('plugins')->enTopShow($data['name'], 0);
            return $this->successJson('取消顶部栏成功');
        } else {

            $menu = config(config('app.menu_key', 'menu'));
            $counts = 0;
            //常用功能
            foreach ($menu as $key => $itme) {
                if (isset($itme['menu']) && $itme['menu'] == 1 && can($key) && ($itme['top_show'] == 1 || app('plugins')->isTopShow($key))) {
                    ++$counts;
                    if ($counts > 7) {
                        return $this->message('顶部栏最大数量为八个');
                    }
                }
            }

            app('plugins')->enTopShow($data['name'], 1);
            return $this->successJson('添加顶部栏成功');
        }
    }

    public function proAuth($name, $action)
    {
        if ($action == 'enable') {
            $key = \Setting::get('shop.key')['key'];
            $secret = \Setting::get('shop.key')['secret'];

            $url = config('auto-update.proAuthUrl') . "/chkname/{$name}";

            $res = \Curl::to($url)
                ->withHeader(
                    "Authorization: Basic " . base64_encode("{$key}:{$secret}")
                )
                ->asJsonResponse(true)
                ->get();
            // dd($res);

            // \Log::debug('-------update res-----', $res);
            if (0 == $res['status']) {
                throw new ShopException('应用未授权');
            }
        }
    }

    public function getType()
    {
        return [
            'dividend' => [
                'name' => '入口类',
                'color' => '#F15353',
            ],
            'industry' => [
                'name' => '行业类',
                'color' => '#eb6f50',
            ],
            'supply' => [
                'name' => '供应链类',
                'color' => '#ffffff',
            ],
            'marketing' => [
                'name' => '营销类',
                'color' => '#f0b652',
            ],
            'business_management' => [
                'name' => '企业管理类',
                'color' => '#f05295',
            ],
            'tool' => [
                'name' => '工具类',
                'color' => '#f59753',
            ],
            'recharge' => [
                'name' => '生活充值',
                'color' => '#50d9a7',
            ],
            'api' => [
                'name' => '接口类',
                'color' => '#53d5f0',
            ],
            'store' => [
                'name' => '门店应用类',
                'color' => '#98aafa',
            ],
            'blockchain' => [
                'name' => '区块链类',
                'color' => '#469de2',
            ],
            'douyin' => [
                'name' => '抖音类应用',
                'color' => '#ffffff',
            ]
        ];
    }

    public function searchPlugin($data, $search)
    {
        foreach ($data as $key => $value) {
            if ($search['title'] && !strexists($value['title'], $search['title'])) {
                unset($data[$key]);
            }
            if ($search['permit_status'] && !strexists($value['permit_status'], $search['permit_status'])) {
                unset($data[$key]);
            }
            if ($search['update_status'] == '可升级' && $value['version'] == $value['new_version']) {
                unset($data[$key]);
            }
            if ($search['update_status'] == '不可升级' && $value['version'] < $value['new_version']) {
                unset($data[$key]);
            }
            if ($search['status'] === 'enable' && $value['status'] == false) {
                unset($data[$key]);
            }
            if ($search['status'] === 'disable' && $value['status'] == true) {
                unset($data[$key]);
            }
        };
        return $this->successJson('请求成功', array_values($data));
    }

    /**
     * 中转方法，安装应用菜单判断应用市场是否开启
     */
    public function jump()
    {
        if (app('plugins')->isEnabled('plugins-market')) {
            return view('Yunshop\PluginsMarket::new_market')->render();
        } else {
            return $this->message('请先开启插件市场插件', yzWebFullUrl('plugins.get-plugin-data'));
        }
    }
}
