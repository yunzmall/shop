<?php

namespace app\common\services\wechatApiV3;

use app\common\services\Utils;
use Ixudra\Curl\Facades\Curl;

class ApiV3Request
{
    /**
     * @var ApiV3Config
     */
    public $config;

    private $url;

    private $method;

    private $timestamp;

    private $params;

    private $nonceStr;

    private $certificationType = 'WECHATPAY2-SHA256-RSA2048';

    public $accept_language; //应答语种

    private $encrypt; //含有加密数据

    public function __construct(ApiV3Config $config)
    {
        $this->config = $config;
    }

    /**
     * @return string[]
     * @throws \Exception
     */
    private function header():array
    {
        $headers = [
            'Accept-Language' => $this->accept_language,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'User-Agent' => $_SERVER['HTTP_USER_AGENT'] ? : 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/101.0.0.0 Safari/537.36',
        ];
        if ($this->encrypt) {//加密数据需传递证书序列号
            $headers['Wechatpay-Serial'] = $this->config->platformSerialNo();
        }
        return $headers;
    }

    private function authorization()
    {
        $token = sprintf('mchid="%s",nonce_str="%s",timestamp="%d",serial_no="%s",signature="%s"',
            $this->config->mchId(),
            $this->nonceStr,
            $this->timestamp,
            $this->config->apiSerialNo(),
            $this->signature($this->method,$this->canonicalUrl(),$this->timestamp,$this->nonceStr,$this->getBody())
        );
        return $this->certificationType . ' ' . $token;
    }

    private function signature($method,$canonicalUrl,$timestamp,$nonce,$body)
    {
        $message = $method."\n".
            $canonicalUrl."\n".
            $timestamp."\n".
            $nonce."\n".
            $body."\n";;

        openssl_sign($message, $raw_sign, $this->config->privateKey(), 'sha256WithRSAEncryption');
        $sign = base64_encode($raw_sign);
        return $sign;
    }

    private function canonicalUrl()
    {
        $url_parts = parse_url($this->url);
        return $url_parts['path'] . (!empty($url_parts['query']) ? "?${url_parts['query']}" : "");
    }

    private function getBody()
    {
        return $this->params ? json_encode($this->params) : '';
    }

    private function createNonceStr($length = 32)
    {
        $str = '1234567890abcdefghijklmnopqrstuvwxyz';
        $result = '';
        for ($i = 1;$i <= $length;$i++) {
            $result .= substr($str,rand(0,(strlen($str)-1)),1);
        }
        $this->nonceStr = strtoupper($result);
    }

    /**
     * @param $url //请求url
     * @param $params //请求参数（有些GET请求参数需要在url拼上的请勿传到此参数，自行拼接到url）
     * @param $request_method //请求方式
     * @param $is_encrypt //是否有加密数据
     * @param $verify_sign //是否验签,验签可能存在获取平台证书错误，造成记录更新错误，所以自行根据接口重要程度判断要不要对返回数据进行验签
     * @param $accept_language //应答语言类型，默认中文
     * @return array
     * @throws \Exception
     */
    public function httpRequest($url,$params = [],$request_method = 'GET',$is_encrypt = false,$verify_sign = 0,$accept_language = 'zh-CN')
    {
        $this->url = $url;
        $this->params = $params;
        $this->method = $request_method;
        $this->encrypt = $is_encrypt;
        $this->accept_language = $accept_language;
        $this->timestamp = time();
        $this->createNonceStr();

        $method = "send" . ucfirst(strtolower($this->method));

        if (!method_exists($this,$method)) {
            throw new \Exception('method not found');
        }
        $res = $this->$method($this->url,$this->params,$this->authorization(),$this->header());
        $res_header = $res->headers;
        $res_content = $res->content;
        if (ApiV3Status::isCheckSign(($res_content['code']?:'')) && $verify_sign) {
            $this->verifySign($res_header['Wechatpay-Signature'],$res_header['Wechatpay-Timestamp'],$res_header['Wechatpay-Nonce'],$res_content,$res_header['Wechatpay-Serial']);
        }
        //处理统一返回体
        return [
            'request_id'    => $res_header['Request-ID'],               //唯一请求ID
            'http_code'     => $res->status,                            //请求状态码
            'code'          => ApiV3Status::returnCode($res->status),   //状态
            'message'       => $res_content['message'] ? : '',          //错误消息
            'data'          => $res_content                             //应答消息体
        ];
    }

    /**
     * @param $url
     * @param $params
     * @param $header
     * @return array|mixed|\stdClass
     */
    private function sendGet($url,$params,$authorization,$header)
    {
        return Curl::to($url)
            ->withHeaders($header)
            ->withAuthorization($authorization)
            ->withData($params)
            ->asJson(true)
            ->withResponseHeaders()
            ->returnResponseObject()
            ->get();
    }

    /**
     * @param $url
     * @param $params
     * @param $header
     * @return array|mixed|\stdClass
     */
    private function sendPost($url,$params,$authorization,$header)
    {
        return Curl::to($url)
            ->withHeaders($header)
            ->withAuthorization($authorization)
            ->withData($params)
            ->asJson(true)
            ->withResponseHeaders()
            ->returnResponseObject()
            ->post();
    }

    /**
     * @param $path
     * @param $file_name
     * @return bool
     * @throws \Exception
     */
    public function platformCertApply($path,$file_name)
    {
        $this->url = 'https://api.mch.weixin.qq.com/v3/certificates';
        $this->params = [];
        $this->method = 'GET';
        $this->timestamp = time();
        $this->createNonceStr();
        $res = $this->sendGet($this->url,[],$this->authorization(),$this->header());
        $res_content = $res->content;
        if (!ApiV3Status::returnCode($res->status)) {
            throw new \Exception($res_content['message'] ? : '平台证书请求错误！');
        }
        $content = $res_content['data'];
        $data = $this->config->encrypt()->decrypt(
            $content[0]['encrypt_certificate']['associated_data'],
            $content[0]['encrypt_certificate']['nonce'],
            $content[0]['encrypt_certificate']['ciphertext']);
        Utils::mkdirs($path);
        if (!$data || !file_put_contents($path . $file_name,$data)) {
            throw new \Exception('平台证书更新保存失败!');
        }
        return true;
    }

    /**
     * 回调及应答签名验证
     * @param $sign
     * @param $timestamp
     * @param $nonce
     * @param $body
     * @param $serial
     * @return bool
     * @throws \Exception
     */
    public function verifySign($sign,$timestamp,$nonce,$body,$serial)
    {
        if ($serial != $this->config->platformSerialNo()) {
            //平台证书需更新
            $this->config->platformCert(1);
        }

        $body = $body ? json_encode($body,256) : '';

        $message = $timestamp . "\n"
            .$nonce . "\n"
            .$body . "\n";

        if (!openssl_verify($message,base64_decode($sign),$this->config->platformCertKey(), OPENSSL_ALGO_SHA256)) {
            \Log::debug('微信支付apiV3--应答签名验证失败',[$sign,$timestamp,$nonce,$body,$serial]);
            throw new \Exception('应答签名验证失败');
        }
        return true;
    }
}