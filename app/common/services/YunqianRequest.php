<?php
/**
 * Created by PhpStorm.
 * User: yunzhong
 * Date: 2021/3/1
 * Time: 14:23
 */

namespace app\common\services;


class YunqianRequest
{

    private  $pa;
    private  $reqURL;
    private $app_id;
    private  $app_secret;

    public function __construct($pa,$reqURL,$app_id,$app_secret)
    {
        $this->pa = $pa;
        $this->reqURL = $reqURL;
        $this->app_id = $app_id;
        $this->app_secret = $app_secret;
    }

    public function getResult()
    {
        $array = $this->http_post_data($this->pa, $this->generateHeader(),$this->reqURL);
        $json_string = $array[1];
        $res = json_decode($json_string,true);
        return $res;
    }

    private function generateHeader(){
        $millisecond = $this->getMillisecond();//为服务端时间前后5分钟内可以访问
        $nonceStr = $this->getNonceStr(16);//10到50位字符串
        $header=[
            'appId:'.$this->app_id,
            'signature:'.$this->getSignature($this->app_id,$this->app_secret,$nonceStr,$millisecond),
            'nonceStr:'.$nonceStr,
            'timestamp:'.$millisecond,
            'Content-Type:application/json',
        ];
        return $header;
    }
    //计算请求签名值
    private function getSignature($appId, $appSecret,$nonceStr,$millisecond)
    {
        $s = $appId.'_'.$appSecret.'_'.$nonceStr.'_'.$millisecond;
        $signature = strtoupper(md5($s));

        return $signature;
    }

    //模拟发送POST请求
    /**
     * 模拟发送POST 方式请求
     * @param $url
     * @param $data
     * @param $projectId
     * @param $signature
     * @return array
     */
    private function http_post_data( $data, $header,$url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_HTTPHEADER,$header);
        ob_start();
        curl_exec($ch);
        $return_content = ob_get_contents();
        ob_end_clean();
        $return_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        //echo curl_errno($ch);
        return array($return_code, $return_content);
    }
    public function curl_post($postdata = '', $header = '', $url,$options = array())
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_HTTPHEADER,$header);
        if (!empty($options)) {
            curl_setopt_array($ch, $options);
        }
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }
    /**
     *
     * 产生随机字符串，不长于50位
     * @param int $length
     * @return 产生的随机字符串
     */
    private function getNonceStr($length = 50)
    {
        $chars = "abcdefghijklmnopqrstuvwxyz0123456789";
        $str ="";
        for ( $i = 0; $i < $length; $i++ )  {
            $str .= substr($chars, mt_rand(0, strlen($chars)-1), 1);
        }
        return $str;
    }
    //获取当前时间戳（毫秒级）
    private function getMillisecond()
    {
        list($s1, $s2) = explode(' ', microtime());

        return (float)sprintf('%.0f', (floatval($s1) + floatval($s2)) * 1000);
    }
}