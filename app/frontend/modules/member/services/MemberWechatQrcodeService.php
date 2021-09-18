<?php
/**
 * Created by PhpStorm.
 * Author: 芸众商城 www.yunzshop.com
 * Date: 17/2/23
 * Time: 上午11:21
 */

namespace app\frontend\modules\member\services;

use app\common\exceptions\MemberErrorMsgException;
use app\common\helpers\Url;
use app\frontend\modules\member\models\MemberUniqueModel;
use app\frontend\modules\member\models\MemberWechatQrcodeModel;
use app\common\services\Session;
use app\frontend\models\Member;


class MemberWechatQrcodeService extends MemberService
{
    const LOGIN_TYPE = 5;
    const IS_PC_QRCODE = 1;

    private $config;

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
    public function checkLogin($is_pc_qrcode){
        $arr = array('status' => 0);

        if(empty($this->config)){
            $arr = ['status'=>1,'msg'=>'不支持扫码登录'];
        }else if($this->config['is_open'] == 0){
            $arr = ['status'=>1,'msg'=>'未开启扫码登录'];
        }else if($this->config['is_wechat_login'] == 1 && $is_pc_qrcode <> self::IS_PC_QRCODE){
            $arr = ['status'=>1,'msg'=>'必须使用微信扫码登录'];
        }
        return $arr;
    }

    public function login()
    {
        $check = $this->checkLogin(\YunShop::request()->is_pc_qrcode);

        if($check['status'] == 1) {
            exit("5001" . $check['msg']);
        }
        $yz_redirect = request()->yz_redirect;

        $uniacid  = \YunShop::app()->uniacid;
        $mid = \app\common\models\Member::getMid();
        $code = \YunShop::request()->code;


        $request_url = $_SERVER['REQUEST_URI'] . "&is_pc_qrcode=" . \YunShop::request()->is_pc_qrcode . "&yz_redirect=" . $yz_redirect;
        $callback = ($_SERVER['REQUEST_SCHEME'] ? $_SERVER['REQUEST_SCHEME'] : 'http')  . '://' . $_SERVER['HTTP_HOST'] . $request_url;
//        \Log::debug('--回调地址--',$callback);

        $state = 'yz-' . session_id();
        Session::set('wx_qrcode_state',$state);

        $wxurl = $this->_getAuthUrl($this->config['appid'],$callback,$state);

        if (!empty($code)) {
            $query = parse_url($callback,PHP_URL_QUERY);
            parse_str($query,$params);
            $redirect_url = $params['yz_redirect'];//重定向地址

            $token = $this->_getTokenUrl($this->config['appid'], $this->config['app_secret'], $code);
//            \Log::debug('token信息', $token);

            if (!empty($token) && is_array($token) && $token['errmsg'] == 'invalid code') {
                return show_json(0, array('msg'=>'请求错误'));
            }

            $user_info = $this->_getUserInfoUrl($token['access_token'], $token['openid']);
            \Log::debug('PC端扫码登录微信授权成功', $user_info);

            if (is_array($user_info) && !empty($user_info['errcode'])) {
                \Log::debug('---微信扫码登陆授权失败---', $user_info);
                throw new MemberErrorMsgException('微信扫码登陆授权失败');
            }

            $member_id = $this->memberLogin($user_info);
            Session::set('member_id', $member_id);

            self::redirectUrl($member_id,$uniacid,$mid,$redirect_url);
        } else {
            return show_json(9, array('url'=> $wxurl,'msg'=>'生成二维码链接成功'));
        }
    }

    /**
     * api
     * 生成微信扫码登录二维码
     * snsapi_userinfo
     * @param $appId
     * @param $url
     * @param $state
     * @return string
     */
    private function _getAuthUrl($appId, $url, $state)
    {
        return "https://open.weixin.qq.com/connect/qrconnect?appid=" . $appId ."&redirect_uri=" . urlencode($url) . "&response_type=code&scope=snsapi_login&state={$state}#wechat_redirect";
    }

    /**
     * 获取微信用户信息
     * @param $accesstoken
     * @param $openid
     * @return mixed
     */
    private function _getUserInfoUrl($accesstoken, $openid)
    {
        $url = "https://api.weixin.qq.com/sns/userinfo?access_token={$accesstoken}&openid={$openid}&lang=zh_CN";
        return $userinfo_url = \Curl::to($url)
            ->asJsonResponse(true)
            ->get();
    }

    /**
     * 获取token api
     *
     * @param $appId
     * @param $appSecret
     * @param $code
     * @return string
     */
    private function _getTokenUrl($appId, $appSecret, $code)
    {
        $url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=" . $appId . "&secret=" . $appSecret . "&code=" . $code . "&grant_type=authorization_code";
        return $tokenurl = \Curl::to($url)
            ->asJsonResponse(true)
            ->get();
    }

    /**
     * 会员关联表操作
     *
     * @param $uniacid
     * @param $member_id
     * @param $unionid
     */
    public function addMemberUnionid($uniacid, $member_id, $unionid)
    {
        MemberUniqueModel::replace(array(
            'uniacid' => $uniacid,
            'unionid' => $unionid,
            'member_id' => $member_id,
            'type' => self::LOGIN_TYPE
        ));
    }

    /**
     * @param $uid
     * @param $uniacid
     * @param $userinfo
     */
    public function addFansMember($uid, $uniacid, $userinfo)
    {
        $user = MemberWechatQrcodeModel::getUserInfo_memberid($uid);
        if (!empty($user)) {
            $this->updateMemberInfo($uid, $userinfo);
        } else {
            MemberWechatQrcodeModel::replace(array(
                'uniacid'   => $uniacid,
                'member_id' => $uid,
                'openid'    => $userinfo['openid'],
                'nickname'  => $userinfo['nickname'],
                'avatar'    => $userinfo['headimgurl'],
                'gender'    => $userinfo['sex'],
                'province'  => '',
                'country'   => '',
                'city'      => '',
            ));
        }
    }

    /**
     *
     * @param $openid
     *
     * @return mixed
     */
    public function getFansModel($openid)
    {
        $model = MemberWechatQrcodeModel::getUserInfo($openid);
        if (!is_null($model)) {
            $model->uid = $model->member_id;
        }
    }

    /**
     * 验证登录状态
     *
     * @return bool
     */
    public function checkLogged($login = null)
    {
        return MemberService::isLogged();
    }

    /**
     * 扫码跳转到商城
     * @param $member_id
     * @param $uniacid
     * @param $mid
     */
    public function redirectUrl($member_id,$uniacid,$mid,$redirect_url){

        Session::set('member_id',$member_id);

        if($redirect_url){
            $url = base64_decode($redirect_url);
        }else{
            $url = Url::absoluteApp('member', ['i' => $uniacid, 'mid' => $mid]);//默认会员中心
        }

        $mobile = Member::where('uid', $member_id)->value('mobile');

        if(empty($mobile) && $this->config['pc_bind_mobile']){
            if(\YunShop::request()->pc){
                $url = 'https://' .$_SERVER['HTTP_HOST'] ."/plugins/shop_server/login?i={$uniacid}&from=phone";
            }else{
                $url =  Url::absoluteApp('login', ['i' => $uniacid, 'from' => 'phone']); ;
            }
        }

        redirect($url)->send();//跳转到前端会员中心页面
    }

    public function addMemberInfo($uniacid, $userinfo)
    {
        $uid = parent::addMemberInfo($uniacid, $userinfo);

        \Log::debug('---wechat_qrcode_fans---', $uid);
        //添加wechat_qrcode_fans表
        $this->addFansMember($uid, $uniacid, $userinfo);

        return $uid;
    }

}