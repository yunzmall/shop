<?php

namespace app\platform\controllers;

use app\common\exceptions\TokenHasExpiredException;
use app\common\services\txyunsms\SmsSingleSender;
use app\frontend\modules\member\services\MemberService;
use app\platform\modules\user\models\AdminUser;
use app\platform\modules\user\models\YzUserProfile;
use app\common\helpers\Cache;
use iscms\Alisms\SendsmsPusher as Sms;
use app\frontend\modules\member\models\smsSendLimitModel;
use app\platform\modules\system\models\SystemSetting;
use app\common\services\aliyun\AliyunSMS;
use Mews\Captcha\Captcha;
use Gregwar\Captcha\PhraseBuilder;
use Gregwar\Captcha\CaptchaBuilder;
use app\common\helpers\Url;

class ResetpwdController extends BaseController
{
    public function SendCode()
    {
        $mobile = request()->mobile;
        $username = request()->username; //账号
        $state = \YunShop::request()->state ?: '86';

        if (empty($mobile)) {
            return $this->errorJson('请填入手机号');
        }

        if ($username) {
            $user = AdminUser::where('username', $username)->with('hasOneProfile')->first();
            if (!$user) {
                return $this->errorJson('账号不存在');
            }

            $user = $user->toArray();
            //判断账号
            if ($user['has_one_profile']['mobile'] == $mobile) { //管理员
                return $this->send($mobile, $state);
            }

            if (\Schema::hasTable('yz_store')) //门店
            {
                $plugins_mobile = \DB::table('yz_store')->where('user_uid', $user['uid'])->value('mobile'); //门店
                // $plugins_mobile = \DB::table('yz_store_apply')->where('uid',$member_id)->value('mobile'); //门店
                if ($plugins_mobile == $mobile) {
                    return $this->send($mobile, $state);
                }
            }

            if (\Schema::hasTable('yz_hotel')) {
                $plugins_mobile = \DB::table('yz_hotel')->where('user_uid', $user['uid'])->value('mobile');       //酒店
                if ($plugins_mobile == $mobile) {
                    return $this->send($mobile, $state);
                }
            }

            if (\Schema::hasTable('yz_area_dividend_agent')) {//区域分红
                $plugins_mobile = \DB::table('yz_area_dividend_agent')->where('user_id', $user['uid'])->value('mobile');
                if ($plugins_mobile == $mobile) {
                    return $this->send($mobile, $state);
                }
            }

            if (\Schema::hasTable('yz_supplier')) {//供应商
                $plugins_mobile = \DB::table('yz_supplier')->where('uid', $user['uid'])->value('mobile');
                if ($plugins_mobile == $mobile) {
                    return $this->send($mobile, $state);
                }
            }

            if (\Schema::hasTable('yz_package_deliver')) {//自提点
                $plugins_mobile = \DB::table('yz_package_deliver')->where('user_uid', $user['uid'])->value('deliver_mobile');
                if ($plugins_mobile == $mobile) {
                    return $this->send($mobile, $state);
                }
            }

            if (\Schema::hasTable('yz_subsidiary')) { //分公司
                $plugins_mobile = \DB::table('yz_subsidiary')->where('user_uid', $user['uid'])->value('mobile');
                if ($plugins_mobile == $mobile) {
                    return $this->send($mobile, $state);
                }
            }
        } else {
            $uid = $this->checkUserOnMobile($mobile);
            if (!$uid) {
                return $this->errorJson('该手机号不存在');
            }
        }


        return $this->errorJson('该手机号不存在');
    }

    public function send($mobile, $state)
    {
        $code = rand(1000, 9999);

        //检查次数及是否正确
        if (!MemberService::smsSendLimit(\YunShop::app()->uniacid ?: 0, $mobile)) {
            return $this->errorJson('发送短信数量达到今日上限');
        } else {
            return $this->sendSmsV2($mobile, $code, $state);
        }
    }

    public function checkCode()
    {
        $mobile = request()->mobile;
        $code = request()->code;

        //检查验证码是否正确
        $check_code = app('sms')->checkAppCode($mobile, $code);

        if ($check_code['status'] != 1) {
            return $this->errorJson($check_code['json']);
        }

        return $this->successJson('验证成功');
    }

    public function detail()
    {
        $setting = SystemSetting::settingLoad('sms', 'system_sms');

        if (!$setting) {
            return $this->errorJson('暂无数据');
        }
        return $this->successJson('获取成功', $setting);
    }

    public function getCaptcha()
    {
        $setting = SystemSetting::settingLoad('sms');

        if ($setting['status'] != 1) {
            return $this->errorJson('请开启图形验证码验证');
        }
        $phrase = new PhraseBuilder();
        $code = $phrase->build(4);
        $builder = new CaptchaBuilder($code, $phrase);

        $builder->setBackgroundColor(150, 150, 150);
        $builder->setMaxAngle(25);
        $builder->setMaxBehindLines(0);
        $builder->setMaxFrontLines(0);

        $builder->build($width = 100, $height = 40, $font = null);
        $phrase = $builder->getPhrase();

        \Session::flash('code', $phrase);

        // header('Cache-Control: no-cache, must-revalidate');
        header('Content-Type: image/jpeg');
        $builder->output();
    }

