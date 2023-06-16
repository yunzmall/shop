<?php

namespace app\common\services\plugin;

use app\backend\controllers\PluginsController;
use app\backend\modules\menu\Menu;
use app\common\exceptions\ShopException;
use app\common\services\AutoUpdate;
use app\common\services\PermissionService;
use app\common\services\Storage;
use app\frontend\modules\update\models\authModel;
use Illuminate\Support\Facades\Cache;
use Ixudra\Curl\Facades\Curl;
use ZipArchive;
use Option;
use Utils;

class PluginService
{
    const REQUEST_DOMAIN = 'https://yun.yunzmall.com';


    /**
     * @param $keyword
     * 插件处理
     * @return array|array[]
     */
    public function ajaxPluginList($keyword = '')
    {
        $installed_plugins_version_list = $this->loadInstalledPluginList();
        $raw_list = $this->getPluginList();
        array_multisort($raw_list['list'], SORT_DESC, $raw_list['list']);
        $auth = $this->authPlugin();

        if (empty($raw_list)) {
            return [];
        }
        //未安装
        $not_installed_list = array();
        //已安装
        $installed_list = array();

        foreach ($raw_list['list'] as $plugin) {

            if (strpos($plugin['title'], $keyword) === false || !$keyword) {
                continue;
            }
            $each_plugin = $this->getSinglePluginInfo($plugin);
            if (!$each_plugin) {
                continue;
            } else {
                $each_plugin['version_status'] = 'un_install';
                $each_plugin['empower'] = 'auth';
                $each_plugin['enabled'] = app('plugins')->isEnabled($each_plugin['name']);
                $lastesVersion = $each_plugin['versionCom'][0];
                $each_plugin['latestVersion'] = '';
                foreach ($each_plugin['versionList'] as $each_version) {
                    if ($each_plugin['version'] == $each_version['version']) {
                        $each_plugin['versionDescription'] = $each_version['description'];
                        break;
                    }
                }
                $versionList = $each_plugin['versionList'];
                $versionCom = $each_plugin['versionCom'];
                unset($each_plugin['versionList']);
                unset($each_plugin['brief']);
                unset($each_plugin['versionCom']);
                //未授权
                if ($auth[$each_plugin['id']] === 0) {
                    continue;
                } elseif (
                    (!empty($plugin['isPreview']) && $plugin['isPreview']) ||
                    (stripos(end($versionCom), 'rc') > 0) ||
                    (stripos(end($versionCom), 'beta') > 0) ||
                    (stripos(end($versionCom), 'alpha') > 0)) {
                    continue;
                } elseif (!empty($installed_plugins_version_list[$each_plugin['name']]) && $each_plugin['enabled']) {
                    $each_plugin['jump_url'] = $this->getJumpUrl($each_plugin['name']);
                    if (!$each_plugin['jump_url']) {
                        continue;
                    }
                    $each_plugin['version_status'] = 'installed';
                    $each_plugin['empower'] = "auth";
                    $installed_list[] = $each_plugin;
                } elseif (count($not_installed_list) < 3 && empty($installed_plugins_version_list[$each_plugin['name']]) && PermissionService::isFounder()) {
                    $not_installed_list[] = $each_plugin;
                }
            }
        }
        return [
            'installed_list' => $installed_list,
            'not_installed_list' => $not_installed_list,
        ];
    }

    /**
     * 获取url
     * @return mixed|string
     * @throws ShopException
     */
    public function getJumpUrl($name)
    {
        //去掉斜杠然后转小写
        $name = strtolower(str_replace(['-', '_'], '', $name));

        $plugin_list = collect(Menu::current()->getPluginMenus());
        if ($plugin_list->isNotEmpty()) {
            $url = '';
            foreach ($plugin_list as $key => $plugin) {
                $key_name = strtolower(str_replace(['-', '_'], '', $key));
                if ($key_name == $name) {
                    $url = $plugin['url_params'] ?
                        yzWebFullUrl(PluginsController::canAccess($key)) . "&" . $plugin['url_params']
                        :
                        yzWebFullUrl(PluginsController::canAccess($key));
                }
            }
            return $url;
        }
        return 'index.index';
    }

