<?php
/**
 * Created by PhpStorm.
 *
 * 
 *
 * Date: 2022/1/7
 * Time: 11:55
 */

namespace app\frontend\modules\wechat\services;

use app\common\models\AccountWechats;
use app\platform\modules\system\models\SystemSetting;

class MediaService
{
    private $mediaId;
    private $plugin;
    private $uploadPath;
    private $remote;

    public function __set($name,$value)
    {
        $this->$name = $value;
    }

    private function easyWechat()
    {
        return \app\common\facades\EasyWeChat::officialAccount($this->wechatConfig());
    }

    /**
     * 获取公众号的 appID 和 appsecret
     * @return array
     */
    private function wechatConfig()
    {
        $account = AccountWechats::getAccountByUniacid(\YunShop::app()->uniacid);
        $options = [
            'app_id'  => $account->key,
            'secret'  => $account->secret,
        ];
        return $options;
    }

    /**
     * 获取临时素材并上传本地
     * @return array
     */
    public function getTemporaryFile()
    {
        try {
            if (!$this->mediaId) {
                throw new \Exception('媒体ID参数错误');
            }
            $filename = md5($this->mediaId) . '_' . \YunShop::app()->uniacid;
            $stream = $this->easyWechat()->media->get($this->mediaId);
            if (!$stream instanceof \EasyWeChat\Kernel\Http\StreamResponse) {
                throw new \Exception('资源错误');
            }
            $filename = $stream->saveAs($this->uploadPath(), $filename);
            $relative_path = $this->plugin.'/'.$filename;
            $this->remoteUpload($relative_path);
            return ['code' => 0 ,'file' => $this->filePath($relative_path)];
        } catch (\GuzzleHttp\Exception\GuzzleException $e) {
            return ['code' => 1 ,'message' => $e->getMessage()];
        } catch (\Exception $e) {
            return ['code' => 1 ,'message' => $e->getMessage()];
        }
    }

    /**
     * 获取 JSSDK 上传的高清语音
     * @return array
     */
    public function getJsSdkMedia()
    {
        try {
            if (!$this->mediaId) {
                throw new \Exception('媒体ID参数错误');
            }
            $filename = md5($this->mediaId) . '_' . \YunShop::app()->uniacid.'.speex';
            $stream = $this->easyWechat()->media->getJssdkMedia($this->mediaId);
            if (!$stream instanceof \EasyWeChat\Kernel\Http\StreamResponse) {
                throw new \Exception('资源错误');
            }
            $filename = $stream->saveAs($this->uploadPath(), $filename);
            $relative_path = $this->plugin.'/'.$filename;
            $this->remoteUpload($relative_path);
            return ['code' => 0 ,'file' => $this->filePath($relative_path)];
        } catch (\GuzzleHttp\Exception\GuzzleException $e) {
            return ['code' => 1 ,'message' => $e->getMessage()];
        } catch (\Exception $e) {
            return ['code' => 1 ,'message' => $e->getMessage()];
        }
    }

    private function uploadPath()
    {
        $this->plugin = $this->plugin?str_replace('-','_',$this->plugin):'shop';
        if (!isset($this->uploadPath)) {
            if (config('app.framework') == 'platform') {
                $this->uploadPath = base_path('static/upload/'.$this->plugin);
            } else  {
                $this->uploadPath = dirname(dirname(base_path())).'/attachment/'.$this->plugin;
            }
        }
        return $this->uploadPath;
    }

    private function filePath($filename)
    {
        if ($this->remote['type']) {
            switch ($this->remote['type']) {
                case 1 :
                    $attach_url_remote = $this->remote['ftp']['url'];
                    break;
                case 2 :
                    $attach_url_remote = $this->remote['alioss']['url'];
                    break;
                case 3 :
                    $attach_url_remote = $this->remote['qiniu']['url'];
                    break;
                case 4 :
                    $attach_url_remote = $this->remote['cos']['url'];
                    break;
            }
            $file_url = $attach_url_remote . DIRECTORY_SEPARATOR . $filename;
        } else {
            if (config('app.framework') == 'platform') {
                $file_url = request()->getSchemeAndHttpHost() . DIRECTORY_SEPARATOR . 'static/upload/' . $filename;
            } else {
                $file_url = request()->getSchemeAndHttpHost() . DIRECTORY_SEPARATOR . 'attachment/' . $filename;
            }
        }
        return [
            'file' => $filename,
            'file_url' => $file_url,
        ];
    }

    private function remoteUpload($filename)
    {
        if ($this->getRemote()['type']) {
            if (config('app.framework') == 'platform') {
                file_remote_upload($filename, true, $this->getRemote());
            } else  {
                file_remote_upload_wq($filename, true, $this->getRemote());
            }
        }
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
}