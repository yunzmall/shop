<?php


namespace app\common\services;


use app\common\helpers\Cache;
use app\common\models\UniAccount;
use Illuminate\Filesystem\Filesystem;
use vierbergenlars\SemVer\version;

class systemUpgrade
{
    public $update;
    public $filesystem;
    public $downloadUrl;

    private $key;
    private $secret;

    public function __construct($key = '', $secret = '')
    {
        $this->key = $key;
        $this->secret = $secret;

        $this->update = new AutoUpdate();
        $this->filesystem = app(Filesystem::class);

        $this->downloadUrl = 'https://downloads.yunzmall.com';
    }

    /**
     * 删除非法文件
     */
    public function deleteFile()
    {
        //file-删除指定文件，file-空 删除目录下所有文件
        $files = [
            [
                'path' => base_path('config'),
                'ext' => ['php'],
                'file' => [
                    base_path('database/migrations/main-menu.php'),
                    base_path('database/migrations/notice-template.php'),
                    base_path('database/migrations/notice.php'),
                    base_path('database/migrations/observer.php'),
                    base_path('database/migrations/widget.php'),
                ]
            ],
            [
                'path' => base_path('database/migrations'),
                'ext' => ['php'],
                'file' => [
                    base_path('database/migrations/2018_10_18_150312_add_unique_to_yz_member_income.php')
                ]
            ],
            [
                'path' => base_path('plugins/store-cashier/migrations'),
                'ext' => ['php'],
                'file' => [
                    base_path('plugins/store-cashier/migrations/2018_11_26_174034_fix_address_store.php'),
                    base_path('plugins/store-cashier/migrations/2017_08_03_170658_create_ims_yz_cashier_goods_table.php')
                ]
            ],
            [
                'path' => base_path('plugins/supplier/migrations'),
                'ext' => ['php'],
                'file' => [
                    base_path('plugins/supplier/migrations/2018_11_26_155528_update_ims_yz_order_and_goods.php')
                ]
            ],
            [
                'path' => base_path(),
                'file' => [
                    base_path('manifest.xml'),
                    base_path('map.json')
                ]
            ],
            [
                'path' => base_path('vendor/james-heinrich/getid3/demos'),
            ],
            [
                'path' => base_path('storage/app/auto-update/shop/vendor/james-heinrich/getid3/demos'),
            ]
        ];

        foreach ($files as $rows) {
            if (!is_dir($rows['path'])) {
                continue;
            }

            $scan_file = $this->filesystem->files($rows['path']);

            if (!empty($scan_file)) {
                foreach ($scan_file as $item) {
                    if (!empty($rows['file'])) {
                        foreach ($rows['file'] as $val) {
                            if ($val == $item) {
                                @unlink($item);
                            }
                        }
                    } else {
                        $file_info = pathinfo($item);

                        if (!in_array($file_info['extension'], $rows['ext'])) {
                            @unlink($item);
                        }
                    }
                }
            }
        }
    }

    /**
     * 执行迁移文件
     */
    public function runMigrate()
    {
        \Log::debug('----CLI----');

        $plugins_dir = $this->update->getDirsByPath('plugins', $this->filesystem);

        if (!empty($plugins_dir)) {
            \Artisan::call('update:version', ['version' => $plugins_dir]);
        }
    }

    /**
     * 更新本地前/后端版本号
     *
     * @param $updateList
     */
    public function setSystemVersion($updateList, $type = 1)
    {
        $version = $this->getFrontVersion($updateList);

        $str = file_get_contents(base_path('config/') . 'front-version.php');
        $str = preg_replace('/"[\d\.]+"/', '"' . $version . '"', $str);

        switch ($type) {
            case 1:
                file_put_contents(base_path('config/') . 'front-version.php', $str);
                break;
            case 2:
                file_put_contents(base_path('config/') . 'backend_version.php', $str);
                break;
        }
    }

