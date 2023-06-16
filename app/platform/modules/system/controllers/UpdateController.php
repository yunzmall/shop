<?php
/**
 * Created by PhpStorm.
 * Author:  
 * Date: 18/04/2017
 * Time: 11:13
 */

namespace app\platform\modules\system\controllers;


use app\common\components\BaseController;
use app\common\facades\Setting;
use app\common\services\AutoUpdate;
use app\common\services\systemUpgrade;
use app\host\HostManager;

class UpdateController extends BaseController
{
    private $set;
    private $key;
    private $secret;

    public $systemUpgrade;
    public $update;

    public function __construct()
    {
        $this->set = Setting::getNotUniacid('platform_shop.key');
        $this->key = $this->set['key'];
        $this->secret = $this->set['secret'];

        $this->systemUpgrade = new systemUpgrade($this->key, $this->secret);
        $this->update = new AutoUpdate(null, null, 300);

        $this->__init();
    }

    private function __init()
    {
        if (!$this->key || !$this->secret) {
            return;
        }

        $this->update->setBasicAuth($this->key, $this->secret);
        $this->update->setUpdateUrl(config('auto-update.checkUrl')); //Replace with your server update directory
    }

    public function systemInfo()
    {
        //删除非法文件
        $this->systemUpgrade->deleteFile();
        //执行迁移文件
        $this->systemUpgrade->runMigrate();
        //清理缓存
        $this->systemUpgrade->createCache();

        $result = $this->systemUpgrade->systemCheck($this->update);

        $data = [
            'result' => 1,
            'msg' => 'ok',
            'data' => $result
        ];
        response()->json($data)->send();
    }

    public function log()
    {
        $page = \YunShop::request()->page ?: 1;

        $log = $this->systemUpgrade->showLog($this->update, $page);
        $log = json_decode($log);

        $data = [
            'result' => 1,
            'msg' => 'ok',
            'data' => $log->result
        ];
        response()->json($data)->send();
    }

