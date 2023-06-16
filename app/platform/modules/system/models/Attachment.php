<?php
/**
 * Created by PhpStorm.
 * User: liuyifan
 * Date: 2019/3/21
 * Time: 16:44
 */

namespace app\platform\modules\system\models;


use app\common\models\BaseModel;
use app\common\services\qcloud\Conf;

class Attachment extends BaseModel
{
    /**
     * 保存全局设置的数据
     *
     * @param $set_data
     * @param $post_max_size
     * @return array
     */
    public static function saveGlobal($set_data, $post_max_size)
    {
        $set_data['thumb_width'] = intval(trim($set_data['thumb_width']));
        if ($set_data['thumb'] && !$set_data['thumb_width']) {
            return ['msg' => '请设置图片缩略宽度'];
        }
        $set_data['image_extentions'] =  self::check_ext($set_data['image_extentions']);
        if (!$set_data['image_extentions']['0']) {
            return ['msg' => '请添加支持的图片附件后缀类型'];
        }
        $set_data['image_limit'] = max(0, min(intval(trim($set_data['image_limit'])), $post_max_size));
        $zip_percentage = intval($set_data['zip_percentage']);
        if ($zip_percentage <= 0 || $zip_percentage > 100) {
            $set_data['image']['zip_percentage'] = 100;
        }
        $set_data['audio_extentions'] =  self::check_ext($set_data['audio_extentions']);
        if (!$set_data['audio_extentions']['0']) {
            return ['msg' => '请添加支持的音频视频附件后缀类型'];
        }
        $set_data['audio_limit'] = max(0, min(intval(trim($set_data['audio_limit'])), $post_max_size));

        return SystemSetting::settingSave($set_data, 'global', 'system_global') ? ['result' => 1] : ['msg' => '失败'];
    }

    /**
     * 检测后缀名是否合适
     *
     * @param $extentions
     * @return array
     */
    public static function check_ext($extentions)
    {
        $harmtype = array('asp', 'php', 'jsp', 'js', 'css', 'php3', 'php4', 'php5', 'ashx', 'aspx', 'exe', 'cgi');
        $extentions = explode("\n", $extentions);
        foreach ($extentions as $key => &$row) {
            $row = trim($row);
            if (in_array($row, $harmtype)) {
                unset($extentions[$key]);
                continue;
            }
        }

        return $extentions;
    }

    /**
     * 保存远程设置的数据
     *
     * @param $alioss
     * @param $cos
     * @param $remote
     * @return array
     */
    public static function saveRemote($alioss, $cos, $obs, $remote)
    {
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
        $remotes = array(
            'type' => intval(request()->type),
            'alioss' => array(
                'key' => $alioss['key'],
                'secret' => !(strpos($alioss['secret'], '*') === FALSE) ? $remote['alioss']['secret'] : $alioss['secret'],
                'bucket' => $alioss['bucket'],
                'loca_name' => $alioss['loca_name'],
                'internal' => $alioss['internal']
            ),
            'cos' => array(
                'appid' => trim($cos['appid']),
                'secretid' => trim($cos['secretid']),
                'secretkey' => !(strpos(trim($cos['secretkey']), '*') === FALSE) ? $remote['cos']['secretkey'] : trim($cos['secretkey']),
                'bucket' => trim($cos['bucket']),
                'local' => trim($cos['local']),
                'url' => trim($cos['url'])
            ),
            'obs' => array(
                'key' => trim($obs['key']),
                'secret' => !(strpos(trim($obs['secret']), '*') === FALSE) ? $remote['obs']['secret'] : trim($obs['secret']),
                'endpoint' => trim($obs['endpoint']),
                'bucket' => trim($obs['bucket']),
                'url' => trim($obs['url'])
            )
        );
        if ($remotes['type'] == '2') {
            $buckets = attachment_alioss_buctkets($remotes['alioss']['key'], $remotes['alioss']['secret']);
            if (is_error($buckets)) {
                return ['msg' => $buckets['message']];
            }

            list($remotes['alioss']['bucket'], $remotes['alioss']['url']) = explode('@@', $alioss['bucket']);
            if (!$buckets[$remotes['alioss']['bucket']]) {
                return ['msg' => $buckets['Bucket不存在或是已经被删除']];
            }

            $remotes['alioss']['url'] = 'http://' . $remotes['alioss']['bucket'] . '.' . $buckets[$remotes['alioss']['bucket']]['location'] . '.aliyuncs.com';
            $remotes['alioss']['ossurl'] = $buckets[$remotes['alioss']['bucket']]['location'] . '.aliyuncs.com';
            if ($alioss['url']) {
                $url = trim($alioss['url'], '/');
                if (!strexists($url, 'http://') && !strexists($url, 'https://')) {
                    $url = 'http://' . $url;
                }
                $remotes['alioss']['url'] = $url;
            }
            $remotes['alioss']['bucket'] = $alioss['bucket'];
        } elseif ($remotes['type'] == '4') {
            $remotes['cos']['bucket'] = str_replace("-{$remotes['cos']['appid']}", '', trim($remotes['cos']['bucket']));

            if (!$remotes['cos']['url']) {
                $remotes['cos']['url'] = sprintf('https://%s-%s.cos.%s.myqcloud.com', $remotes['cos']['bucket'], $remotes['cos']['appid'], $remotes['cos']['local']);
            }
            $remotes['cos']['url'] = rtrim($remotes['cos']['url'], '/');

            Conf::$appid = $remotes['cos']['appid'];
            Conf::$secretid = $remotes['cos']['secretid'];
            Conf::$tsecretkey = $remotes['cos']['secretkey'];

            $auth = attachment_cos_auth($remotes['cos']['bucket'], $remotes['cos']['appid'], $remotes['cos']['secretid'], $remotes['cos']['secretkey'], $remotes['cos']['local']);

            if (is_error($auth)) {
                return ['msg' => $auth['message']];
            }
        } elseif ($remotes['type'] == '5') {
            $remotes['obs']['bucket'] = trim($remotes['obs']['bucket']);
            if (!$remotes['obs']['url']) {
                $remotes['obs']['url'] = sprintf('https://%s.%s', $remotes['obs']['bucket'], $remotes['obs']['endpoint']);
            }
            $remotes['obs']['url'] = rtrim($remotes['obs']['url'], '/');
            $auth = attachment_obs_auth($remotes['obs']['key'], $remotes['obs']['secret'], $remotes['obs']['endpoint'], $remotes['obs']['bucket']);
            if (is_error($auth)) {
                return ['msg' => $auth['message']];
            }
        }
        return SystemSetting::settingSave($remotes, 'remote', 'system_remote') ? ['result' => 1] : ['msg' => '失败'];
    }
}