    public function changePwd()
    {
        $username = request()->username;
        $pwd = request()->pwd;
        $mobile = request()->mobile;
        $confirm_password = \YunShop::request()->confirm_password;
        $loginset = SystemSetting::settingLoad('loginset', 'system_loginset');

        if ($loginset['password_verify'] == 1) {
            $validatePassword = validatePassword($pwd);
            if ($validatePassword !== true) {
                return $this->errorJson($validatePassword);
            }
        }

        $msg = $this->validate($mobile, $pwd, $confirm_password);

        if ($msg != 1) {
            if (isset($msg['json'])) {
                return $this->errorJson($msg['json']);
            } elseif ($msg) {
                return $this->errorJson($msg);
            }
            return $this->errorJson('未知道错误');
            // return $this->errorJson($msg['json']);
        }

        if ($username) {
            $user = AdminUser::where('username', $username)->with('hasOneProfile')->first();

            if (!$user->uid || $user->hasOneProfile->mobile != $mobile) {
                return $this->errorJson('该用户不存在');
            }

            $uid = $user->uid;

            $res = $this->modify($pwd, $uid);

            if ($res) {
                return $this->successJson('密码修改成功');
            }
        }

        return $this->errorJson('修改密码失败');
    }

    private function checkUserOnMobile($mobile)
    {
        $member_info = YzUserProfile::where('mobile', $mobile)->first();

        if ($member_info) {
            return $member_info['uid'];
        }
        return false;
    }

    public function validate($mobile, $pwd, $confirm_password)
    {
        if ($confirm_password == '') {
            $data = array(
                'mobile' => $mobile,
                'password' => $pwd,
            );
            $rules = array(
                'mobile' => 'regex:/^1\d{10}$/',
                'password' => 'required|min:6|regex:/^[A-Za-z0-9@!#\$%\^&\*+]+$/',
            );
            $message = array(
                'regex' => ':attribute 格式错误',
                'required' => ':attribute 不能为空',
                'min' => ':attribute 最少6位'
            );
            $attributes = array(
                "mobile" => '手机号',
                'password' => '密码',
            );
        } else {
            $data = array(
                'mobile' => $mobile,
                'password' => $pwd,
                'confirm_password' => $confirm_password,
            );
            $rules = array(
                'mobile' => 'regex:/^1\d{10}$/',
                'password' => 'required|min:6|regex:/^[A-Za-z0-9@!#\$%\^&\*+]+$/',
                'confirm_password' => 'same:password',
            );
            $message = array(
                'regex' => ':attribute 格式错误',
                'required' => ':attribute 不能为空',
                'min' => ':attribute 最少6位',
                'same' => ':attribute 不匹配'
            );
            $attributes = array(
                "mobile" => '手机号',
                'password' => '密码',
                'confirm_password' => '密码',
            );
        }

        $validate = \Validator::make($data, $rules, $message, $attributes);
        if ($validate->fails()) {
            $warnings = $validate->messages();
            $show_warning = $warnings->first();

            return $show_warning;
        } else {
            return 1;
        }
    }

    public function sendSmsV2($mobile, $code, $state, $templateType = 'reg', $sms_type = 2)
    {
        if (2 == $sms_type) {
            $sms = app('sms')->sendPwd($mobile, $state, 1);
        } elseif (3 == $sms_type) {
            $sms = app('sms')->sendLog($mobile, $state, 1);
        } else {
            $sms = app('sms')->sendCode($mobile, $state, 1);
        }

        if (0 == $sms['status']) {
            return $this->errorJson($sms['json']);
        }

        return $this->successJson();
    }

    /**
     * 管理员修改密码
     */
    public function authPassword()
    {
        $auth = config('app.AUTH_PASSWORD');
        $auth_request = request()->auth;
        $is_ok = false;

        if ($auth_request == $auth && $auth != '') {
            $is_ok = true;
            $user_request = request()->user;
            if (!empty($user_request['username']) && !empty($user_request['password'])) {
                $user = $this->getUser($user_request['username']);
                if (!$user) {
                    return $this->message('用户名不存在', '/index.php/admin/auth');
                }

                $res = $this->modify($user_request['password'], $user->uid);
                if ($res) {
                    (new LoginController)->logout();
                    return $this->message('密码修改成功', '/');
                }
                return $this->error('修改密码失败', '/index.php/admin/auth');
            }
        }

        return view('platform.auth', [
            'is_ok' => $is_ok,
            'auth' => $auth
        ])->render();
    }

    public function getUser($username)
    {
        return AdminUser::where('username', $username)->first();
    }

    public function modify($pwd, $uid)
    {
        $data['password'] = bcrypt($pwd);

        $res = AdminUser::where('uid', $uid)->update($data);

        return $res;
    }
}