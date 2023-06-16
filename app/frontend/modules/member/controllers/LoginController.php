<?php
/**
 * Created by PhpStorm.
 * Author:
 * Date: 17/2/22
 * Time: 上午11:56
 */

namespace app\frontend\modules\member\controllers;

use app\common\components\ApiController;
use app\common\events\member\MemberLoginEvent;
use app\common\facades\RichText;
use app\common\helpers\Client;
use app\common\helpers\Url;
use app\common\models\Member;
use app\common\models\Protocol;
use app\common\services\Session;
use app\frontend\models\MemberShopInfo;
use app\frontend\modules\member\services\factory\MemberFactory;
use app\frontend\modules\member\services\MemberService;
use Illuminate\Contracts\Encryption\DecryptException;

class LoginController extends ApiController
{
    protected $publicController = ['Login'];
    protected $publicAction = ['index', 'phoneSetGet', 'chekAccount', 'loginMode','newLoginMode'];
    protected $ignoreAction = ['index', 'phoneSetGet', 'chekAccount', 'loginMode','newLoginMode'];

    public function index()
    {
        $type = request()->input('type');

        $uniacid = \YunShop::app()->uniacid;
        $mid = Member::getMid();

        //判断是否开启微信登录
        if (\YunShop::request()->show_wechat_login) {
            return $this->init_login();
        }

        if (!empty($type)) {
            $member = MemberFactory::create($type);

            if ($member !== null) {
                $msg = $member->login();

                if (!empty($msg)) {
                    if ($msg['status'] == 1 || $msg['status'] == 11) {
                        $url = Url::absoluteApp('member', ['i' => $uniacid, 'mid' => $mid]);

                        if (isset($msg['json']['redirect_url'])) {
                            $url = $msg['json']['redirect_url'];
                        }

                        if (isset($msg['variable']['url'])) {
                            $url = $msg['variable']['url'];
                        }

                        $data = $msg['variable'];
                        $data['status'] = $msg['status'];
                        $data['url'] = $url;
                        $this->updateLastLoginTime($msg['json']['uid']);
                        return $this->successJson($msg['json'], $data);
                    } else {
                        return $this->errorJson($msg['json'], ['status' => $msg['status']]);
                    }
                } else {
                    return $this->errorJson('登录失败', ['status' => 3]);
                }
            } else {
                return $this->errorJson('登录异常', ['status' => 2]);
            }
        } else {
            return $this->errorJson('客户端类型错误', ['status' => 0]);
        }
    }

    /**
     * 初始化登录，判断是否开启微信登录
     *
     * @return \Illuminate\Http\JsonResponse
     */
    private function init_login()
    {
        $weixin_oauth = \Setting::get('shop_app.pay.weixin_oauth');
        return $this->successJson('', ['status' => 1, 'wetach_login' => $weixin_oauth]);
    }

    public function updateLastLoginTime($uid)
    {
        event(new MemberLoginEvent($uid));

        MemberShopInfo::where('member_id', $uid)->update(['last_login_time' => time()]);
    }

    public function phoneSetGet()
    {
        $phone_oauth = \Setting::get('shop_app.pay.phone_oauth');

        if (empty($phone_oauth)) {
            $phone_oauth = '0';
        }
        return $this->successJson('ok', ['phone_oauth' => $phone_oauth]);
    }

    public function chekAccount()
    {
        $type = \YunShop::request()->type;

        if (1 == $type) {
            $member = MemberFactory::create($type);
            $member->chekAccount();
        }
    }

    public function checkLogin()
    {
        return $this->successJson('已登录');
    }

    public function loginMode()
    {
        $data = [];
        //增加验证码功能
        $status = \Setting::get('shop.sms.status');
        if (extension_loaded('fileinfo')) {
            if ($status == 1) {
                $captcha = self::captcha();
                $result['captcha'] = $captcha;
                $result['captcha']['status'] = $status;
            } else {
                $result['captcha']['status'] = $status;
            }
        }
        $data['yun_sign'] = [];
        if (app('plugins')->isEnabled('yun-sign')) {
            $yun_sign = [
                'login_type' => 5,
                'redirect_url' => '',
                'tel' => ''
            ];
            $redirect_url = Session::get('ys_short_url_redirect_url');
            $redirect_tel = intval(Session::get('ys_short_url_redirect_tel'));
            if ($redirect_url && $redirect_tel) {
                $yun_sign = [
                    'login_type' => 10,
                    'redirect_url' => $redirect_url,
                    'tel' => $redirect_tel
                ];
            }
            $data['yun_sign'] = $yun_sign;
        }

        $data['sms'] = $result;
        $registerSet = \Setting::get('shop.register');
        $data['mobile_login_code'] = !$registerSet['login_mode'] || in_array('mobile_code',$registerSet['login_mode']) ? 1 : 0;
        $data['mobile_code_login'] = !$registerSet['login_mode'] || in_array('mobile_code',$registerSet['login_mode']) ? 1 : 0;//开启手机验证码登录
        $data['password_login'] = !$registerSet['login_mode'] || in_array('password',$registerSet['login_mode']) ? 1 : 0;//开启密码登录
        $data['logo'] = !empty(\Setting::get('shop.shop')['logo']) ? yz_tomedia(\Setting::get('shop.shop')['logo']) : 0;
        $data['protocol_title'] = Protocol::uniacid()->value('title') ?: '平台用户协议';
        $data['country_code'] = \Setting::get('shop.sms')['country_code']?1:0;

        if (!is_null(\app\common\modules\shop\ShopConfig::current()->get('wechat_qrcode_config'))) {
            $class = array_get(\app\common\modules\shop\ShopConfig::current()->get('wechat_qrcode_config'), 'class');
            $function = array_get(\app\common\modules\shop\ShopConfig::current()->get('wechat_qrcode_config'), 'function');
            $wechat_qrcode_config = $class::$function();
            $data['wechat_qrcode_config'] = [
                'is_open'           => $wechat_qrcode_config['is_open'],
                'is_wechat_login'   => $wechat_qrcode_config['is_wechat_login'],
                'callback'          => $wechat_qrcode_config['callback'],
                'wechat_login_type' => $wechat_qrcode_config['wechat_login_type'],
            ];
            unset($wechat_qrcode_config);
        }


        return $this->successJson('ok', $data);
    }

