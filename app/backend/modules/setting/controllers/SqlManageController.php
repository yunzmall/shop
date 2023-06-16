<?php
/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 2022-03-10
 * Time: 10:26
 */

namespace app\backend\modules\setting\controllers;


use app\common\components\BaseController;
use app\common\models\SqlInstallLog;

class SqlManageController extends BaseController
{
    public function index()
    {
        $log = SqlInstallLog::first();
        $is_install = 0;
        $access_path = '';
        if ($log) {
            $access_url_len = strlen(base_path());
            $access_path = request()->getSchemeAndHttpHost().'/'.substr($log->path, $access_url_len+1).'/phpmyadmin';
            if (config('app.framework') != 'platform') {
                $access_path = request()->getSchemeAndHttpHost().'/addons/yun_shop/'.substr($log->path, $access_url_len+1).'/phpmyadmin';
            }
            $is_install = 1;
        }
        $list = [
            [
                'download_url' => 'https://downloads.yunzmall.com/phpmyadmin.zip',
                'is_install' => $is_install,
                'access_url' => $access_path,
            ]
        ];
        return view('setting.sql-manage.index', [
            'list' => json_encode($list),
        ]);
    }

    public function download()
    {
        $url = request()->url;
        $file_info = pathinfo($url);
        $basename = $file_info['basename'];
        $download_path = base_path().'/'.$basename;
        $get_res = file_get_contents($url);
        if (!$get_res) {
            return $this->errorJson('请求链接数据失败');
        }
        file_put_contents($download_path, file_get_contents($url));
        if (!file_exists($download_path)) {
            return $this->errorJson('下载失败');
        }
        $zip = new \ZipArchive();
        $new_path = base_path(md5(str_random(6).time()));
        if ($zip->open($download_path)) {
            $zip->extractTo($new_path);
            $zip->close();
        } else {
            return $this->errorJson('解压失败');
        }
        $log_arr = [
            'path' => $new_path,
        ];
        $log = SqlInstallLog::first();
        if ($log) {
            $log->update($log_arr);
        } else {
            SqlInstallLog::create($log_arr);
        }
        @unlink($download_path);
        return $this->successJson('下载安装成功');
    }

    public function uninstall()
    {
        $log = SqlInstallLog::first();
        if (!$log) {
            return $this->errorJson('安装记录不存在');
        }
        if (file_exists($log->path)) {
            if (!$this->removeDir($log->path)) {
                return $this->errorJson('卸载失败');
            }
            $log->delete();
        }
        return $this->successJson('卸载成功');
    }

    private function removeDir($dir_name)
    {
        if (!is_dir($dir_name)) {
            return false;
        }
        $handle = @opendir($dir_name);
        while ($file = @readdir($handle)) {
            if ($file != '.' && $file != '..') {
                $dir = $dir_name . '/' . $file;
                is_dir($dir) ? $this->removeDir($dir) : @unlink($dir);
            }
        }
        closedir($handle);
        @rmdir($dir_name);
        return true;
    }
}