<?php
/**
 * Created by PhpStorm.
 * 
 *
 *
 * Date: 2022/2/17
 * Time: 15:10
 */

namespace app\backend\modules\upload\services;

use app\common\services\Utils;
use app\platform\modules\system\models\SystemSetting;

/**
 * 需要一个上传文件（图片、视频、音频、pdf等）的接口，未看到有相应的接口，写个服务先
 * Class FileService
 * @package app\backend\modules\upload\services
 */
class FileService
{
    /**
     * @var \Illuminate\Http\UploadedFile
     */
    private $file;

    private $fileName;

    private $basePath;

    private $uploadPath;

    private $remote;

    public $maxSize = 5 * 1024 * 1024;

    public $extLimit = null;

    public function setFile(\Illuminate\Http\UploadedFile $file)
    {
        $this->file = $file;
    }

    /**
     * @return array|\Illuminate\Http\UploadedFile|\Illuminate\Http\UploadedFile[]|null
     * @throws \Exception
     */
    private function getFile()
    {
        if (!isset($this->file)) {
            $this->file = request()->file('file');
            if (!$this->file) {
                throw new \Exception('请传入正确文件参数.');
            }
        }
        return $this->file;
    }

    public function setFileName($fileName)
    {
        $this->fileName = $fileName;
    }

    /**
     * 文件名
     * @return string
     * @throws \Exception
     */
    private function getFileName()
    {
        if (!$this->fileName) {
            $ext = $this->getFile()->getClientOriginalExtension() ? : 'jpg'; //文件后缀
            $this->fileName = md5($this->getFile()->getClientOriginalName() . date('Y-m-d') . str_random(6)) . '.' . $ext;
        }
        return $this->fileName;
    }

    public function setUploadPath($uploadPath)
    {
        $this->uploadPath = $uploadPath;
    }

    private function getUploadPath()
    {
        if (!isset($this->uploadPath)) {
            $this->uploadPath = '';
        }
        return $this->uploadPath;
    }

    private function getBasePath()
    {
        if (!isset($this->basePath)) {
            if (config('app.framework') == 'platform') {
                $this->basePath = base_path('static/upload');
            } else  {
                $this->basePath = dirname(dirname(base_path())).'/attachment';
            }
        }
        return $this->basePath;
    }

    private function getRemote()
    {
        if (!isset($this->remote)) {
            if (config('app.framework') == 'platform') {
                $this->remote = SystemSetting::settingLoad('remote', 'system_remote');
            } else {
                //全局配置
                global $_W;

                //公众号独立配置信息 优先使用公众号独立配置
                $uni_setting = app('WqUniSetting')->get()->toArray();
                if (!empty($uni_setting['remote']) && iunserializer($uni_setting['remote'])['type'] != 0) {
                    $setting['remote'] = iunserializer($uni_setting['remote']);
                    $this->remote = $setting['remote'];
                } else {
                    $this->remote = $_W['setting']['remote'];
                }
            }
        }
        return $this->remote;
    }

    /**
     * 文件验证
     * @throws \Exception
     */
    private function verifyFile()
    {
        if (!$this->getFile()->isValid()) {
            throw new \Exception('文件上传失败');
        }
        if ($this->extLimit && is_array($this->extLimit) && !in_array($this->getFile()->getClientOriginalExtension(),$this->extLimit)) {
            throw new \Exception('文件不符合类型');
        }
        if ($this->getFile()->getSize() > $this->maxSize) {
            throw new \Exception('文件上传超过大小限制'.($this->maxSize/1024/1024).'M');
        }
    }

    /**
     * 上传文件
     * @throws \Exception
     */
    public function upload()
    {
        $this->verifyFile();
        // 获取文件相关信息
        $realPath = $this->getFile()->getRealPath();                //临时文件的绝对路径
        Utils::mkdirs($this->getBasePath() . '/' . $this->getUploadPath());
        $result = file_put_contents($this->getBasePath() . '/' . $this->getUploadPath() . '/' . $this->getFileName(),file_get_contents($realPath));
        if (!$result){
            throw new \Exception('上传失败');
        }
        if ($this->getRemote()['type'] != 0) {//远程附件
            if (config('app.framework') == 'platform') {
                $res = file_remote_upload($this->getUploadPath() . '/' . $this->getFileName(), true, $this->getRemote());
            } else  {
                $res = file_remote_upload_wq($this->getUploadPath() . '/' . $this->getFileName(), true, $this->getRemote());
            }
            if ($res && in_array($res['errno'],[1,-1])) {
                throw new \Exception('上传失败:'.$res['message']);
            }
        }
        return $this->getFileUrl();
    }

    /**
     * @return bool|string
     * @throws \Exception
     */
    private function getFileUrl()
    {
        return $this->getUploadPath() . '/' . $this->getFileName();
    }
}