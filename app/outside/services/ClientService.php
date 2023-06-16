<?php
/**
 * Created by PhpStorm.
 * 
 *
 *
 * Date: 2022/1/7
 * Time: 15:15
 */

namespace app\outside\services;


use app\outside\modes\OutsideAppSetting;

class ClientService
{
    private $route; //方法
    private $app_id; //应用AppID
    private $secret; //密钥字符串

    private $data = []; //data参数
    private $sign_type = "MD5"; //SHA256 MD5

    public function __construct()
    {
        $this->init();
    }

    /**
     * 配置信息
     */
    public function init()
    {
        $outsideApp = OutsideAppSetting::current();

        $this->app_id = $outsideApp->app_id;
        $this->secret = $outsideApp->app_secret;
    }


    public function setRoute($route)
    {
        $this->route = $route;
    }

    public function getRoute()
    {
        return $this->route;
    }

    /**
     * 请求参数
     * @return array
     */
    public function getAllParameter()
    {
        //去除秘钥
        $vars = array_except(get_object_vars($this), ['secret','data']);

        $result = array_merge($vars, $this->getAllData());

        return $result;
    }

    public function initData($data)
    {
        $this->data = $data;
    }

    public function setData($key, $value)
    {
        $this->data[$key] = $value;
    }

    /**
     * 通过键获取 data 指定值
     * @param $key
     * @return mixed|string
     */
    public function getData($key)
    {
        return $this->data[$key] ? $this->data[$key] : '';
    }

    public function getAllData()
    {
        return $this->data;
    }


    /**
     * @param $url
     * @param $data
     *
     */
    public function post($url = '')
    {
        $requestData = $this->getAllParameter();

        $requestData['sign'] = $this->autograph($requestData['sign_type']);


        return $requestData;

    }

    protected function autograph($type)
    {
        if (strtoupper($type) == 'SHA256') {
            return  $this->verifySha256();
        }

        return $this->verifyMd5();
    }

    protected function verifySha256()
    {
        $hashSign = hash_hmac('sha256', $this->toQueryString($this->getAllParameter()), $this->secret);

        return $hashSign;
    }

    protected function verifyMd5()
    {
        return strtoupper(md5($this->toQueryString($this->getAllParameter()).'&secret='.$this->secret));
    }

    /**
     * 将参数转换成k=v拼接的形式
     * @param $parameter
     * @return string
     */
    public function toQueryString($parameter)
    {

        //按key的字典序升序排序，并保留key值
        ksort($parameter);

        $strQuery="";
        foreach ($parameter as $k=>$v){

            //不参与签名、验签
            if($k == "sign"){
                continue;
            }

            if($v === null) {$v = '';}

            $strQuery .= strlen($strQuery) == 0 ? "" : "&";
            $strQuery.=$k."=".$v;
        }

        return $strQuery;
    }

}