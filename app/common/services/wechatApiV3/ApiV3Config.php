<?php

namespace app\common\services\wechatApiV3;


class ApiV3Config
{
    private $config;

    /**
     * @var ApiV3Request
     */
    private $request;

    /**
     * @var ApiV3Encrypt
     */
    private $encrypt;

    public function __construct($config)
    {
        $this->config = $config;
    }

    private function check($key)
    {
        if (!$this->config[$key]) {
            throw new \Exception($key.'配置错误');
        }
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function appid()
    {
        $this->check('appid');
        return $this->config['appid'];
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function secret()
    {
        $this->check('secret');
        return $this->config['secret'];
    }

    /**
     * apiV3
     * @return mixed
     * @throws \Exception
     */
    public function secretV3()
    {
        $this->check('secret_v3');
        return $this->config['secret_v3'];
    }

    /**
     * 商户号
     * @return mixed
     * @throws \Exception
     */
    public function mchId()
    {
        $this->check('mchid');
        return $this->config['mchid'];
    }

    /**
     * API证书
     * @return mixed
     * @throws \Exception
     */
    public function apiCertPem()
    {
        $this->check('api_cert_pem');
        return $this->config['api_cert_pem'];
    }

    /**
     * API key
     * @return mixed
     * @throws \Exception
     */
    public function apiKeyPem()
    {
        $this->check('api_key_pem');
        return $this->config['api_key_pem'];
    }

    /**
     * 平台证书
     * @return string
     * @throws \Exception
     */
    public function platformCert($is_new = 0)
    {
        $file = $this->platformCertPath() . $this->platformCertFileName();
        if (!file_exists($file) || $is_new) {
            $this->request()->platformCertApply($this->platformCertPath(),$this->platformCertFileName());
        }
        return $file;
    }

    /**
     * 平台证书序列号
     * @return false|resource
     * @throws \Exception
     */
    public function platformSerialNo()
    {
        $ctx = stream_context_create([
            "ssl"=>[
                "verify_peer"=>false,
                "verify_peer_name"=>false,
            ]
        ]);
        $resource = openssl_x509_read(file_get_contents($this->platformCert(),false,$ctx));
        if (!$resource) {
            throw new \Exception('平台证书读取失败，请检查证书有效性!');
        }
        $arr = openssl_x509_parse($resource);
        return $arr['serialNumberHex'];
    }

    /**
     * API证书序列号
     * @return false|resource
     * @throws \Exception
     */
    public function apiSerialNo()
    {
        $ctx = stream_context_create([
            "ssl"=>[
                "verify_peer"=>false,
                "verify_peer_name"=>false,
            ]
        ]);
        $resource = openssl_x509_read(file_get_contents($this->apiCertPem(),false,$ctx));
        if (!$resource) {
            throw new \Exception('API证书读取失败，请检查证书有效性!');
        }
        $arr = openssl_x509_parse($resource);
        return $arr['serialNumberHex'];
    }

    /**
     * @return resource
     * @throws \Exception
     */
    public function privateKey()
    {
        $ctx = stream_context_create([
            "ssl"=>[
                "verify_peer"=>false,
                "verify_peer_name"=>false,
            ]
        ]);
        $resource = openssl_get_privatekey(file_get_contents($this->apiKeyPem(),false,$ctx));
        if (!$resource) {
            throw new \Exception('API秘钥读取失败，请检查秘钥有效性!');
        }
        return $resource;
    }

    /**
     * 平台公钥
     * @return resource
     * @throws \Exception
     */
    public function platformCertKey()
    {
        $ctx = stream_context_create([
            "ssl"=>[
                "verify_peer"=>false,
                "verify_peer_name"=>false,
            ]
        ]);
        $resource = openssl_get_publickey(file_get_contents($this->platformCert(),false,$ctx));
        if (!$resource) {
            throw new \Exception('平台证书秘钥读取失败!');
        }
        return $resource;
    }

    /**
     * @return ApiV3Request
     */
    public function request():ApiV3Request
    {
        if (!isset($this->request)) {
            $this->request = new ApiV3Request($this);
        }
        return $this->request;
    }

    /**
     * @return ApiV3Encrypt
     */
    public function encrypt():ApiV3Encrypt
    {
        if (!isset($this->encrypt)) {
            $this->encrypt = new ApiV3Encrypt($this);
        }
        return $this->encrypt;
    }

    private function platformCertPath()
    {
        return storage_path('platformcert' . DIRECTORY_SEPARATOR);
    }

    private function platformCertFileName()
    {
        return \YunShop::app()->uniacid . "_" . $this->mchId() . "_wechat_pay_cert.pem";
    }
}