    /**
     * 新版登录页接口
     * @return \Illuminate\Http\JsonResponse
     */
    public function newLoginMode()
    {
        //增加验证码功能
        $status = \Setting::get('shop.sms.status');
        if (extension_loaded('fileinfo')) {
            if ($status == 1) {
                $captcha = self::captcha();
                $result['captcha'] = $captcha;
                $result['captcha']['status'] = $status;
            } else {
                $result['captcha']['status'] = $status;
            }
        }
        $data['yun_sign'] = [];
        if (app('plugins')->isEnabled('yun-sign')) {
            $yun_sign = [
                'login_type' => 5,
                'redirect_url' => '',
                'tel' => ''
            ];
            $redirect_url = Session::get('ys_short_url_redirect_url');
            $redirect_tel = intval(Session::get('ys_short_url_redirect_tel'));
            if ($redirect_url && $redirect_tel) {
                $yun_sign = [
                    'login_type' => 10,
                    'redirect_url' => $redirect_url,
                    'tel' => $redirect_tel
                ];
            }
            $data['yun_sign'] = $yun_sign;
        }

        $data['is_wallet_log'] = 0;
        if (app('plugins')->isEnabled('love-speed-pool')) {
            //钱包登录入口
            $data['is_wallet_log'] = \Setting::get('plugin.love_speed_pool.is_wallet_log');
        }

        $data['sms'] = $result ? : [];
        $shopSet = \Setting::get('shop.shop');
        $registerSet = \Setting::get('shop.register');
        $memberSet = \Setting::get('shop.member');
        $data['get_register'] = $memberSet['get_register'] ? 1 : 0;
        $data['shop_name'] = $shopSet['name']?:'商城';
        $data['logo'] = $shopSet['logo'] ? yz_tomedia($shopSet['logo']) : "";
        $data['title1'] = $registerSet['title1'] ? : '欢迎来到['.($shopSet['name']?:'商城').']';
        $data['title2'] = $registerSet['title2'] ? : '登录尽享各种优惠权益！';
        $data['login_page_mode'] = $registerSet['login_page_mode'] ? : 0;
        $data['login_banner_url'] = $registerSet['login_banner'] ? yz_tomedia($registerSet['login_banner']): '';
        $data['login_diy_url'] = $registerSet['login_diy_url'] ? : '';
        $registerSet['login_mode'] || $registerSet['login_mode'] = [];
        $data['mobile_code_login'] = !$registerSet['login_mode'] || in_array('mobile_code',$registerSet['login_mode']) ? 1 : 0;//开启手机验证码登录
        $data['register_status'] = $registerSet['get_register'] == 1 ? 1 : 0;//注册状态/
        $data['password_login'] = !$registerSet['login_mode'] || in_array('password',$registerSet['login_mode']) ? 1 : 0;//开启密码登录
        $data['country_code'] = \Setting::get('shop.sms')['country_code']?1:0;

        $agreement = RichText::get('shop.agreement');
        $data['platform_agreement'] = [
            'status' => $shopSet['is_agreement'] ? 1 : 0,
            'title'  => $shopSet['agreement_name'] ? : "平台协议",
            'content'  => $agreement ? : ""
        ];

        $protocol = Protocol::uniacid()->first();
        $data['register_agreement'] = [
            'status' => $protocol->status ? : 0,
            'title'  => $protocol->title ? : "会员注册协议",
            'default_tick'  => $protocol->default_tick ? : 0,
            'content'  => $protocol->content ? : ""
        ];

        if (!is_null(\app\common\modules\shop\ShopConfig::current()->get('wechat_qrcode_config'))) {
            $class = array_get(\app\common\modules\shop\ShopConfig::current()->get('wechat_qrcode_config'), 'class');
            $function = array_get(\app\common\modules\shop\ShopConfig::current()->get('wechat_qrcode_config'), 'function');
            $wechat_qrcode_config = $class::$function();
            $data['wechat_qrcode_config'] = [
                'is_open'           => $wechat_qrcode_config['is_open'],
                'is_wechat_login'   => $wechat_qrcode_config['is_wechat_login'],
                'callback'          => $wechat_qrcode_config['callback'],
                'wechat_login_type' => $wechat_qrcode_config['wechat_login_type'],
            ];
            unset($wechat_qrcode_config);
        }

        return $this->successJson('ok', $data);
    }

    //增加验证码功能
    public function captcha()
    {
        $captcha = app('captcha');
        $captcha_base64 = $captcha->create('default', true);

        return $captcha_base64;
    }
}