    /**
     * 废弃
     *
     * @return array|string
     * @throws \Throwable
     */
    public function upgrade()
    {
        $list = [];

        //删除非法文件
        $this->systemUpgrade->deleteFile();
        //执行迁移文件
        $this->systemUpgrade->runMigrate();

        $this->update->setUpdateFile('check_app.json');

        if (is_file(base_path() . '/' . 'config/front-version.php')) {
            $this->update->setCurrentVersion(config('front-version'));
            $version = config('front-version');
        } else {
            $this->update->setCurrentVersion(config('version'));
            $version = config('version');
        }

        $this->update->checkUpdate();

        if ($this->update->newVersionAvailable()) {
            $list = $this->update->getUpdates();
        }

        krsort($list);

        if (!empty($list[0]['php_version']) && !$this->systemUpgrade->checkPHPVersion($list[0]['php_version'])) {
            $list = [];
        }

        return view('system.update.upgrad', [
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

        $this->update->setUpdateFile('check_app.json');
        $this->update->setCurrentVersion(config('version'));

        $res = $this->update->checkUpdate();

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

        if ($this->update->newVersionAvailable()) {
            $result['last_version'] = $this->update->getLatestVersion()->getVersion();
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
    public function verifyCheck()
    {
        set_time_limit(0);

        //执行迁移文件
        $this->systemUpgrade->runMigrate();

        $filter_file = ['composer.json', 'README.md'];
        $plugins_dir = $this->update->getDirsByPath('plugins', $this->systemUpgrade->filesystem);

        $result = ['result' => -2, 'msg' => '网络请求超时', 'version' => ''];

        if (!$this->key || !$this->secret) {
            return response()->json(['result' => -1, 'msg' => '商城未授权', 'data' => []]);
        }

        //前端更新文件检测
        $frontendUpgrad = $this->systemUpgrade->frontendUpgrad($this->key, $this->secret);

        //后台更新文件检测
        $this->update->setUpdateFile('backcheck_app.json');
        $this->update->setCurrentVersion(config('version'));

        //Check for a new update
        $ret = $this->update->checkBackUpdate();

        if ($ret == 'unknown') {
            $result = ['result' => -1];
        }

        if (is_array($ret)) {
            if (!empty($ret['php-version']) && !$this->systemUpgrade->checkPHPVersion($ret['php-version'])) {
                $result = ['result' => 98, 'msg' => '服务器php版本(v' . PHP_VERSION . ')过低,不符合更新条件,建议升级到php版本>=(v' . $ret['php-version'] . ')', 'last_version' => ''];

                response()->json($result)->send();
                exit;
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

                        //忽略前后端版本号记录文件
                        if (($file['path'] == 'config/front-version.php'
                                || $file['path'] == 'config/backend_version.php'
                                || $file['path'] == 'config/wq-ersion.php'
                                || $file['path'] == 'config/business_version.php')
                            && is_file(base_path() . '/' . $file['path'])) {
                            continue;
                        }

                        $entry = base_path() . '/' . $file['path'];

                        if ($file['path'] == 'composer.lock' && md5_file($entry) != $file['md5']) {
                            $this->systemUpgrade->setComposerStatus();
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
                    $this->systemUpgrade->filesystem->makeDirectory($tmpdir, 0755, true);
                }

                $ret['files'] = $files;
                file_put_contents($tmpdir . "/file.txt", json_encode($ret));

                if (empty($files)) {
                    $version = config('version');
                    //TODO 更新日志记录
                } else {
                    $version = $ret['version'];
                }

                $this->systemUpgrade->mvenv();

                //business更新
                $this->systemUpgrade->business($ret['business_version']);

                $result = [
                    'result' => 1,
                    'version' => $version,
                    'files' => $ret['files'],
                    'filecount' => count($files),
                    'frontendUpgrad' => count($frontendUpgrad),
                    'list' => $frontendUpgrad
                ];
            } else {
                preg_match('/"[\d\.]+"/', file_get_contents(base_path('config/') . 'version.php'), $match);
                $version = $match ? trim($match[0], '"') : '1.0.0';

                $result = ['result' => 99, 'msg' => '', 'version' => $version];
            }
        }

        response()->json($result)->send();
    }

    public function fileDownload()
    {
        $protocol = \YunShop::request()->protocol;

        /*if (!$protocol['file'] || !$protocol['update']) {
            response()->json([
                'result' => 0,
                'msg' => '未同意更新协议，禁止更新',
                'data' => []
            ])->send();
            exit;
        }*/

        $tmpdir = storage_path('app/public/tmp/' . date('ymd'));
        $f = file_get_contents($tmpdir . "/file.txt");
        $upgrade = json_decode($f, true);
        $files = $upgrade['files'];
        $total = count($upgrade['files']);
        $path = "";
        $nofiles = \YunShop::request()->nofiles;
        $status = 1;

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
                        $this->systemUpgrade->filesystem->makeDirectory($tmpdir, 0755, true);
                    }
                    file_put_contents($tmpdir . "/file.txt", json_encode($upgrade));

                    return response()->json(['result' => 3])->send();
                }
            }

            $this->update->setUpdateFile('backdownload_app.json');
            $this->update->setCurrentVersion(config('version'));

            //Check for a new download
            $ret = $this->update->checkBackDownload([
                'path' => urlencode($path)
            ]);

            $this->systemUpgrade->setSysUpgrade($ret);
            $this->systemUpgrade->setVendorZip($ret);

            //下载vendor
            if ($this->systemUpgrade->isVendorZip()) {
                $this->systemUpgrade->downloadVendorZip();
            }

            //预下载
            if (is_array($ret)) {
                $path = $ret['path'];
                $dirpath = dirname($path);
                $save_path = storage_path('app/auto-update/shop') . '/' . $dirpath;

                if (!is_dir($save_path)) {
                    $this->systemUpgrade->filesystem->makeDirectory($save_path, 0755, true);
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
                    $this->systemUpgrade->filesystem->makeDirectory($tmpdir, 0755, true);
                }

                file_put_contents($tmpdir . "/file.txt", json_encode($upgrade));
            }
        } else {
            //检查并下载框架更新文件
            $this->startDownloadFramework();

            //覆盖
            foreach ($files as $f) {
                $path = $f['path'];
                $file_dir = dirname($path);

                if (!is_dir(base_path($file_dir))) {
                    $this->systemUpgrade->filesystem->makeDirectory(base_path($file_dir), 0755, true);
                }

                $content = file_get_contents(storage_path('app/auto-update/shop') . '/' . $path);

                //去除空文件判断
                if (!empty($content)) {
                    file_put_contents(base_path($path), $content);

                    @unlink(storage_path('app/auto-update/shop') . '/' . $path);
                }
            }

            //执行迁移文件
            $this->systemUpgrade->runMigrate();

            $status = 2;
            $success = $total;
            $response = response()->json([
                'result' => $status,
                'total' => $total,
                'success' => $success
            ]);

            if ($this->systemUpgrade->isVendorZip() && $this->systemUpgrade->validateVendorZip()) {
                $this->systemUpgrade->delConfig();
                $this->systemUpgrade->renameVendor();
                $res = $this->systemUpgrade->unVendorZip();

                if (!$res) {
                    $this->systemUpgrade->renameVendor($res);
                }
            }

            //清理缓存
            $this->systemUpgrade->clearCache($this->systemUpgrade->filesystem);

            \Log::debug('----Queue Restarth----');
            app('supervisor')->restart();
            (new HostManager())->restart();

            if ($this->systemUpgrade->isVendorZip()) {
                if ($this->systemUpgrade->validateVendorZip()) {
                    $this->systemUpgrade->delVendor(base_path('vendor_' . date('Y-m-d')));
                }

                $this->systemUpgrade->delVendorZip();
            }

            $response->send();
        }

        response()->json([
            'result' => $status,
            'total' => $total,
            'success' => $success
        ])->send();
    }

