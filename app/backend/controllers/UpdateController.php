<?php
/**
 * Created by PhpStorm.
 * Author: 芸众商城 www.yunzshop.com
 * Date: 18/04/2017
 * Time: 11:13
 */

namespace app\backend\controllers;

use app\common\components\BaseController;
use app\common\facades\Option;
use app\common\facades\Setting;
use app\common\helpers\Cache;
use app\common\models\UniAccount;
use app\common\services\AutoUpdate;
use app\common\services\Storage;
use app\common\services\Utils;
use Illuminate\Filesystem\Filesystem;
use vierbergenlars\SemVer\version;

class UpdateController extends BaseController
{

    public function index()
    {
        $list = [];

        //删除非法文件
        $this->deleteFile();
        //执行迁移文件
        $this->runMigrate();
        //清理缓存
        $this->createCache();

        $key = Setting::get('shop.key')['key'];
        $secret = Setting::get('shop.key')['secret'];

        $update = new AutoUpdate(null, null, 300);
        $update->setUpdateFile('check_app.json');

        if (is_file(base_path() . '/' . 'config/front-version.php')) {
            $update->setCurrentVersion(config('front-version'));
            $version = config('front-version');
        } else {
            $update->setCurrentVersion(config('version'));
            $version = config('version');
        }

        $update->setUpdateUrl(config('auto-update.checkUrl')); //Replace with your server update directory

        $update->setBasicAuth($key, $secret);

        $update->checkUpdate();

        if ($update->newVersionAvailable()) {
            $list = $update->getUpdates();
        }

        krsort($list);

        if (!empty($list[0]['php_version']) && !$this->checkPHPVersion($list[0]['php_version'])) {
            $list = [];
        }

        return view('update.upgrad', [
            'list' => $list,
            'version' => $version,
            'count' => count($list)
        ])->render();
    }

    /**
     * footer检测更新
     * @return \Illuminate\Http\JsonResponse
     */
    public function check()
    {
        $result = ['msg' => '', 'last_version' => '', 'updated' => 0];
        $key = Setting::get('shop.key')['key'];
        $secret = Setting::get('shop.key')['secret'];
        if (!$key || !$secret) {
            return;
        }

        $update = new AutoUpdate(null, null, 300);
        $update->setUpdateFile('check_app.json');
        $update->setCurrentVersion(config('version'));
        $update->setUpdateUrl(config('auto-update.checkUrl')); //Replace with your server update directory
        $update->setBasicAuth($key, $secret);
        //$update->setBasicAuth();

        $res = $update->checkUpdate();

        if ($res === 'unknown') {
            $result = ['updated' => -1];
        }

        //Check for a new update
        if ($res === false) {
            $result['msg'] = 'Could not check for updates! See log file for details.';
            response()->json($result)->send();
            return;
        }

        if (isset($res['result']) && 0 == $res['result']) {
            $res['updated'] = 0;
            return response()->json($res)->send();
        }

        if ($update->newVersionAvailable()) {
            $result['last_version'] = $update->getLatestVersion()->getVersion();
            $result['updated'] = 1;
            $result['current_version'] = config('version');
        }
        response()->json($result)->send();
        return;
    }


