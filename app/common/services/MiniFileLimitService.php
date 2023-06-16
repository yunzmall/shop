<?php
/**
 * Created by PhpStorm.
 * User: weifeng
 * Date: 2020-05-22
 * Time: 18:27
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


use app\common\facades\Setting;

class MiniFileLimitService
{
    public function checkImg($file)
    {
        $url = $this->getImgSecCheckUrl();

        $obj = new \CURLFile($file->path(), $file->getMimeType(), $file->getClientOriginalName());

        $data = [
            'media' => $obj,
        ];

        $result = $this->curl_post($url, $data, $options = array());

        return json_decode($result, JSON_FORCE_OBJECT);
    }

    public function checkMedia($media_url, $type)
    {
        $url = $this->getMediaCheckAsyncUrl();

        $data = [
            'media_url' => $media_url,
            'media_type' => $type,
        ];

        $result = $this->curl_post($url, $data, $options = array());

        return json_decode($result, JSON_FORCE_OBJECT);
    }

    public function checkMsg($content)
    {
        $url = $this->getMsgSecCheckUrl();

        $data = '{ "content":" ' . $content . ' " }';

        $result = $this->curl_post($url, $data, $options = array());

        return json_decode($result, JSON_FORCE_OBJECT);
    }

    public function getToken()
    {
        $tokenUrl = $this->getTokenUrl();

        $res = self::curl_post($tokenUrl, $post_data = '', $options = array());
        $data = json_decode($res, JSON_FORCE_OBJECT);

        return $data['access_token'];
    }

    private function curl_post($url = '',$post_data = '',$options = array())
    {
        $ch = curl_init($url);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch,CURLOPT_POST,1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

        if(!empty($options)){
            curl_setopt_array($ch, $options);
        }

        $data = curl_exec($ch);
        curl_close($ch);

        return $data;
    }

    private function getImgSecCheckUrl()
    {
        return "https://api.weixin.qq.com/wxa/img_sec_check?access_token=" . $this->getToken();
    }

    private function getMediaCheckAsyncUrl()
    {
        return "https://api.weixin.qq.com/wxa/media_check_async?access_token=" . $this->getToken();
    }

    private function getMsgSecCheckUrl()
    {
        return "https://api.weixin.qq.com/wxa/msg_sec_check?access_token=" . $this->getToken();
    }

    private function getTokenUrl()
    {
        $set = Setting::get('plugin.min_app');
//        $set['key'] = 'wxbe88683bd339aaf5';
//        $set['secret'] = 'fcf189d2a18002a463e7b675cea86c87';

        return "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=" . $set['key'] . "&secret=" . $set['secret'];
    }
}