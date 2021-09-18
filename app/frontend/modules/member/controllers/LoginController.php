<?php
/**
 * Created by PhpStorm.
 * Author: 芸众商城 www.yunzshop.com
 * Date: 17/2/22
 * Time: 上午11:56
 */

namespace app\frontend\modules\member\controllers;

use app\common\components\ApiController;
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
    protected $publicAction = ['index', 'phoneSetGet', 'chekAccount','loginMode'];
    protected $ignoreAction = ['index', 'phoneSetGet', 'chekAccount','loginMode'];

    public function index()
    {
        $type = \YunShop::request()->type ;
        $uniacid = \YunShop::app()->uniacid;
        $mid = Member::getMid();
        if (empty($type) || $type == 'undefined') {
            $type = Client::getType();
        }

        if ($type == 8 && !(app('plugins')->isEnabled('alipay-onekey-login'))) {
            $type = Client::getType();
        }
        //判断是否开启微信登录
        if (\YunShop::request()->show_wechat_login) {
            return $this->init_login();
        }

        if(\Setting::get('shop.member.mobile_login_code') == 1 and \YunShop::request()->is_sms == 1){
            // todo 待优化，需要考虑其他很多种情况
            $type = 10;
        }

        if (\YunShop::request()->client) {
            $type = 17;
        }

        if (!empty($type)) {
            $member = MemberFactory::create($type);

            if ($member !== NULL) {
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
                        return $this->errorJson($msg['json'], ['status'=> $msg['status']]);
                    }
                } else {
                    return $this->errorJson('登录失败', ['status' => 3]);
                }
            } else {
                return $this->errorJson('登录异常', ['status'=> 2]);
            }
        } else {
            return $this->errorJson('客户端类型错误', ['status'=> 0]);
        }
    }

    /**
     * 初始化登录，判断是否开启微信登录
     *
     * @return \Illuminate\Http\JsonResponse
     */
    private function init_login () {
        $weixin_oauth = \Setting::get('shop_app.pay.weixin_oauth');
        return $this->successJson('', ['status'=> 1, 'wetach_login' => $weixin_oauth]);
    }

    public function updateLastLoginTime($uid){
        MemberShopInfo::where('member_id',$uid)->update(['last_login_time' => time()]);
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
        $type = \YunShop::request()->type ;

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

        $data['sms'] = $result;
        $data['mobile_login_code'] = \Setting::get('shop.member.mobile_login_code') ?: 0;
        $data['logo'] = !empty(\Setting::get('shop.shop')['logo']) ? yz_tomedia(\Setting::get('shop.shop')['logo']) : 0;
        $data['protocol_title'] = Protocol::uniacid()->value('title') ?: '平台用户协议';

        if (!is_null(\app\common\modules\shop\ShopConfig::current()->get('wechat_qrcode_config'))) {
            $class    = array_get(\app\common\modules\shop\ShopConfig::current()->get('wechat_qrcode_config'), 'class');
            $function = array_get(\app\common\modules\shop\ShopConfig::current()->get('wechat_qrcode_config'), 'function');
            $wechat_qrcode_config = $class::$function();
            $data['wechat_qrcode_config'] = [
                'is_open' => $wechat_qrcode_config['is_open'],
                'is_wechat_login' => $wechat_qrcode_config['is_wechat_login'],
                'callback' => $wechat_qrcode_config['callback'],
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