    /**
     * 检测更新
     * @return \Illuminate\Http\JsonResponse
     */
    public function verifyheck()
    {
        set_time_limit(0);

        $filesystem = app(Filesystem::class);
        $update = new AutoUpdate(null, null, 300);

        $filter_file = ['index.php', 'composer.json', 'README.md'];
        $plugins_dir = $update->getDirsByPath('plugins', $filesystem);

        $result = ['result' => 0, 'msg' => '网络请求超时', 'last_version' => ''];
        $key = Setting::get('shop.key')['key'];
        $secret = Setting::get('shop.key')['secret'];
        if (!$key || !$secret) {
            return;
        }

        $update = new AutoUpdate(null, null, 300);
        $update->setUpdateFile('backcheck_app.json');
        $update->setCurrentVersion(config('version'));

        $update->setUpdateUrl(config('auto-update.checkUrl')); //Replace with your server update directory

        $update->setBasicAuth($key, $secret);
        //$update->setBasicAuth();

        //Check for a new update
        $ret = $update->checkBackUpdate();

        if (is_array($ret)) {
            if (!empty($ret['php-version']) && !$this->checkPHPVersion($ret['php-version'])) {
                $result = ['result' => 98, 'msg' => '服务器php版本(v' . PHP_VERSION . ')过低,不符合更新条件,建议升级到php版本>=(v' . $ret['php-version'] . ')', 'last_version' => ''];

                response()->json($result)->send();
            }

            if (1 == $ret['result']) {
                $files = [];

                if (!empty($ret['files'])) {
                    foreach ($ret['files'] as $file) {
                        //忽略指定文件
                        if (in_array($file['path'], $filter_file)) {
                            continue;
                        }

                        //忽略前端样式文件
                        if (preg_match('/^static\/app/', $file['path'])) {
                            continue;
                        }

                        //忽略没有安装的插件
                        if (preg_match('/^plugins/', $file['path'])) {
                            $sub_dir = substr($file['path'], strpos($file['path'], '/') + 1);
                            $sub_dir = substr($sub_dir, 0, strpos($sub_dir, '/'));

                            if (!in_array($sub_dir, $plugins_dir)) {
                                continue;
                            }
                        }

                        //忽略前后端\wq版本号记录文件
                        if (($file['path'] == 'config/front-version.php'
                                || $file['path'] == 'config/backend_version.php'
                                || $file['path'] == 'config/wq-version.php')
                            && is_file(base_path() . '/' . $file['path'])) {
                            continue;
                        }

                        $entry = base_path() . '/' . $file['path'];

                        if ($file['path'] == 'composer.lock' && md5_file($entry) != $file['md5']) {
                            $this->setComposerStatus();
                        }

                        //如果本地没有此文件或者文件与服务器不一致
                        if (!is_file($entry) || md5_file($entry) != $file['md5']) {
                            $files[] = array(
                                'path' => $file['path'],
                                'download' => 0
                            );
                            $difffile[] = $file['path'];
                        } else {
                            $samefile[] = $file['path'];
                        }
                    }
                }

                $tmpdir = storage_path('app/public/tmp/' . date('ymd'));
                if (!is_dir($tmpdir)) {
                    $filesystem->makeDirectory($tmpdir, 0755, true);
                }

                $ret['files'] = $files;
                file_put_contents($tmpdir . "/file.txt", json_encode($ret));

                if (empty($files)) {
                    $version = config('version');
                    //TODO 更新日志记录
                } else {
                    $version = $ret['version'];
                }

                $result = [
                    'result' => 1,
                    'version' => $version,
                    'files' => $ret['files'],
                    'filecount' => count($files),
                    'log' => $ret['log']
                ];
            } else {
                preg_match('/"[\d\.]+"/', file_get_contents(base_path('config/') . 'version.php'), $match);
                $version = $match ? trim($match[0], '"') : '1.0.0';

                $result = ['result' => 99, 'msg' => '', 'last_version' => $version];
            }
        }

        response()->json($result)->send();
    }

