<?php
/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 2023-02-03
 * Time: 11:20
 */

namespace app\common\services;


class ObsService
{
    private $key;
    private $secret;
    private $endpoint;
    private $bucket;
    private $obsClient;

    public function __construct($key = '', $secret = '', $endpoint = '', $bucket = '')
    {
        $this->key = $key;
        $this->secret = $secret;
        $this->endpoint = $endpoint;
        $this->bucket = $bucket;
        $this->obsClient = \Obs\ObsClient::factory([
            'key' => $key,
            'secret' => $secret,
            'endpoint' => $endpoint,
        ]);
    }

    public function upload($file_name = '', $test = false)
    {
        if (!$test) {
            if (strexists($file_name, '/static/upload')) {
                $file = request()->getSchemeAndHttpHost() . $file_name;
            } else {
                $file = request()->getSchemeAndHttpHost() . '/static/upload/' . $file_name;
            }
        } else {
            $file = request()->getSchemeAndHttpHost() . $file_name;
        }
        try {
            $this->obsClient->putObject(array (
                'Bucket' => $this->bucket,
                'Key' => $file_name,
                'Body' => file_get_contents($file),
            ));
        } catch (\app\common\exceptions\ShopException $exception) {
            \Log::error('华为云obs上传报错', $exception->getMessage());
            return false;
        }
        return true;
    }

    public function delete($file_name = '')
    {
        try {
            $this->obsClient->deleteObject(array (
                'Bucket' => $this->bucket,
                'Key' => $file_name,
            ));
        } catch (\app\common\exceptions\ShopException $exception) {
            \Log::error('华为云obs删除报错', $exception->getMessage());
            return false;
        }
        return true;
    }
}