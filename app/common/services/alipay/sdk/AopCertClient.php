<?php
/**
 * Created by PhpStorm.
 * Name: 芸众商城系统
 * Author: 广州市芸众信息科技有限公司
 * Profile: 广州市芸众信息科技有限公司位于国际商贸中心的广州，专注于移动电子商务生态系统打造，拥有芸众社交电商系统、区块链数字资产管理系统、供应链管理系统、电子合同等产品/服务。官网 ：www.yunzmall.com  www.yunzshop.com
 * Date: 2021/6/30
 * Time: 10:17
 */

namespace app\common\services\alipay\sdk;


use Alipay\EasySDK\Kernel\Factory;

class AopCertClient
{

    protected $config;

    public function __construct()
    {
        $this->_initConfig();
    }

    private function _initConfig()
    {
        $options = new \Alipay\EasySDK\Kernel\Config();
        $options->protocol = 'https';
        //$options->gatewayHost = 'openapi.alipaydev.com'; //沙箱测试
        $options->gatewayHost = 'openapi.alipay.com';
        $options->signType = 'RSA2';

        $this->config = $options;
    }

    public function execute($method, $bizParams,$textParams = [])
    {
        //设置支付宝参数（全局只需设置一次）
        Factory::setOptions($this->config);

        try {

            //支付宝请求接口Client
            $aop = Factory::util()->generic();
            // 发起API调用
            //1、接口名称  2、接口公共参数附加 3、请求参数 biz_content
            $result = $aop->execute($method,$textParams,$bizParams);

            //3. 处理响应或异常
            if (!empty($result->code) && $result->code == 10000) {
                return ['code'=> true,
                    'data'=> $result->toMap(),
                    'msg'=> '成功'
                ];
            }
            return ['code'=> false,
                'data'=> $result->toMap(),
                'msg'=> "调用失败，原因：". $result->msg."，".$result->subMsg
            ];
        } catch (\Exception $e) {
            return ['code'=> false, 'msg'=> $e->getMessage()];
        }


    }

    public function getConfig($key = null)
    {
        if ($key == null) {
            return $this->config;
        }

        return $this->config->$key;
    }


    public function setConfigValue($key,$value)
    {
        $this->config->{$key} = $value;
    }

    /**
     * @param $config
     */
    public function setConfig(array $config)
    {
        if (!empty($config) && is_array($config)) {
            foreach ($config as $k => $v) {
                $this->config->{$k} = $v;
            }
        }

//        $options = new Config();
//        $options->protocol = 'https';
//        $options->gatewayHost = 'openapi.alipay.com';
//        $options->signType = 'RSA2';
//
//        $options->appId = '<-- 请填写您的AppId，例如：2019022663440152 -->';
//
//        // 为避免私钥随源码泄露，推荐从文件中读取私钥字符串而不是写入源码中
//        $options->merchantPrivateKey = '<-- 请填写您的应用私钥，例如：MIIEvQIBADANB ... ... -->';
//
//        $options->alipayCertPath = '<-- 请填写您的支付宝公钥证书文件路径，例如：/foo/alipayCertPublicKey_RSA2.crt -->';
//        $options->alipayRootCertPath = '<-- 请填写您的支付宝根证书文件路径，例如：/foo/alipayRootCert.crt" -->';
//        $options->merchantCertPath = '<-- 请填写您的应用公钥证书文件路径，例如：/foo/appCertPublicKey_2019051064521003.crt -->';
//
//        //注：如果采用非证书模式，则无需赋值上面的三个证书路径，改为赋值如下的支付宝公钥字符串即可
//        // $options->alipayPublicKey = '<-- 请填写您的支付宝公钥，例如：MIIBIjANBg... -->';
//
//        //可设置异步通知接收服务地址（可选）
//        $options->notifyUrl = "<-- 请填写您的支付类接口异步通知接收服务地址，例如：https://www.test.com/callback -->";
//
//        //可设置AES密钥，调用AES加解密相关接口时需要（可选）
//        $options->encryptKey = "<-- 请填写您的AES密钥，例如：aa4BtZ4tspm2wnXLb1ThQA== -->";

    }
}