    public function fileDownload()
    {
        $filesystem = app(Filesystem::class);

        $tmpdir = storage_path('app/public/tmp/' . date('ymd'));
        $f = file_get_contents($tmpdir . "/file.txt");
        $upgrade = json_decode($f, true);
        $files = $upgrade['files'];
        $total = count($upgrade['files']);
        $path = "";
        $nofiles = \YunShop::request()->nofiles;
        $status = 1;

        $update = new AutoUpdate(null, null, 300);

        //找到一个没更新过的文件去更新
        foreach ($files as $f) {
            if (empty($f['download'])) {
                $path = $f['path'];
                break;
            }
        }

        if (!empty($path)) {
            if (!empty($nofiles)) {
                if (in_array($path, $nofiles)) {
                    foreach ($files as &$f) {
                        if ($f['path'] == $path) {
                            $f['download'] = 1;
                            break;
                        }
                    }
                    unset($f);
                    $upgrade['files'] = $files;
                    $tmpdir = storage_path('app/public/tmp/' . date('ymd'));
                    if (!is_dir($tmpdir)) {
                        $filesystem->makeDirectory($tmpdir, 0755, true);
                    }
                    file_put_contents($tmpdir . "/file.txt", json_encode($upgrade));

                    return response()->json(['result' => 3])->send();
                }
            }

            $key = Setting::get('shop.key')['key'];
            $secret = Setting::get('shop.key')['secret'];
            if (!$key || !$secret) {
                return;
            }

            $update->setUpdateFile('backdownload_app.json');
            $update->setCurrentVersion(config('version'));

            $update->setUpdateUrl(config('auto-update.checkUrl')); //Replace with your server update directory

            $update->setBasicAuth($key, $secret);

            //Check for a new download
            $ret = $update->checkBackDownload([
                'path' => urlencode($path)
            ]);

            $this->setSysUpgrade($ret);
            $this->setVendorZip($ret);

            //下载vendor
            if ($this->isVendorZip()) {
                $this->downloadVendorZip();
            }

            //预下载
            if (is_array($ret)) {
                $path = $ret['path'];
                $dirpath = dirname($path);
                $save_path = storage_path('app/auto-update/shop') . '/' . $dirpath;

                if (!is_dir($save_path)) {
                    $filesystem->makeDirectory($save_path, 0755, true);
                }

                //新建
                $content = base64_decode($ret['content']);
                file_put_contents(storage_path('app/auto-update/shop') . '/' . $path, $content);

                $success = 0;
                foreach ($files as &$f) {
                    if ($f['path'] == $path) {
                        $f['download'] = 1;
                        break;
                    }
                    if ($f['download']) {
                        $success++;
                    }
                }

                unset($f);
                $upgrade['files'] = $files;
                $tmpdir = storage_path('app/public/tmp/' . date('ymd'));

                if (!is_dir($tmpdir)) {
                    $filesystem->makeDirectory($tmpdir, 0755, true);
                }

                file_put_contents($tmpdir . "/file.txt", json_encode($upgrade));
            }
        } else {
            //覆盖
            foreach ($files as $f) {
                $path = $f['path'];
                $file_dir = dirname($path);

                if (!is_dir(base_path($file_dir))) {
                    $filesystem->makeDirectory(base_path($file_dir), 0755, true);
                }

                $content = file_get_contents(storage_path('app/auto-update/shop') . '/' . $path);

                //去除空文件判断
                if (!empty($content)) {
                    file_put_contents(base_path($path), $content);

                    @unlink(storage_path('app/auto-update/shop') . '/' . $path);
                }
            }
            $status = 2;
            $success = $total;
            $response = response()->json([
                'result' => $status,
                'total' => $total,
                'success' => $success
            ]);

            if ($this->isVendorZip() && $this->validateVendorZip()) {
                $this->delConfig();
                $this->renameVendor();
                //解压
                $res = $this->unVendorZip();

                if (!$res) {
                    $this->renameVendor($res);
                }
            }

            //清理缓存
            $this->clearCache($filesystem);

            \Log::debug('----Queue Restarth----');
            app('supervisor')->restart();

            if ($this->isVendorZip()) {
                if ($this->validateVendorZip()) {
                    $this->delVendor(base_path('vendor_' . date('Y-m-d')));
                }

                $this->delVendorZip();
            }

            //返回
            $response->send();
        }

        response()->json([
            'result' => $status,
            'total' => $total,
            'success' => $success
        ])->send();
    }

    /**
     * 开始下载并更新程序
     * @return \Illuminate\Http\RedirectResponse
     */
    public function startDownload()
    {
        \Cache::flush();
        $resultArr = ['msg' => '', 'status' => 0, 'data' => []];
        set_time_limit(0);

        $key = Setting::get('shop.key')['key'];
        $secret = Setting::get('shop.key')['secret'];

        $update = new AutoUpdate(null, null, 300);
        $update->setUpdateFile('check_app.json');

        if (is_file(base_path() . '/' . 'config/front-version.php')) {
            $update->setCurrentVersion(config('front-version'));
        } else {
            $update->setCurrentVersion(config('version'));
        }

        $update->setUpdateUrl(config('auto-update.checkUrl')); //Replace with your server update directory
        Setting::get('auth.key');
        $update->setBasicAuth($key, $secret);

        //Check for a new update
        if ($update->checkUpdate() === false) {
            $resultArr['msg'] = 'Could not check for updates! See log file for details.';
            response()->json($resultArr)->send();
            return;
        }

        if ($update->newVersionAvailable()) {
            /*$update->onEachUpdateFinish(function($version){
                \Log::debug('----CLI----');
                \Artisan::call('update:version' ,['version'=>$version]);
            });*/

            $result = $update->update();

            if ($result === true) {
                $list = $update->getUpdates();
                if (!empty($list)) {
                    $this->setSystemVersion($list);
                    if (!is_dir(base_path('config/shop-foundation'))) {
                        \Artisan::call('config:cache');
                    }
                }

                $resultArr['status'] = 1;
                $resultArr['msg'] = '更新成功';
            } else {
                $resultArr['msg'] = '更新失败: ' . $result;
                if ($result = AutoUpdate::ERROR_SIMULATE) {
                    $resultArr['data'] = $update->getSimulationResults();
                }
            }
        } else {
            $resultArr['msg'] = 'Current Version is up to date';
        }
        response()->json($resultArr)->send();
        return;
    }

    /**
     * 更新本地前端版本号
     *
     * @param $updateList
     */
    private function setSystemVersion($updateList)
    {
        $version = $this->getFrontVersion($updateList);

        $str = file_get_contents(base_path('config/') . 'front-version.php');
        $str = preg_replace('/"[\d\.]+"/', '"' . $version . '"', $str);
        file_put_contents(base_path('config/') . 'front-version.php', $str);
    }

