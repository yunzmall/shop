<?php
/**
 * Created by PhpStorm.
 * Author: 芸众商城 www.yunzshop.com
 * Date: 17/2/23
 * Time: 上午11:21
 */

namespace app\frontend\modules\member\services;

use app\common\facades\EasyWeChat;
use app\common\models\AccountWechats;
use app\common\services\Session;
use app\frontend\models\Member;
use EasyWeChat\Foundation\Application;
use Illuminate\Support\Facades\Redis;
use app\common\helpers\Url;



class MemberPcOfficeAccountService extends MemberService
{
    const LOGIN_TYPE = 5;
    const IS_PC_QRCODE = 1;
    const WE_CHAT_SHOW_QR_CODE_URL = 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=';

    private $config;
    private $scene;

    public function __construct()
    {
        $this->config = '';
        if (!is_null(\app\common\modules\shop\ShopConfig::current()->get('wechat_qrcode_config'))) {
            $class    = array_get(\app\common\modules\shop\ShopConfig::current()->get('wechat_qrcode_config'), 'class');
            $function = array_get(\app\common\modules\shop\ShopConfig::current()->get('wechat_qrcode_config'), 'function');
            $this->config = $class::$function();
        }
        return $this->config;
    }

    //验证是否能扫码登录
    public function checkLogin(){
        $arr = array('status' => 0);

        if(empty($this->config)){
            $arr = ['status'=>1,'msg'=>'不支持扫码登录'];
        }else if($this->config['is_open'] == 0){
            $arr = ['status'=>1,'msg'=>'未开启扫码登录'];
        }
        return $arr;
    }

    public function login()
    {
        $check = $this->checkLogin();

        if($check['status'] == 1) {
            exit("5001" . $check['msg']);
        }

        $pc_token = \YunShop::request()->pc_token;

        $yz_redirect = request()->yz_redirect;

        $uniacid  = \YunShop::app()->uniacid;

        $mid = \app\common\models\Member::getMid();

        if($pc_token){
            if(Redis::get($pc_token)){
                return self::redirectUrl( Redis::get($pc_token.'member_id'),$uniacid, $mid, $yz_redirect); //登录成功 跳转会员中心
            }else{
                return show_json(10, '登录失败'); //todo status类型待优化
            }
        }else{
            return show_json(11, '生成二维码链接成功',array('account_url'=> $this->getQrCodeUrl(), 'pc_token' => $this->scene));
        }
    }

    private function getQrCodeUrl()
    {
        return static::WE_CHAT_SHOW_QR_CODE_URL . $this->getTicket();
    }


    /**
     * 生成公众号临时二维码，默认120s到期
     * @param $scene
     * @return mixed
     */
    private function createQR()
    {
        $account =  AccountWechats::getAccountByUniacid(\YunShop::app()->uniacid);
        $options = [
            'app_id'  => $account->key,
            'secret'  => $account->secret,
        ];
        $app = EasyWeChat::officialAccount($options);
        $qrcode = $app->qrcode;
        $result = $qrcode->temporary($this->getSceneValue(), 120);
        return $result;
    }

    public function checkLogged($login = null)
    {
        return MemberService::isLogged();
    }

    private function getTicket()
    {
        return self::createQR()['ticket'];
    }

    /**
     * 获取唯一场景值
     * @return string
     */
    private function getSceneValue()
    {
        $scene = sha1(rand(0,999999));
        $result = Redis::get($scene);
        if(!$result){
            Redis::setex($scene, 120, 0); //0 = 生成二维码未扫码
            $this->scene = $scene;
            return $scene;
        }else{
            $this->getSceneValue();
        }

    }

    /**
     * 扫码跳转到商城
     * @param $member_id
     * @param $uniacid
     * @param $mid
     */
    private function redirectUrl($member_id,$uniacid,$mid,$redirect_url){
        Session::set('member_id',$member_id);
        if($redirect_url){
            $url = base64_decode($redirect_url);
        }else{
            $url = Url::absoluteApp('member', ['i' => $uniacid, 'mid' => $mid]);//默认会员中心
        }
        $mobile = Member::where('uid', $member_id)->value('mobile');

        $pc_bind_mobile = 0;

        if(empty($mobile) && $this->config['pc_bind_mobile']){
              $pc_bind_mobile = 1;
        }
       return show_json(1,'登陆成功', ['url' => $url, 'pc_bind_mobile' => $pc_bind_mobile]);
    }




}