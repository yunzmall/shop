<?php
/**
 * Created by PhpStorm.
 * User: liuyifan
 * Date: 2019/2/28
 * Time: 10:06
 */

namespace app\platform\modules\system\controllers;


use app\common\helpers\Cache;
use app\common\services\qcloud\Conf;
use app\platform\controllers\BaseController;
use app\platform\modules\system\models\Attachment;
use app\platform\modules\system\models\SystemSetting;

class AttachmentController extends BaseController
{
    public $remote;

    public function __construct()
    {
        $this->remote = SystemSetting::settingLoad('remote', 'system_remote');
    }

    /**
     * 保存及显示全局设置
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \app\common\exceptions\AppException
     */
    public function globals()
    {
        $post_max_size = ini_get('post_max_size');
        $post_max_size = $post_max_size > 0 ? bytecount($post_max_size) / 1024 : 0;
        $upload_max_filesize = ini_get('upload_max_filesize');
        $global = SystemSetting::settingLoad('global', 'system_global');
        $set_data = request()->upload;

        if ($set_data) {
            $validate = $this->validate($this->rules(''), $set_data, $this->message());
            if ($validate) {
                return $validate;
            }
            $attach = Attachment::saveGlobal($set_data, $post_max_size);

            if ($attach['result']) {
                return $this->successJson('成功');
            } else {
                return $this->errorJson($attach['msg']);
            }
        }

        $global['thumb_width'] = intval($global['thumb_width']);

        if ($global['image_extentions']['0']) {
            $global['image_extentions'] = implode("\n", $global['image_extentions']);
        }

        if ($global['audio_extentions']['0']) {
            $global['audio_extentions'] = implode("\n", $global['audio_extentions']);
        }

        return $this->successJson('成功', [
            'global' => $global,
            'post_max_size' => $post_max_size,
            'upload_max_filesize' => $upload_max_filesize
        ]);
    }

    /**
     * 保存及显示远程设置
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \app\common\exceptions\AppException
     */
    public function remote()
    {
        $alioss = request()->alioss;
        $cos = request()->cos;
        $obs = request()->obs;
        if ($alioss || $cos || $obs) {
            $validate = false;
            if ($alioss['key']) {
                $validate  = $this->validate($this->rules(1), $alioss, $this->message());
            } elseif($cos['key']) {
                $validate  = $this->validate($this->rules(2), $cos, $this->message());
            } elseif ($obs['key']) {
                $validate  = $this->validate($this->rules(3), $obs, $this->message());
            }
            if ($validate) {
                return $validate;
            }
            $attach = Attachment::saveRemote($alioss, $cos, $obs, $this->remote);
            if ($attach['result']) {
                Cache::flush();
                return $this->successJson('成功');
            } else {
                return $this->errorJson($attach['msg']);
            }
        }
        $this->remote['alioss']['internal'] ? $this->remote['alioss']['internal'] = intval($this->remote['alioss']['internal']) : null;
        switch($this->remote['cos']['local']) {
            case 'ap-nanjing':
                $this->remote['cos']['local'] = '南京';
                break;
            case 'ap-guangzhou':
                $this->remote['cos']['local'] = '广州';
                break;
            case 'ap-chengdu':
                $this->remote['cos']['local'] = '成都';
                break;
            case 'ap-beijing':
                $this->remote['cos']['local'] = '北京';
                break;
            case 'ap-chongqing':
                $this->remote['cos']['local'] = '重庆';
                break;
            case 'ap-shanghai':
                $this->remote['cos']['local'] = '上海';
                break;
            case 'ap-hongkong':
                $this->remote['cos']['local'] = '香港';
                break;
            case 'ap-beijing-fsi':
                $this->remote['cos']['local'] = '北京金融';
                break;
            case 'ap-shanghai-fsi':
                $this->remote['cos']['local'] = '上海金融';
                break;
            case 'ap-shenzhen-fsi':
                $this->remote['cos']['local'] = '深圳金融';
                break;
        }

        unset($this->remote['alioss']['secret']);
        unset($this->remote['cos']['secretkey']);
        unset($this->remote['obs']['secret']);

        return $this->successJson('成功', $this->remote);
    }

