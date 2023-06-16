<?php

namespace app\frontend\modules\member\services;

use app\common\exceptions\MemberErrorMsgException;
use app\common\facades\EasyWeChat;
use app\common\models\AccountWechats;
use app\common\models\Member;
use app\common\services\Session;
use app\frontend\modules\member\models\MemberModel;
use app\frontend\modules\member\models\MemberWechatQrcodeModel;
use business\common\models\PlatLog;
use business\common\services\SettingService;
use Illuminate\Support\Facades\Redis;

class MemberBusinessScanCodeService extends MemberService
{
    const LOGIN_TYPE = 19;
    const IS_PC_QRCODE = 1;
    const WE_CHAT_SHOW_QR_CODE_URL = 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=';

    private $config;

    public function __construct()
    {
        $this->config = '';
        if (!is_null(\app\common\modules\shop\ShopConfig::current()->get('wechat_qrcode_config'))) {
            $class = array_get(\app\common\modules\shop\ShopConfig::current()->get('wechat_qrcode_config'), 'class');
            $function = array_get(\app\common\modules\shop\ShopConfig::current()->get('wechat_qrcode_config'), 'function');
            $this->config = $class::$function();
        }
        return $this->config;
    }

    //验证是否能扫码登录
    public function checkLogin($is_pc_qrcode)
    {
        $arr = array('status' => 0);

        if (empty($this->config)) {
            $arr = ['status' => 1, 'msg' => '不支持扫码登录'];
        } else if ($this->config['is_open'] == 0) {
            $arr = ['status' => 1, 'msg' => '未开启扫码登录'];
        } else if ($this->config['is_wechat_login'] == 1 && $is_pc_qrcode <> self::IS_PC_QRCODE) {
            $arr = ['status' => 1, 'msg' => '必须使用微信扫码登录'];
        }
        return $arr;
    }

    public function login()
    {
        $check = $this->checkLogin(\YunShop::request()->is_pc_qrcode);

        if ($check['status'] == 1) {
            exit("5001" . $check['msg']);
        }

        $yz_redirect = request()->yz_redirect;

        if ($this->config['wechat_login_type'] == 1) {

            return $this->nowLogin($yz_redirect);

        } elseif ($this->config['wechat_login_type'] == 2) {

            return $this->interestLogin($yz_redirect);

        }

    }

    /**
     * 关注关注公众号登录
     * @param $yzRedirect
     * @return array|void
     * @throws MemberErrorMsgException
     */
    protected function interestLogin($yzRedirect = '')
    {
        $yz_redirect = $yzRedirect;
        $is_from = request()->is_from;
        $pc_token = \YunShop::request()->pc_token;

        if ($pc_token) {
            if (Redis::get($pc_token)) {


                $member_info = Member::find(Redis::get($pc_token . 'member_id'));
                //登录成功
                if ($member_info) {
                    $this->save(array_add($member_info->toArray(), 'password', $member_info->password), $member_info->uniacid);
                } else {
                    throw new MemberErrorMsgException('用户不存在，登录失败！');
                }

                $params = [
                    'is_from' => $is_from
                ];
                return self::redirectUrl($yz_redirect, $params);


            } else {
                return show_json(10, '登录失败'); //todo status类型待优化
            }
        } else {
            return show_json(11, '生成二维码链接成功', array('account_url' => $this->getQrCodeUrl(), 'pc_token' => $this->scene));
        }
    }

