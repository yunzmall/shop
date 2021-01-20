<?php
/**
 * Created by PhpStorm.
 * User: weifeng
 * Date: 2020-05-11
 * Time: 16:18
 *
 *    .--,       .--,
 *   ( (  \.---./  ) )
 *    '.__/o   o\__.'
 *       {=  ^  =}
 *        >  -  <
 *       /       \
 *      //       \\
 *     //|   .   |\\
 *     "'\       /'"_.-~^`'-.
 *        \  _  /--'         `
 *      ___)( )(___
 *     (((__) (__)))     梦之所想,心之所向.
 */

namespace app\common\services;


use app\platform\modules\system\models\SystemSetting;

class QcloudCosService
{
    private $app_id;
    private $region;
    private $secretId;
    private $secretKey;
    private $bucket;
    private $cosClient;

    public function __construct($region = '', $secretId = '', $secretKey = '', $bucket = '', $appid = '')
    {
        $this->app_id = $appid;
        $this->region = $region; //地域
        $this->secretId = $secretId;
        $this->secretKey = $secretKey;
        $this->bucket = $bucket . '-' . $appid;

        $this->cosClient = CosV5Service::init($this->region, $this->secretId, $this->secretKey);
    }

    /**
     * 上传文件流 测试
     * @param $bucket
     * @param $key
     * @param $srcPath
     * @param $cosClient
     */
    public function uploadTest($key = '')
    {
        $file = request()->getSchemeAndHttpHost() . '/static/' . $key;

        try {
            if ($file) {
                $result = $this->cosClient->putObject(array(
                    'Bucket' => $this->bucket,
                    'Key' => $key,
                    'Body' => $this->curl_file($file),
                ));
                return true;
            } else {
                return '文件资源不存在';
            }
        } catch (\Exception $e) {
            \Log::error('qcloud-cos上传文件流报错', $e->getMessage());
            return $e->getMessage();
        }
    }

    /**
     * 上传文件流
     * @param $bucket
     * @param $key
     * @param $srcPath
     * @param $cosClient
     */
    public function upload($key = '')
    {
        if (config('app.framework') == 'platform') {
            if (strexists($key, '/static/upload')) {
                $file = request()->getSchemeAndHttpHost() . $key;
            } else {
                $file = request()->getSchemeAndHttpHost() . '/static/upload/' . $key;
            }
        } else {
            $file = request()->getSchemeAndHttpHost() . '/attachment/' . $key;
        }

        try {
            if ($file) {
                $result = $this->cosClient->putObject(array(
                    'Bucket' => $this->bucket,
                    'Key' => $key,
                    'Body' => $this->curl_file($file),
                ));
                return true;
            } else {
                return '文件资源不存在';
            }
        } catch (\Exception $e) {
            \Log::error('qcloud-cos上传文件流报错', $e->getMessage());
            return $e->getMessage();
        }
    }

    /**
     * 获取上传的url
     * @param $bucket
     * @param $key
     * @param $cosClient
     * @param $expire
     */
    public function getObjUrl($key = '')
    {
        $expire = 10;

        try {
            $signedUrl = $this->cosClient->getObjectUrl($this->bucket, $key, '+'.$expire.' minutes');
            // 请求成功
            return $signedUrl;
        } catch (\Exception $e) {
            // 请求失败
            \Log::error('qcloud-cos读取文件报错', $e);
            return $e->getMessage();
        }
    }

    public function curl_file($url)
    {
        $ch = curl_init();
        $timeout = 10;
        curl_setopt ($ch, CURLOPT_URL, $url);
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        $file_contents = curl_exec($ch);
        curl_close($ch);

        return $file_contents;
    }
}