    /**
     * 获取授权系统前端版本号
     *
     * @param $updateList
     * @return mixed
     */
    public function getFrontVersion($updateList)
    {
        rsort($updateList);
        $version = $updateList[0]['version'];

        return $version;
    }

    /**
     * 前端更新文件检测
     *
     * @param $key
     * @param $secret
     * @return array|null
     */
    public function frontendUpgrad($key, $secret)
    {
        $this->update->setUpdateFile('check_app.json');
        $this->update->setCurrentVersion(config('front-version'));
        $this->update->setUpdateUrl(config('auto-update.checkUrl')); //Replace with your server update directory
        $this->update->setBasicAuth($key, $secret);
        $this->update->checkUpdate();

        if ($this->update->newVersionAvailable()) {
            $list = $this->update->getUpdates();
        }

        krsort($list);

        return $list;
    }

    /**
     * 验证php版本
     *
     * @param $php_version
     * @return bool
     */
    public function checkPHPVersion($php_version)
    {
        if (version::lt($php_version, PHP_VERSION)) {
            return true;
        }

        return false;
    }

    /**
     * 迁移数据库信息
     */
    public function mvenv()
    {
        $database = config('database');
        $databaseSet = $database['connections'][$database['default']];

        $DB_HOST = $databaseSet['host'];
        $DB_USERNAME = $databaseSet['username'];
        $DB_PASSWORD = $databaseSet['password'];
        $DB_PORT = $databaseSet['port'];
        $DB_DATABASE = $databaseSet['database'];
        $DB_PREFIX = $databaseSet['prefix'];

        if (config('app.APP_Framework', false) == 'platform' && !empty($DB_HOST)) {
            $str = '<?php
$config = array();

$config[\'db\'][\'master\'][\'host\'] = \'' . $DB_HOST . '\';
$config[\'db\'][\'master\'][\'username\'] = \'' . $DB_USERNAME . '\';
$config[\'db\'][\'master\'][\'password\'] = \'' . $DB_PASSWORD . '\';
$config[\'db\'][\'master\'][\'port\'] = \'' . $DB_PORT . '\';
$config[\'db\'][\'master\'][\'database\'] = \'' . $DB_DATABASE . '\';
$config[\'db\'][\'master\'][\'tablepre\'] = \'' . $DB_PREFIX . '\';

$config[\'db\'][\'slave_status\'] = false;
$config[\'db\'][\'slave\'][\'1\'][\'host\'] = \'\';
$config[\'db\'][\'slave\'][\'1\'][\'username\'] = \'\';
$config[\'db\'][\'slave\'][\'1\'][\'password\'] = \'\';
$config[\'db\'][\'slave\'][\'1\'][\'port\'] = \'\';
$config[\'db\'][\'slave\'][\'1\'][\'database\'] = \'\';
$config[\'db\'][\'slave\'][\'1\'][\'tablepre\'] = \'\';
';
            $this->filesystem->put(base_path('database/config.php'), $str);

            if (file_exists(base_path('database/config.php'))) {
                $str = "APP_ENV=production
APP_KEY=base64:2q7s0Z714xS1L1WNN/8dsB69XDqOb4Qdptgh4X2ZtZU=
APP_DEBUG=true
APP_LOG_LEVEL=debug

APP_Framework=platform
IS_WEB=/admin/shop
ROOT_PATH=''
EXTEND_DIR=''";
                $this->filesystem->put(base_path('.env'), $str);
            }

        }
    }

    /**
     * 下载venddor组件压缩包
     */
    public function downloadVendorZip()
    {
        $url = $this->downloadUrl . $this->getSysUpgrade() . '.zip';

        $tmp_path = base_path($this->getSysUpgrade() . '_' . date('Y-m-d') . '.zip');

        if (file_exists($tmp_path)) {
            return;
        }

        try {
            Utils::download($url, $tmp_path);
            \Log::debug('----vendor zip 下载ok----');
        } catch (\Exception $e) {
            \Log::debug('----vendor zip 下载失败----');
        }
    }

    /**
     * 解压vendor压缩包
     *
     * @return bool
     */
    public function unVendorZip()
    {
        ini_set("memory_limit", "-1"); //不限制内存
        ini_set('max_execution_time', '0');

        $path = base_path($this->getSysUpgrade() . '_' . date('Y-m-d') . '.zip');

        if (file_exists($path)) {
            $zip = new \ZipArchive();
            $res = $zip->open($path);

            if ($res === true) {
                try {
                    $zip->extractTo(base_path());
                } catch (\Exception $e) {
                    $zip->close();
                    \Log::debug('----vendor zip 解压失败----');
                    return false;
                }
            } else {
                $zip->close();
                \Log::debug('----vendor zip 下载失败----');
                return false;
            }
            $zip->close();

            \Log::debug('----vendor zip 解压ok----');
            return true;
        }
    }

    /**
     * 删除vendor压缩包
     */
    public function delVendorZip()
    {
        $path = base_path($this->getSysUpgrade() . '_' . date('Y-m-d') . '.zip');

        if (file_exists($path)) {
            @unlink($path);
            \Log::debug('----vendor zip 删除ok----');
        }
    }

    /**
     * 删除缓存配置文件
     */
    public function delConfig()
    {
        $path = base_path('bootstrap/cache/config.php');

        if (file_exists($path)) {
            @unlink($path);
            \Log::debug('----config 删除ok----');
        }
    }

    /**
     * 清理视图文件
     *
     * @param Filesystem|null $filesystem
     */
    public function clearCache(Filesystem $filesystem = null)
    {
        \Log::debug('----View Cache Flush----');
        if (is_null($filesystem)) {
            $filesystem = app(Filesystem::class);
        }

        $allfiles = $filesystem->allFiles(storage_path('framework/views'));

        foreach ($allfiles as $rows) {
            @unlink($rows->getPathname());
        }
    }

    /**
     * 清理缓存
     */
    public function createCache()
    {
        $request = request();
        \Artisan::call('vendor:publish', ['--tag' => 'plugins', '--force' => true]);
        \Artisan::call('config:cache');
        \Cache::flush();
        app()->instance('request', $request);
    }

    /**
     * 下载vendor组件包重命名
     *
     * @param bool $res
     */
    public function renameVendor($res = true)
    {
        \Log::debug('------renameVendor-------', [$res]);

        if ($res) {
            rename(base_path('vendor'), base_path('vendor_' . date('Y-m-d')));
        } else {
            rename(base_path('vendor_' . date('Y-m-d')), base_path('vendor'));
        }
    }

    /**
     * 删除本地vendor组件包
     *
     * @param $path
     * @return bool
     */
    public function delVendor($path)
    {
        \Log::debug('------delVendor-------');

        if (is_dir($path)) {
            $p = scandir($path);
            if (count($p) > 2) {
                foreach ($p as $val) {
                    if ($val != "." && $val != "..") {
                        if (is_dir($path . '/' . $val)) {
                            $this->delVendor($path . '/' . $val . '/');
                        } else {
                            unlink($path . '/' . $val);
                        }
                    }
                }
            }
        }

        return rmdir($path);
    }

    /**
     * 设置更新的系统版本分支
     *
     * @param $ret
     */
    public function setSysUpgrade($ret)
    {
        Cache::put('sys_upgrade', $ret['upgrade'], 60);
    }

    /**
     * 获取系统版本分支
     *
     * @return mixed|string
     */
    public function getSysUpgrade()
    {
        if (Cache::has('sys_upgrade')) {
            return Cache::get('sys_upgrade');
        }

        return 'vendor';
    }

    /**
     * 设置vendor压缩包是否下载成功
     * @param $ret
     */
    public function setVendorZip($ret)
    {
        Cache::put('sys_vendor_zip', $ret['is_vendor_zip'], 60);
    }

    /**
     * 本地是否存在vendor压缩包
     *
     * @return false|mixed
     */
    public function isVendorZip()
    {
        if (!$this->getComposerStatus()) {
            return false;
        }

        if (Cache::has('sys_vendor_zip')) {
            return Cache::get('sys_vendor_zip');
        }

        return false;
    }

    /**
     * composer是否需要更新
     */
    public function setComposerStatus()
    {
        Cache::put('sys_composer_status', 1, 60);
    }

    /**
     * 获取composer状态
     *
     * @return false|mixed
     */
    public function getComposerStatus()
    {
        if (Cache::has('sys_composer_status')) {
            return Cache::get('sys_composer_status');
        }

        return false;
    }

    /**
     * 检查vendor压缩包下载文件是否有效
     *
     * @return bool
     */
    public function validateVendorZip()
    {
        $path = base_path($this->getSysUpgrade() . '_' . date('Y-m-d') . '.zip');

        if (file_exists($path) && filesize($path) > 0) {
            \Log::debug('--------validateVendorZip------');
            return true;
        }
        \Log::debug('--------no validateVendorZip------');

        return false;
    }

    /**
     * 获取本地前端版本
     *
     * @return \Illuminate\Config\Repository|\Illuminate\Contracts\Foundation\Application|mixed
     */
    public function getFrontendVersioin()
    {
        return config('front-version');
    }

    /**
     * 获取本地后端版本
     *
     * @return \Illuminate\Config\Repository|\Illuminate\Contracts\Foundation\Application|mixed
     */
    public function getBackendVersion()
    {
        return config('version');
    }

    /**
     * 授权数据
     *
     * @param $autoUpdate
     * @return mixed
     */
    public function OriginData($autoUpdate)
    {
        $autoUpdate->setUpdateFile('system_check.json');
        $res = $autoUpdate->remoteSystemVersion();

        return $res;
    }

    /**
     * 版本比较
     *
     * @param $autoUpdate
     * @return array
     */
    public function compareVersion($params)
    {
        $originBackendVersion = '未知';
        $originFrontendVersion = '未知';
        $hasUpgradeFile = 0;


        if (isset($params->result)) {
            $originBackendVersion = $params->result->backendVersion;
            $originFrontendVersion = $params->result->frontendVersion;
        }

        $localFrontendVersion = $this->getFrontendVersioin();
        $localBackendVersion = $this->getBackendVersion();

        if (version::eq($localFrontendVersion, $originFrontendVersion)) {
            $originFrontendVersion = '已经和本地版本一致';
        }

        if (version::eq($localBackendVersion, $originBackendVersion)) {
            $originBackendVersion = '已经和本地版本一致';
        }

        if ($originBackendVersion == '已经和本地版本一致' && $originFrontendVersion == '已经和本地版本一致') {
            $hasUpgradeFile = 1;
        }

        return [
            'localBackendVersion' => $localBackendVersion,
            'localFrontendVersion' => $localFrontendVersion,
            'originBackendVersion' => $originBackendVersion,
            'originFrontendVersion' => $originFrontendVersion,
            'hasUpgradeFile' => $hasUpgradeFile
        ];
    }

    /**
     * 域名信息
     *
     * @param $params
     * @return array
     */
    public function domainInfo($params)
    {
        $domainStatus = 0;
        $currentDomain = rtrim(request()->getHost(), '/');

        $originDomain = '未知';

        if (isset($params->result)) {
            $originDomain = $params->result->authDomain;
        }

        if (strpos($currentDomain, $originDomain) !== false) {
            $domainStatus = 1;
        }

        switch ($domainStatus) {
            case 0:
                $domainStatusTxt = '不一致';
                break;
            case 1:
                $domainStatusTxt = '已授权';
                break;
            default:
                $domainStatusTxt = '未知';
        }

        return [
            'originDomain' => $originDomain,
            'domainStatus' => $domainStatus,
            'domainStatusTxt' => $domainStatusTxt,
            'currentDomain' => $currentDomain
        ];
    }

    /**
     * 授权插件信息
     *
     * @param $params
     * @return array
     */
    public function originPlugins($params)
    {
        $originAuthPlugins = '未知';
        $originAllPlugins = [];
        $tmp_authPlugins = [];
        $tmp_allPlugins = [];
        $noAuthPlugins = [];  //未授权插件

        if (isset($params->result)) {
            $originAuthPlugins = $params->result->authPlugins;
            $originAllPlugins = $params->result->allPlugins;
        }

        foreach ($originAuthPlugins as $items) {
            $tmp_authPlugins[] = $items->name;
        }

        foreach ($originAllPlugins as $rows) {
            $tmp_allPlugins[] = $rows->name;
        }

        $noAuthPlugins = array_diff($tmp_allPlugins, $tmp_authPlugins);

        if (is_array($originAuthPlugins)) {
            $originAuthPlugins = count($originAuthPlugins);
        }

        return [
            'originAuthPlugins' => $originAuthPlugins,
            'noAuthPlugins' => $noAuthPlugins
        ];
    }

    /**
     * 本地安装插件
     *
     * @return array
     */
    public function localPlugins()
    {
        $localPlugins = [];

        $resource = opendir(base_path('plugins'));

        while ($file_name = @readdir($resource)) {
            if ($file_name == '.' || $file_name == '..')
                continue;
            $plugin_path = base_path('plugins') . '/' . $file_name;
            if (is_dir($plugin_path)) {
                $localPlugins[] = $file_name;
            }
        }

        closedir($resource);

        return $localPlugins;
    }

    /**
     * 插件信息
     *
     * @param $params
     * @return array
     */
    public function pluginsInfo($params)
    {
        //忽略
        $filter_plugins = ['wechat'];
        //未授权安装插件
        $localNoAuthPlugins = [];

        $originPlugins = $this->originPlugins($params);
        $localPlugins = $this->localPlugins();

        if (!empty($originPlugins['noAuthPlugins'])) {
            $localNoAuthPlugins = array_intersect($originPlugins['noAuthPlugins'], $localPlugins);
            $localNoAuthPlugins = array_diff($localNoAuthPlugins, $filter_plugins);
        }

        return [
            'localInstallPlugins' => count($localPlugins),
            'originAuthPlugins' => $originPlugins['originAuthPlugins'],
            'localNoAuthPlugins' => count($localNoAuthPlugins),
            'localNoAuthPluginsName' => $localNoAuthPlugins
        ];
    }

    /**
     * 商城是否允许更新
     *
     * @param $domainInfo
     * @param $pluginsInfo
     * @return int[]
     */
    public function systemStatus($domainInfo, $pluginsInfo)
    {
        $buttonStatus = 0;  // 0-禁止更新;1-允许更新

        if ($domainInfo['domainStatus'] == 1 && $pluginsInfo['localNoAuthPlugins'] == 0) {
            $buttonStatus = 1;
        }

        return ['status' => $buttonStatus];
    }

    /**
     * 系统版本检测
     *
     * @param $autoUpdate
     * @return array|string[]
     */
    public function systemCheck($autoUpdate)
    {
        $data = [
            'local_backend_version' => '未知',
            'local_frontend_version' => '未知',
            'origin_backend_version' => '未知',
            'origin_frontend_version' => '未知',
            'origin_domain' => '未知',
            'current_domain' => '未知',
            'domain_status_txt' => '未知',
            'origin_plugins_count' => '未知',
            'local_plugins_count' => '未知',
            'no_auth_plugins' => '未知',
            'upgrade_status' => '未知',
            'service_time' => '未知'
        ];

        $res = $this->OriginData($autoUpdate);
        $origin = json_decode($res);

        if (!isset($origin->result)) {
            return $data;
        }

        $ver = $this->compareVersion($origin);
        $domainInfo = $this->domainInfo($origin);
        $pluginsInfo = $this->pluginsInfo($origin);
        $upgrade = $this->systemStatus($domainInfo, $pluginsInfo);

        $data = [
            'local_backend_version' => $ver['localBackendVersion'],
            'local_frontend_version' => $ver['localFrontendVersion'],
            'origin_backend_version' => $ver['originBackendVersion'],
            'origin_frontend_version' => $ver['originFrontendVersion'],
            'hasUpgradeFile' => $ver['hasUpgradeFile'],
            'origin_domain' => $domainInfo['originDomain'],
            'current_domain' => $domainInfo['currentDomain'],
            'domain_status_txt' => $domainInfo['domainStatusTxt'],
            'origin_plugins_count' => $pluginsInfo['originAuthPlugins'],
            'local_plugins_count' => $pluginsInfo['localInstallPlugins'],
            'no_auth_plugins' => $pluginsInfo['localNoAuthPlugins'],
            'upgrade_status' => $upgrade['status'],
            'service_time' => $origin->result->remainingTime
        ];

        return $data;
    }

    /**
     * 更新日志
     *
     * @param $autoUpdate
     * @param $page
     * @return mixed
     */
    public function showLog($autoUpdate, $page)
    {
        $autoUpdate->setUpdateFile('show_log.json');
        $res = $autoUpdate->showLog($page);

        return $res;
    }

    /**
     * 企业管理前端更新
     *
     * @return void
     */
    public function business($version)
    {
        ini_set("memory_limit", "-1");
        ini_set('max_execution_time', '0');

        if (!file_exists(base_path('config/business_version.php')) || is_null($version)) {
            return false;
        }

        //验证
        if (version::lt(config('business_version'), $version) && $this->filesystem->isDirectory(base_path('plugins/business-pc'))) {
            //下载
            $path = base_path('business/business_font.zip');

            if (!file_exists($path)) {
                $url = $this->downloadUrl . '/company_backend/business_font.zip';

                try {
                    Utils::download($url, $path);
                    \Log::debug('----business zip 下载ok----');
                } catch (\Exception $e) {
                    \Log::debug('----business zip 下载失败----');
                    return;
                }
            }

            //删除本地文件
            if (file_exists($path)) {
                $this->filesystem->deleteDirectory(base_path('business/business_font'));
                \Log::debug('----business dir delete----');
            }

            //解压
            if (file_exists($path)) {
                $zip = new \ZipArchive();
                $res = $zip->open($path);

                if ($res === true) {
                    try {
                        $zip->extractTo(base_path('business'));
                    } catch (\Exception $e) {
                        $zip->close();
                        \Log::debug('----business zip 解压失败----');
                        return false;
                    }
                } else {
                    $zip->close();
                    \Log::debug('----business zip 解压下载失败----');
                    return false;
                }
                $zip->close();

                \Log::debug('----business zip 解压ok----');
            }

            //更新版本
            $str = file_get_contents(base_path('config/') . 'business_version.php');
            $str = preg_replace('/"[\d\.]+"/', '"' . $version . '"', $str);
            file_put_contents(base_path('config/') . 'business_version.php', $str);

            //删除压缩文件
            if (file_exists($path)) {
                @unlink($path);
                \Log::debug('----business zip 删除ok----');
            }
        }
    }

    public function getPluginsInfo($autoUpdate)
    {
        $res = $this->OriginData($autoUpdate);
        $origin = json_decode($res);

        $pls = json_decode($this->OriginPlugin($autoUpdate));

        $pluginsInfo = $this->pluginsInfo($origin);
        $pluginsInfo['localNoAuthPluginsTitle'] = [];

        if ($pluginsInfo['localNoAuthPlugins']) {

            foreach ($pluginsInfo['localNoAuthPluginsName'] as $name) {
                foreach ($pls->result->allPlugins as $rows) {
                    if ($name == $rows->name) {
                        $pluginsInfo['localNoAuthPluginsTitle'][] = $rows->title;
                    }
                }
            }
        }

        return $pluginsInfo;
    }

    public function OriginPlugin($autoUpdate)
    {
        $autoUpdate->setUpdateFile('origin_plugin.json');
        $res = $autoUpdate->remoteSystemVersion();

        return $res;
    }
}