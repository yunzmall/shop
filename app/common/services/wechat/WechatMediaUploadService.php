<?php
/**
 * Created by PhpStorm.
 * User: yunzhong
 * Date: 2020/1/9
 * Time: 15:47
 */

namespace app\common\services\wechat;


use app\common\services\wechat\lib\WxPayConfig;
use app\common\services\wechat\lib\WxPayException;
use GuzzleHttp\HandlerStack;
use WechatPay\GuzzleMiddleware\WechatPayMiddleware;
use WechatPay\GuzzleMiddleware\Util\PemUtil;

class WechatMediaUploadService
{
    /**
     * @param $config
     * @param $file
     * @param $fileName
     * @return mixed
     * @throws WxPayException
     */
    public function postCurlV3($config, $file, $fileName, $second = 30)
    {
        $url ='https://api.mch.weixin.qq.com/v3/merchant/media/upload';

        $boundary ='yunzmall';//分割符号
        $sign = hash('sha256',$file);

        $strdata = [
            'filename' => $fileName,
            'sha256' => $sign,
        ];
        $filestr = json_encode($strdata);

        $body = $this->getBody($filestr, $file, $fileName, $boundary);
        $header = $this->getHeader($config, $url, $filestr, $boundary);

        $ch = curl_init();

        //设置超时
        curl_setopt($ch, CURLOPT_TIMEOUT, $second);
        curl_setopt($ch,CURLOPT_URL, $url);
        if(stripos($url,"https://")!==FALSE){
            curl_setopt($ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        } else{
            curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,TRUE);
            curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,2);//严格校验
        }
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        //要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

        //post提交方式
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        //运行curl
        $data = curl_exec($ch);
        //返回结果
        if($data){
            curl_close($ch);
            return json_decode($data, true);
        } else {
            $error = curl_errno($ch);
            curl_close($ch);
            throw new WxPayException("curl出错，错误码:$error");
        }
    }

    public function getHeader($config, $url, $filestr, $boundary)
    {
        $method ='POST';
        $http_method =$method;
        $timestamp = time();
        $nonce = $this->getNonceStr();
        $mch_private_key = PemUtil::loadPrivateKey($config->getKeyPath());
        $merchant_id = $config->GetMerchantId();
        $serial_no = '19D4A2191AE6688A8213E248C35457A3E4E85A17';

        $url_parts = parse_url($url);
        $canonical_url = ($url_parts['path'] . (!empty($url_parts['query']) ?"?${url_parts['query']}" :""));
        $message =$http_method."\n".
            $canonical_url."\n".
            $timestamp."\n".
            $nonce."\n".
            $filestr.
            "\n";
        openssl_sign($message,$raw_sign,$mch_private_key,'sha256WithRSAEncryption');
        $sign =base64_encode($raw_sign);

        $schema ='WECHATPAY2-SHA256-RSA2048';
        $token = sprintf('mchid="%s",nonce_str="%s",timestamp="%d",serial_no="%s",signature="%s"',
            $merchant_id,$nonce,$timestamp,$serial_no,$sign);

        $header = [
            'Authorization: '.$schema.' '.$token,
            'Accept: application/json',
            "Content-Type: multipart/form-data;boundary=".$boundary,
            'User-Agent: Fangbc',
        ];
        return $header;
    }

    /**
     * @param $filestr
     * @param $file
     * @param $fileName
     * @param $boundary
     * @return string
     */
    public function getBody($filestr, $file, $fileName, $boundary)
    {

        $out = "--{$boundary}\r\n";
        $out .= 'Content-Disposition: form-data; name="meta"'."\r\n";
        $out .= 'Content-Type: application/json; charset=UTF-8'."\r\n";
        $out .= "\r\n";
        $out .= "".$filestr."\r\n";
        $out .= "--{$boundary}\r\n";
        $out .= 'Content-Disposition: form-data; name="file"; filename="'.$fileName.'"'."\n";
        $out .= 'Content-Type: image/jpeg;'."\r\n";
        $out .= "\r\n";
        $out .= $file."\r\n";
        $out .= "--{$boundary}--\r\n";
        return $out;
    }

    /**
     *
     * 产生随机字符串，不长于32位
     * @param int $length
     * @return string
     */
    public static function getNonceStr($length = 32)
    {
        $chars = "abcdefghijklmnopqrstuvwxyz0123456789";
        $str ="";
        for ( $i = 0; $i < $length; $i++ )  {
            $str .= substr($chars, mt_rand(0, strlen($chars)-1), 1);
        }
        return $str;
    }

}