    /**
     * 开始下载并更新前端vue
     * @return \Illuminate\Http\RedirectResponse
     */
    public function startDownload()
    {
        \Cache::flush();
        $resultArr = ['msg' => '', 'status' => 0, 'data' => []];
        set_time_limit(0);

        $this->update->setUpdateFile('check_app.json');

        if (is_file(base_path() . '/' . 'config/front-version.php')) {
            $this->update->setCurrentVersion(config('front-version'));
        } else {
            $this->update->setCurrentVersion(config('version'));
        }

        //Check for a new update
        if ($this->update->checkUpdate() === false) {
            $resultArr['msg'] = 'Could not check for updates! See log file for details.';
            response()->json($resultArr)->send();
            return;
        }

        if ($this->update->newVersionAvailable()) {
            $result = $this->update->update();

            if ($result === true) {
                $list = $this->update->getUpdates();
                if (!empty($list)) {
                    $this->systemUpgrade->setSystemVersion($list);
                    if (!is_dir(base_path('config/shop-foundation'))) {
                        \Artisan::call('config:cache');
                    }
                }

                $resultArr['status'] = 1;
                $resultArr['msg'] = '更新成功';
            } else {
                $resultArr['msg'] = '更新失败: ' . $result;
                if ($result = AutoUpdate::ERROR_SIMULATE) {
                    $resultArr['data'] = $this->update->getSimulationResults();
                }
            }
        } else {
            $resultArr['msg'] = 'Current Version is up to date frontend';
        }
        response()->json($resultArr)->send();
        return;
    }

    /**
     * 开始下载并更新框架vue
     * @return \Illuminate\Http\RedirectResponse
     */
    public function startDownloadFramework()
    {
        $resultArr = ['msg' => '', 'status' => 0, 'data' => []];
        set_time_limit(0);

        $this->update->setUpdateFile('check_fromework.json');
        $this->update->setCurrentVersion(config('backend_version'));

        //Check for a new update
        if ($this->update->checkUpdate() === false) {
            $resultArr['msg'] = 'Could not check for updates! See log file for details.';
            response()->json($resultArr)->send();
            return;
        }

        if ($this->update->newVersionAvailable()) {
            $result = $this->update->update(2);

            if ($result === true) {
                $list = $this->update->getUpdates();
                if (!empty($list)) {
                    $this->systemUpgrade->setSystemVersion($list, 2);
                }

                $resultArr['status'] = 1;
                $resultArr['msg'] = '-----------后台框架更新成功---------';
                \Log::debug($resultArr['msg']);
            } else {
                $resultArr['msg'] = '---------后台框架更新失败---------: ' . $result;
                if ($result = AutoUpdate::ERROR_SIMULATE) {
                    $resultArr['data'] = $this->update->getSimulationResults();
                }

                response()->json($resultArr)->send();
                return;
            }
        } else {
            \Log::debug('--------后台框架已是最新版本-------');
        }
    }

    public function pirate()
    {
        return view('update.pirate', [])->render();
    }
}