    /**
     * 验证数据
     *
     * @param array $rules
     * @param \Request|null $request
     * @param array $messages
     * @param array $customAttributes
     * @return \Illuminate\Http\JsonResponse
     */
    public function validate(array $rules, $request = null, array $messages = [], array $customAttributes = [])
    {
        if (!isset($request)) {
            $request = request();
        }
        $validator = $this->getValidationFactory()->make($request, $rules, $messages, $customAttributes);

        if ($validator->fails()) {
            return $this->errorJson('失败', $validator->errors()->all());
        }
    }

    /**
     * 配置验证规则
     *
     * @param $param
     * @return array
     */
    public function rules($param)
    {
        $rules = [];
        if (request()->path() == "admin/system/globals") {
            $rules = [
                'image_extentions' => 'required',
                'image_limit' => 'required',
                'audio_extentions' => 'required',
                'audio_limit' => 'required',
            ];
        }
        if ($param == 1) {
            $rules = [
                'key' => 'required',
                'secret' => 'required',
            ];
        } elseif ($param == 2) {
            $rules = [
                'appid' => 'required',
                'secretid' => 'required',
                'secretkey' => 'required',
                'bucket' => 'required',
            ];
        } elseif ($param == 3) {
            $rules = [
                'key' => 'required',
                'secret' => 'required',
                'endpoint' => 'required',
                'bucket' => 'required',
            ];
        }
        return $rules;
    }

    /**
     * 自定义错误信息
     *
     * @return array
     */
    public function message()
    {
        return [
            'image_extentions.required' => '图片后缀不能为空.',
            'image_limit.required' => '图片上传大小不能为空.',
            'audio_extentions.required' => '音频视频后缀不能为空.',
            'audio_limit.required' => '音频视频大小不能为空.',
            'key' => '阿里云OSS-Access Key ID不能为空',
            'secret' => '阿里云OSS-Access Key Secret不能为空',
            'appid' => '请填写APPID',
            'secretid' => '请填写SECRETID',
            'secretkey' => '请填写SECRETKEY',
            'bucket' => '请填写BUCKET',
            'endpoint' => '请填写Endpoint',
        ];
    }