    /**
     * 扫码立即登录
     * @param $yzRedirect
     * @return array|void
     * @throws MemberErrorMsgException
     * @throws \app\common\exceptions\AppException
     */
    protected function nowLogin($yzRedirect = '')
    {
        $yz_redirect = $yzRedirect;
        $is_from = request()->is_from;
        $code = \YunShop::request()->code;

        $business_id = request('business_id', SettingService::bindBusinessId());
        $request_url = $_SERVER['REQUEST_URI'] . "&business_id={$business_id}" . "&yz_redirect={$yz_redirect}" . "&is_from={$is_from}";
        $callback = ($_SERVER['REQUEST_SCHEME'] ? $_SERVER['REQUEST_SCHEME'] : 'http') . '://' . $_SERVER['HTTP_HOST'] . $request_url;

        $state = 'yz-' . session_id();
        Session::set('wx_qrcode_state', $state);

        $wxurl = $this->_getAuthUrl($this->config['appid'], $callback, $state);

        if (!empty($code)) {

            $query = parse_url($callback, PHP_URL_QUERY);
            parse_str($query, $params);


            \YunShop::app()->uniacid = \Setting::$uniqueAccountId = $params['i'];
            SettingService::setBusinessId($params['business_id']);
            $url_path = base64_decode($params['yz_redirect']);
            $is_from = $params['is_from'];

            $token = $this->_getTokenUrl($this->config['appid'], $this->config['app_secret'], $code);
//            \Log::debug('token信息', $token);

            if (!empty($token) && is_array($token) && $token['errmsg'] == 'invalid code') {
                return show_json(0, array('msg' => '请求错误'));
            }

            $user_info = $this->_getUserInfoUrl($token['access_token'], $token['openid']);
            \Log::debug('企业微信PC端扫码登录微信授权成功', $user_info);

            if (is_array($user_info) && !empty($user_info['errcode'])) {
                \Log::debug('---微信扫码登陆授权失败---', $user_info);
                throw new MemberErrorMsgException('微信扫码登陆授权失败');
            }

            $member_id = $this->memberLogin($user_info);


            $member_info = Member::find($member_id);
            //登录成功
            if ($member_info) {
                $this->save(array_add($member_info->toArray(), 'password', $member_info->password), $member_info->uniacid);
            } else {
                throw new MemberErrorMsgException('用户不存在，登录失败！');
            }

            $busniess_id = PlatLog::where('uid', $member_id)->orderByDesc('id')->value('final_plat_id');

            $params = [
                'is_from' => $is_from
            ];
            self::redirectUrl($url_path, $params, $busniess_id);

        } else {
            return show_json(9, array('url' => $wxurl, 'msg' => '生成二维码链接成功'));
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
        return "https://open.weixin.qq.com/connect/qrconnect?appid=" . $appId . "&redirect_uri=" . urlencode($url) . "&response_type=code&scope=snsapi_login&state={$state}#wechat_redirect";
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
     * 扫码跳转到企业微信PC端
     * @param $urlPath
     * @param array $from
     * @return array|void
     */
    public function redirectUrl($urlPath = null, array $from = [], $busniessId = null)
    {
        if ($this->config['wechat_login_type'] == 2) {
            return show_json(1, '登陆成功', ['url' => '']);
        }

        $params['cid'] = $busniessId ?: SettingService::getBusinessId();
        SettingService::setBusinessId($params['cid']);
        $params = array_merge($params, $from);

        if ($urlPath) {
            $url = yzBusinessFullUrl($urlPath, $params);;
        } else {
            $url = yzBusinessFullUrl('business/index', $params);//默认企业微信PC端主页
        }

        redirect($url)->send();//跳转到前端会员中心页面
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
                'uniacid' => $uniacid,
                'member_id' => $uid,
                'openid' => $userinfo['openid'],
                'nickname' => $userinfo['nickname'],
                'avatar' => $userinfo['headimgurl'],
                'gender' => $userinfo['sex'],
                'province' => '',
                'country' => '',
                'city' => '',
            ));
        }
    }

    private function getQrCodeUrl()
    {
        return static::WE_CHAT_SHOW_QR_CODE_URL . $this->getTicket();
    }

    private function getTicket()
    {
        return self::createQR()['ticket'];
    }

    /**
     * 生成公众号临时二维码，默认120s到期
     * @param $scene
     * @return mixed
     */
    private function createQR()
    {
        $account = AccountWechats::getAccountByUniacid(\YunShop::app()->uniacid);
        $options = [
            'app_id' => $account->key,
            'secret' => $account->secret,
        ];
        $app = EasyWeChat::officialAccount($options);
        $qrcode = $app->qrcode;
        $result = $qrcode->temporary($this->getSceneValue(), 120);
        return $result;
    }

    /**
     * 获取唯一场景值
     * @return string
     */
    private function getSceneValue()
    {
        $scene = sha1(rand(0, 999999));
        $result = Redis::get($scene);
        if (!$result) {
            Redis::setex($scene, 120, 0); //0 = 生成二维码未扫码
            $this->scene = $scene;
            return $scene;
        } else {
            $this->getSceneValue();
        }

    }

}