    /**
     * 获取所有本地插件
     * @return array
     */
    protected function loadInstalledPluginList()
    {
        $version_list = array();
        $resource = opendir(base_path('plugins'));
        while ($file_name = @readdir($resource)) {
            if ($file_name == '.' || $file_name == '..')
                continue;
            $plugin_path = base_path('plugins') . '/' . $file_name;
            if (is_dir($plugin_path) && file_exists($plugin_path . '/package.json')) {
                $plugin_info = json_decode(file_get_contents($plugin_path . '/package.json'), true);
                $version_list[$plugin_info['name']] = $plugin_info['version'];
            }
        }
        closedir($resource);
        return $version_list;
    }


    /**
     * 获取所有源插件
     * @return mixed|null
     */
    protected function getPluginList()
    {
        //增加缓存
        if (Cache::has('plugins-market-list')) {
            return Cache::get('plugins-market-list');
        }
        if (empty(option('market_source'))) {
            //A source maintained by me
            option(['market_source' => config('app.PLUGIN_MARKET_SOURCE') ?: 'https://yun.yunzmall.com/plugin.json']);

        }

        //TODO 加上不同的域名
        $domain = request()->getHttpHost();

        $market_source_path = option('market_source') . '/domain/' . $domain;

        try {
            $json_content = Curl::to($market_source_path)
                ->get();
        } catch (\Exception $e) {
            return null;
        }
        $json_content = json_decode($json_content, true);
        Cache::put('plugins-market-list', $json_content, 60 * 5);
        return $json_content;
    }

    //请求接口区分插件搜未授权
    protected function authPlugin()
    {
        $domain = request()->getHttpHost();
        $url = self::REQUEST_DOMAIN . '/plugin/plugin_authorize/' . $domain;
        $content = Curl::to($url)
            ->asJsonResponse(true)
            ->get();
        $auth = [];
        if ($content['result'] == 1) {
            foreach ($content['data'] as $k => $v) {
                $auth[$v['plugin_id']] = $v['status'];
            }

            return $auth;
        }
        return [];
    }


    /**
     * @param $plugin
     * @return array|bool
     */
    protected function getSinglePluginInfo($plugin)
    {
//        if ($plugin['switch'] == 0) {
//            return false;
//        }
        if (empty($plugin['name']) || empty($plugin['title']) || empty($plugin['author']) || empty($plugin['url']) || empty($plugin['version'])) {
            return false;
        } else {
            $versionList = [];
            if (!empty($plugin['old'])) {
                // $versions = array_keys($plugin['old']);
                $versionList = $plugin['old'];
            }
            $version = $plugin['version'];

            return array(
                'id' => $plugin['id'],
                'name' => $plugin['name'],

                'title' => $plugin['title'],

                'description' => empty($plugin['description']) ? trans('Yunshop\PluginsMarket::market.no-description') : $plugin['description'],

                'author' => $plugin['author'],

                'version' => $version,

                'versionList' => $versionList,

                'versionCom' => $plugin['versionCom'],

                'size' => empty($plugin['size']) ? trans('Yunshop\PluginsMarket::market.unknown') : $plugin['size'],

                'brief' => empty($plugin['brief']) ? '' : $plugin['brief'],
                'imageUrl' => !empty(trim($plugin['imageUrl'])) ? yz_tomedia($plugin['imageUrl']) : yz_tomedia('/plugins/plugins-market/assets/images/default.png'),
                'category_id' => $plugin['category_id'],
                'category_name' => $plugin['category_name'],
                'content' => is_null($plugin['content']) ? '暂无详情，请联系客服咨询' : $plugin['content'],
                'switch' => $plugin['switch']
            );
        }
    }