    /**
     * 阿里云搜索 bucket
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function bucket()
    {
        $key = request()->key;
        $secret = request()->secret;
        $is_auth = request()->is_auth;//判断是否加载完请求
        if ($is_auth) {
            $secret = $this->remote['alioss']['secret'];
        }
        $buckets = attachment_alioss_buctkets($key, $secret);
        if (is_error($buckets)) {
            return $this->errorJson($buckets['message']);
        }
        $bucket_datacenter = attachment_alioss_datacenters();
        $bucket = array();
        foreach ($buckets as $key => $value) {
            $value['loca_name'] = $key. '@@'. $bucket_datacenter[$value['location']];
            $value['value'] = $key. '@@'. $value['location'];
            $bucket[] = $value;
        }
        return $this->successJson('成功', $bucket);
    }

    /**
     * 测试阿里云配置是否成功
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function oss()
    {
        $alioss = request()->alioss;

        $secret = strexists($alioss['secret'], '*') ? $this->remote['alioss']['secret'] : $alioss['secret'];
        $buckets = attachment_alioss_buctkets($alioss['key'], $secret);
        list($bucket, $url) = explode('@@', $alioss['bucket']);

        $result = attachment_newalioss_auth($alioss['key'], $secret, $bucket, $alioss['internal']);
        if (is_error($result)) {
            return $this->errorJson('OSS-Access Key ID 或 OSS-Access Key Secret错误，请重新填写');
        }
        $ossurl = $buckets[$bucket]['location'].'.aliyuncs.com';
        if ($alioss['url']) {
            if (!strexists($alioss['url'], 'http://') && !strexists($alioss['url'],'https://')) {
                $url = 'http://'. trim($alioss['url']);
            } else {
                $url = trim($alioss['url']);
            }
            $url = trim($url, '/').'/';
        } else {
            $url = 'http://'.$bucket.'.'.$buckets[$bucket]['location'].'.aliyuncs.com/';
        }
        $filename = 'logo.png';
        $response = \Curl::to($url. '/'.$filename)->get();
        if (!$response) {
            return $this->errorJson('配置失败，阿里云访问url错误');
        }
        $image = getimagesizefromstring($response);
        if ($image && strexists($image['mime'], 'image')) {
            return $this->successJson('配置成功', request()->alioss);
        } else {
            return $this->errorJson('配置失败，阿里云访问url错误');
        }
    }

    /**
     * 测试腾讯云配置是否成功
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function cos()
    {
        $cos = request()->cos;
        switch($cos['local']) {
            case '南京':
                $cos['local'] = 'ap-nanjing';
                break;
            case '成都':
                $cos['local'] = 'ap-chengdu';
                break;
            case '北京':
                $cos['local'] = 'ap-beijing';
                break;
            case '广州':
                $cos['local'] = 'ap-guangzhou';
                break;
            case '上海':
                $cos['local'] = 'ap-shanghai';
                break;
            case '重庆':
                $cos['local'] = 'ap-chongqing';
                break;
            case '北京金融':
                $cos['local'] = 'ap-beijing-fsi';
                break;
            case '上海金融':
                $cos['local'] = 'ap-shanghai-fsi';
                break;
            case '深圳金融':
                $cos['local'] = 'ap-shenzhen-fsi';
                break;
            case '香港':
                $cos['local'] = 'ap-hongkong';
                break;
        }

        $secretkey = strexists($cos['secretkey'], '*') ? $this->remote['cos']['secretkey'] : trim($cos['secretkey']);
        $bucket =  str_replace("-{$cos['appid']}", '', trim($cos['bucket']));

        if (!$cos['url']) {
            $cos['url'] = sprintf('https://%s-%s.cos.%s.myqcloud.com', $bucket, $cos['appid'], $cos['local']);
        }
        $cos['url'] = rtrim($cos['url'], '/');
        Conf::$appid = $cos['appid'];
        Conf::$secretid = $cos['secretid'];
        Conf::$tsecretkey = $cos['secretkey'];
        $auth = attachment_cos_auth($bucket, $cos['appid'], $cos['secretid'], $secretkey, $cos['local']);

        if (is_error($auth)) {
            return $this->errorJson('配置失败，请检查配置' . $auth['message']);
        }

        $filename = 'logo.png';
        $response = \Curl::to($cos['url']. '/'. $filename)->get();
        if (!$response) {
            return $this->errorJson('配置失败，腾讯cos访问url错误');
        }

        $image = getimagesizefromstring($response);
        if ($image && strexists($image['mime'], 'image')) {
            return $this->successJson('配置成功', request()->cos);
        } else {
            return $this->errorJson('配置失败，腾讯cos访问url错误');
        }
    }

    /**
     * 测试腾讯云配置是否成功
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function obs()
    {
        $obs = request()->obs;
        $key = trim($obs['key']);
        $secret = strexists($obs['secret'], '*') ? $this->remote['cos']['secret'] : trim($obs['secret']);
        $bucket = trim($obs['bucket']);
        $endpoint = trim($obs['endpoint']);
        if (!$obs['url']) {
            $obs['url'] = sprintf('https://%s.%s', $obs['bucket'], $obs['endpoint']);
        }
        $obs['url'] = rtrim($obs['url'], '/');
        $auth = attachment_obs_auth($key, $secret, $endpoint, $bucket);
        if (is_error($auth)) {
            return $this->errorJson('配置失败，请检查配置' . $auth['message']);
        }
        $filename = '/static/logo.png';
        $response = \Curl::to($obs['url']. '/'. $filename)->get();
        if (!$response) {
            return $this->errorJson('配置失败，华为云obs访问url错误');
        }
        $image = getimagesizefromstring($response);
        if ($image && strexists($image['mime'], 'image')) {
            return $this->successJson('配置成功', request()->obs);
        } else {
            return $this->errorJson('配置失败，华为云obs访问url错误');
        }
    }

    public function sms()
    {
        $type = request()->type;

        if (request()->input()) {
            
            $data = request()->input();

            if ($data) {
                
                $res = SystemSetting::settingSave($data, 'sms', 'system_sms');

                if ($res) {
                    Cache::flush();//清除缓存
                    return $this->successJson('短信设置成功');
                } else {
                    return $this->errorJson('短信设置失败');
                }
            }
        }
    }
}