    /**
     * 获取前端版本号
     *
     * @param $updateList
     * @return mixed
     */
    private function getFrontVersion($updateList)
    {
        rsort($updateList);
        $version = $updateList[0]['version'];

        return $version;
    }

    /**
     * 删除文件
     *
     */
    private function deleteFile()
    {
        $filesystem = app(Filesystem::class);

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

        if (config('app.framework') == false) {
            array_push($files, [
                'path' => base_path(),
                'ext' => ['php'],
                'file' => [
                    base_path('index.php')
                ]
            ]);
        }

        foreach ($files as $rows) {
            if (!is_dir($rows['path'])) {
                continue;
            }

            $scan_file = $filesystem->files($rows['path']);

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

    private function dataSecret()
    {
        $uniAccount = UniAccount::get();

        foreach ($uniAccount as $u) {
            \YunShop::app()->uniacid = $u->uniacid;
            \Setting::$uniqueAccountId = $u->uniacid;

            $pay = \Setting::get('shop.pay');

            if (!isset($pay['secret'])) {
                foreach ($pay as $key => &$val) {
                    if (!empty($val)) {
                        switch ($key) {
                            case 'alipay_app_id':
                            case 'rsa_private_key':
                            case 'rsa_public_key':
                            case 'alipay_number':
                            case 'alipay_name':
                                $val = encrypt($val);
                                break;
                        }
                    }
                }

                $pay['secret'] = 1;
                \Setting::set('shop.pay', $pay);
            }
        }
    }

    public function pirate()
    {
        return view('update.pirate', [])->render();
    }

    private function runMigrate()
    {
        \Log::debug('----CLI----');
        $update = new AutoUpdate();
        $filesystem = app(Filesystem::class);

        $plugins_dir = $update->getDirsByPath('plugins', $filesystem);

        if (!empty($plugins_dir)) {
            \Artisan::call('update:version', ['version' => $plugins_dir]);
        }
    }

    private function checkPHPVersion($php_version)
    {
        if (version::lt($php_version, PHP_VERSION)) {
            return true;
        }

        return false;
    }

    private function downloadVendorZip()
    {
        $url = 'https://downloads.yunzmall.com/' . $this->getSysUpgrade() . '.zip';

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

    private function unVendorZip()
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

    private function delVendorZip()
    {
        $path = base_path($this->getSysUpgrade() . '_' . date('Y-m-d') . '.zip');

        if (file_exists($path)) {
            @unlink($path);
            \Log::debug('----vendor zip 删除ok----');
        }
    }

    private function delConfig()
    {
        $path = base_path('bootstrap/cache/config.php');

        if (file_exists($path)) {
            @unlink($path);
            \Log::debug('----config 删除ok----');
        }
    }

    private function clearCache(Filesystem $filesystem = null)
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

    private function createCache()
    {
        $request = request();
        \Artisan::call('config:cache');
        \Cache::flush();
        app()->instance('request', $request);
    }

    private function renameVendor($res = true)
    {
        \Log::debug('------renameVendor-------', [$res]);

        if ($res) {
            rename(base_path('vendor'), base_path('vendor_' . date('Y-m-d')));
        } else {
            rename(base_path('vendor_' . date('Y-m-d')), base_path('vendor'));
        }
    }

    private function delVendor($path)
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

    private function setSysUpgrade($ret)
    {
        Cache::put('sys_upgrade', $ret['upgrade'], 60);
    }

    private function getSysUpgrade()
    {
        if (Cache::has('sys_upgrade')) {
            return Cache::get('sys_upgrade');
        }

        return 'vendor';
    }

    private function setVendorZip($ret)
    {
        Cache::put('sys_vendor_zip', $ret['is_vendor_zip'], 60);
    }

    private function isVendorZip()
    {
        if (!$this->getComposerStatus()) {
            return false;
        }

        if (Cache::has('sys_vendor_zip')) {
            return Cache::get('sys_vendor_zip');
        }

        return false;
    }

    private function setComposerStatus()
    {
        Cache::put('sys_composer_status', 1, 60);
    }

    private function getComposerStatus()
    {
        if (Cache::has('sys_composer_status')) {
            return Cache::get('sys_composer_status');
        }

        return false;
    }

    private function validateVendorZip()
    {
        $path = base_path($this->getSysUpgrade() . '_' . date('Y-m-d') . '.zip');

        if (file_exists($path) && filesize($path) > 0) {
            \Log::debug('--------validateVendorZip------');
            return true;
        }
        \Log::debug('--------no validateVendorZip------');

        return false;
    }
}
