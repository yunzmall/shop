<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/7/29
 * Time: 9:44
 */

namespace app\common\services\wechat;

use app\common\exceptions\ShopException;
use app\common\services\Utils;
use GuzzleHttp\Client;

class WxaQrCodeService
{

    protected $get_token_url = 'https://api.weixin.qq.com/cgi-bin/token?'; //获取token的url
    protected $wxaUrl = 'https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token=';//获取小程序码url

    private $patch; //小程序码保存路径
    private $fileName; //小程序码文件名称

    private $new = false; //是否重新生成



    protected $parameters;

    /**
     * @param $url
     * @param $patch
     * @throws ShopException
     */
    function __construct($patch, $new = false)
    {
        $this->patch = $patch;

        $this->new =  $new;
    }

    /**
     * @param mixed $parameters
     */
    public function setParameter($key, $value)
    {
        $this->parameters[$key] = $value;
    }

    /**
     * @return mixed
     */
    public function getParameter($key)
    {
        return isset($this->parameters[$key])?$this->parameters[$key]:'';
    }

    public function getAllParameters()
    {
        return $this->parameters;
    }

    /**
     * @return bool|mixed|\Psr\Http\Message\ResponseInterface|string
     * @throws ShopException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getQrCode()
    {
        if (!$this->getParameter('page')) {
            throw new ShopException('小程序页面路径不能为空');
        }

        $filePathName = $this->getQrFileFullPath();

        if (file_exists($filePathName) && $this->new === false) {
            return  $this->getPathUrl();

        }

        return $this->getWxaCode();
    }


    /**
     * 生成小程序码
     * @param $order_id
     * @return bool|mixed|\Psr\Http\Message\ResponseInterface
     * @throws ShopException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getWxaCode()
    {
        $token = $this->getToken();

        $url = $this->wxaUrl.$token;
//        $json_data = [
//            "scene" => 'order_id=232&orderType=verifier',
//            "page"  => 'packageA/member/orderdetail/orderdetail'
//        ];
        $client = new Client;
        $res = $client->request('POST', $url, ['json'=>$this->getAllParameters()]);
        $data = json_decode($res->getBody()->getContents(), JSON_FORCE_OBJECT);

        //$path_file = $this->getPosterPath().'ceshi.png';
        //file_put_contents($path_file, $data);

        if (isset($data['errcode'])) {
            \Log::debug('-----小程序码生成失败------', $data);
            throw new ShopException('小程序码生成失败:'.$data['errMsg']);
        }

        $qr_binary_content = $res->getBody(); //图片二进制内容

        return $this->saveWxaQrCode($qr_binary_content);
    }

    /**
     * 发送获取token请求,获取token(有效期2小时)
     * @return mixed
     * @throws ShopException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function getToken()
    {
        $set = \Setting::get('plugin.min_app');

        $paramMap = [
            'grant_type' => 'client_credential',
            'appid' => $set['key'],
            'secret' => $set['secret'],
        ];
        //获取token的url参数拼接
        $strQuery="";
        foreach ($paramMap as $k=>$v){
            $strQuery .= strlen($strQuery) == 0 ? "" : "&";
            $strQuery.=$k."=".urlencode($v);
        }

        $getTokenUrl = $this->get_token_url. $strQuery; //获取token的url

        $client = new Client();
        $res = $client->request('GET', $getTokenUrl);

        $data = json_decode($res->getBody()->getContents(), JSON_FORCE_OBJECT);

        if (isset($data['errcode'])) {
            \Log::debug('----小程序码获取token失败---', $data);
            throw new ShopException('小程序码获取token失败:'.$data['errmsg']);
        }
        return $data['access_token'];
    }

    /**
     * 保存小程序码
     * @param $qr_binary_content
     * @return string
     */
    private function saveWxaQrCode($qr_binary_content)
    {

        $filePathName = $this->getQrFileFullPath();

        unlink(storage_path($filePathName));//存在删除

        file_put_contents($filePathName, $qr_binary_content); //保存

        return  $this->getPathUrl();
    }

    /**
     * 获取小程序码文件名
     * @return string
     */
    protected function getFileName()
    {
        if (!isset($this->fileName)) {
            $file_name = md5(json_encode($this->getAllParameters()));
            $this->fileName = $file_name.'.png';
        }
        return $this->fileName;
    }

    /**
     * 文件储存全路径
     * @return string
     */
    private function getQrFileFullPath()
    {
        return $this->getStoragePath() .DIRECTORY_SEPARATOR. $this->getFileName();
    }

    /**
     * 储存路径
     * @return string
     */
    public function getStoragePath()
    {
        $path = storage_path($this->patch);

        if (!is_dir($path)) {
            Utils::mkdirs($path);
        }

        return $path;
    }

    public function getPathUrl()
    {
        return request()->getSchemeAndHttpHost() . config('app.webPath') . \Storage::url($this->patch .DIRECTORY_SEPARATOR.$this->getFileName());
    }

}