    /**
     * 安装或者升级插件
     * @return false|mixed|string
     */
    public function install($pluginData)
    {
        //检测数据库有没有对应的key 和secret
        $domain = request()->getHttpHost();
        $res = $this->existsPlugin($domain, $pluginData['name']);
        \Log::info('------插件安装升级------', $res);
        if ($res) {
            $downloadRes = $this->downloadPlugin($pluginData);
            if ($downloadRes['code'] !== 0) {
                $this->clearOption($domain, $pluginData['name']);
                throw new ShopException('安装失败,或使用https请求安装');
            }
        } else {
            throw new ShopException('安装失败,插件不存在');
        }

        return true;

    }

    /**
     * 密钥错误，清除数据库密钥
     * @param $domain
     * @param $key
     * @return void
     */
    protected function clearOption($domain, $key)
    {
        $keys = json_decode(option('key'), true);
        if ($keys[$domain][$key])
            unset($keys[$domain][$key]);
        Option::set('key', json_encode($keys));
    }


    /**
     * 检测站点插件是否存在
     * @param $domain
     * @param $plugin
     * @return mixed
     */
    protected function existsPlugin($domain, $plugin)
    {
        $url = self::REQUEST_DOMAIN . '/plugin/exists_plugin/' . $domain . ':' . $plugin;

        $update = new AutoUpdate();

        $res = $update->isPluginExists($url);

        return $res['isExists'];
    }

    /**
     * 下载文件
     * @param $plugin
     * @return array|false|mixed|string
     */
    protected function downloadPlugin($plugin)
    {
        $plugins = app('app\common\services\PluginManager');
        $name = $plugin['name'];
        $code = '';

        if (!$name)
            return ['code' => -1, 'msg' => '名字不存在！'];

        //Prepare download
        $tmp_dir = storage_path('plugin-download-temp');
        $tmp_path = $tmp_dir . '/tmp_' . $name . '.zip';
        if (!is_dir($tmp_dir)) {
            if (false === mkdir($tmp_dir)) {
                return ['code' => 1];
            }
        }
        //Gather URL
        $marketSourcePath = option('market_source') . '/' . request()->getHost() . '/' . $name;

        $ctx = stream_context_create([
            "ssl" => [
                "verify_peer" => false,
                "verify_peer_name" => false,
            ]
        ]);

        $json_content = '';
        try {
            $json_content = file_get_contents($marketSourcePath, false, $ctx);
            $code = authModel::orderBy('id', 'desc')->value('code');
        } catch (\Exception $e) {

            return json_encode(['code' => 2, 'msg' => '访问路径不对']);
        }
        $url = json_decode($json_content, true)['url'];


        $url = $url . "/" . $code . '/' . request()->getHost() . ":" . $name;
        //Connection check
        if (!$fp = @fopen($url, 'rb', false, $ctx)) {
            return ['code' => 5, 'msg' => 'url 打不开！'];
        }
        // TODO check
        //Start to download
        try {
            Utils::download($url, $tmp_path);

        } catch (\Exception $e) {
            Storage::removeDir($tmp_dir);
            return ['code' => 3, 'msg' => '下载失败！'];
        }


        $zip = new ZipArchive();
        $res = $zip->open($tmp_path);
        if ($res === true) {
            try {
                $zip->extractTo(base_path('plugins'));
            } catch (\Exception $e) {
                $zip->close();
                Storage::removeDir($tmp_dir);
                return ['code' => 4, 'msg' => '解压失败'];
            }
        } else {
            $zip->close();
            Storage::removeDir($tmp_dir);
            return ['code' => 4, '解压失败！'];
        }
        $zip->close();

        //Clean temporary working dir
        Storage::removeDir($tmp_dir);
        \Cache::flush();
        //Fire event of plugin was installed
        $plugin = $plugins->getPlugin($name);

        //执行迁移文件
        $path = 'plugins/' . $name . '/migrations';
        if (is_dir(base_path($path))) {
            \Artisan::call('migrate', ['--force' => true, '--path' => $path]);
        }

        event(new \Yunshop\PluginsMarket\Events\PluginWasInstalled($plugin));
        return array('code' => 0, 'enable' => option('auto_enable_plugin'));
    }

}
