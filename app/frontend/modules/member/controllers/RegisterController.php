<?php
/**
 * Created by PhpStorm.
 * Author: 芸众商城 www.yunzshop.com
 * Date: 17/2/22
 * Time: 上午11:56
 */

namespace app\frontend\modules\member\controllers;

use app\backend\modules\charts\modules\phone\models\PhoneAttribution;
use app\backend\modules\charts\modules\phone\services\PhoneAttributionService;
use app\common\components\ApiController;
use app\common\events\member\MemberBindMobile;
use app\common\events\member\RegisterByMobile;
use app\common\helpers\Client;
use app\common\helpers\Url;
use app\common\models\Address;
use app\common\models\Member;
use app\common\models\member\MemberInvitationCodeLog;
use app\common\models\MemberAlipay;
use app\common\models\MemberGroup;
use app\common\models\MemberLevel;
use app\common\models\MemberShopInfo;
use app\common\modules\sms\SmsService;
use app\common\services\aliyun\AliyunSMS;
use app\common\services\Session;
use app\common\services\txyunsms\SmsSingleSender;
use app\framework\Http\Request;
use app\frontend\modules\member\models\MemberMiniAppModel;
use app\frontend\modules\member\models\MemberModel;
use app\frontend\modules\member\models\SubMemberModel;
use app\frontend\modules\member\models\MemberWechatModel;
use app\frontend\modules\member\services\MemberPluginSmsService;
use app\frontend\modules\member\services\MemberService;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Str;
use iscms\Alisms\SendsmsPusher as Sms;
use app\common\exceptions\AppException;
use Mews\Captcha\Captcha;
use app\common\facades\Setting;
use app\common\services\alipay\OnekeyLogin;
use app\common\models\McMappingFans;


class RegisterController extends ApiController
{
    protected $publicController = ['Register'];
    protected $publicAction = ['index', 'sendCode', 'sendCodeV2', 'checkCode', 'sendSms', 'changePassword', 'getInviteCode', 'chkRegister', 'alySendCode','appSendCode'];
    protected $ignoreAction = ['index', 'sendCode', 'sendCodeV2', 'checkCode', 'sendSms', 'changePassword', 'getInviteCode', 'chkRegister', 'alySendCode','appSendCode'];

    public function index()
    {
        $mobile = \YunShop::request()->mobile;
        $code = \YunShop::request()->code;
        $password = \YunShop::request()->password;
        $confirm_password = \YunShop::request()->confirm_password;
        $customDatas = \YunShop::request()->customDatas;
        $address = \YunShop::request()->address;
        $birthday = \YunShop::request()->birthday;
        $gender = \YunShop::request()->gender;
        $custom_value = \YunShop::request()->custom_value;
        $uniacid = \YunShop::app()->uniacid;
        $systemType = \YunShop::app()->system_type;

        if ((\Request::getMethod() == 'POST')) {

            $check_code = app('sms')->checkCode($mobile, $code);

            if ($check_code['status'] != 1) {
                return $this->errorJson($check_code['json']);
            }

            $invite_code = MemberService::inviteCode();

            if ($invite_code['status'] != 1) {
                return $this->errorJson($invite_code['json']);
            }

            $register = Setting::get('shop.register');
            if (isset($register['is_password']) && $register['is_password'] == 0) {
                $password = '';
            } else {
                $msg = MemberService::validate($mobile, $password, $confirm_password);

                if ($msg['status'] != 1) {
                    return $this->errorJson($msg['json']);
                }
            }

            $member_info = MemberModel::getId($uniacid, $mobile);

            if (!empty($member_info)) {
                return $this->errorJson('该手机号已被注册');
            }

            //添加mc_members表
            $default_groupid = MemberGroup::getDefaultGroupId($uniacid)->first();

            $member_set = \Setting::get('shop.member');

            if (isset($member_set) && $member_set['headimg']) {
                $avatar = replace_yunshop(tomedia($member_set['headimg']));
            } else {
                $avatar = Url::shopUrl('static/images/photo-mr.jpg');
            }

            if ($birthday) {
                $birthday = explode('-', $birthday);
            }

            $data = array(
                'uniacid' => $uniacid,
                'mobile' => $mobile,
                'groupid' => $default_groupid->id ? $default_groupid->id : 0,
                'createtime' => time(),
                'nickname' => $mobile,
                'avatar' => $avatar,
                'gender' => $gender?:0,
                'birthyear' => $birthday[0]?:0,
                'birthmonth' => $birthday[1]?:0,
                'birthday' => $birthday[2]?:0,
                'residecity' => '',
            );
            $data['salt'] = Str::random(8);

            $data['password'] = md5($password . $data['salt']);
            $memberModel = MemberModel::create($data);
            $member_id = $memberModel->uid;

            //添加yz_member表
            $default_sub_group_id = MemberGroup::getDefaultGroupId()->first();

            if (!empty($default_sub_group_id)) {
                $default_subgroup_id = $default_sub_group_id->id;
            } else {
                $default_subgroup_id = 0;
            }

            $customDatas['customDatas'] = $customDatas;
            //自定义表单
            $member_form = (new MemberService())->updateMemberForm($customDatas);
            if (!empty($member_form)) {
                $member_form = json_encode($member_form);
            }

            $sub_data = array(
                'member_id' => $member_id,
                'uniacid' => $uniacid,
                'group_id' => $default_subgroup_id,
                'level_id' => 0,
                'invite_code' => \app\frontend\modules\member\models\MemberModel::generateInviteCode(),
                'member_form' => $member_form,
                'province' => $address['province']?:'',
                'city' => $address['city']?:'',
                'area' => $address['area']?:'',
                'province_name' => $address['province_name']?:'',
                'city_name' => $address['city_name']?:'',
                'area_name' => $address['area_name']?:'',
                'address' => $address['address']?:'',
                'custom_value' => $custom_value,
                'system_type' => $systemType,
            );

            SubMemberModel::insertData($sub_data);
            //生成分销关系链
            Member::createRealtion($member_id);

            $cookieid = "__cookie_yun_shop_userid_{$uniacid}";
            Cookie::queue($cookieid, $member_id);
            Session::set('member_id', $member_id);

            $password = $data['password'];
            $member_info = MemberModel::getUserInfo($uniacid, $mobile, $password)->first();
            $yz_member = MemberShopInfo::getMemberShopInfo($member_id)->toArray();

            $data = MemberModel::userData($member_info, $yz_member);

            //app注册添加member_wechat表中数据
            $type = \YunShop::request()->type;
            if ($type == 7) {
                $uuid = \YunShop::request()->uuid;
                MemberWechatModel::insertData(array(
                    'uniacid' => $uniacid,
                    'member_id' => $member_info['uid'],
                    'openid' => $member_info['mobile'],
                    'nickname' => $member_info['nickname'],
                    'gender' => $member_info['gender'],
                    'avatar' => $member_info['avatar'],
                    'province' => $member_info['resideprovince'],
                    'city' => $member_info['residecity'],
                    'country' => $member_info['nationality'],
                    'uuid' => $uuid
                ));
            }

            if(\YunShop::request()->positioning_success == 1)
            {
                \Log::info($member_id.'注册定位' . \YunShop::app()->uniacid);
                if (!is_null(\app\common\modules\shop\ShopConfig::current()->get('set_location'))) {
                    $class    = array_get(\app\common\modules\shop\ShopConfig::current()->get('set_location'), 'class');
                    $function = array_get(\app\common\modules\shop\ShopConfig::current()->get('set_location'), 'function');
                    $class::$function($member_id,\Yunshop\RegistrationArea\Common\models\MemberLocation::TYPE_REGISTER,\YunShop::request()->register_province,\YunShop::request()->register_city);
                }
            }
            if (app('plugins')->isEnabled('share-reward')){
                event(new \app\common\events\member\RegisterMember(0, $member_id));
            }
            event(new RegisterByMobile($member_info));
            return $this->successJson('', $data);
        } else {
            return $this->errorJson('手机号或密码格式错误');
        }
    }

    public function newApiData()
    {

        $request = request();
        $this->dataIntegrated($this->getInviteCode($request, true),'getInviteCode');
        $this->dataIntegrated(\app\frontend\controllers\SettingController::getRegisterDiyForm($request, true),'get_register_diy_form');
        $this->dataIntegrated(\app\frontend\controllers\SettingController::getMemberProtocol($request, true),'get_member_protocol');
        $this->dataIntegrated(\app\frontend\controllers\HomePageController::getCaptcha($request, true),'get_captcha');
        if (app('plugins')->isEnabled('diyform') && $this->apiData['get_register_diy_form']['status'] == 1) {
            $this->dataIntegrated(\Yunshop\Diyform\api\DiyFormController::getDiyFormById($request, true, $this->apiData['get_register_diy_form']['form_id']),'get_diy_form_by_id');
        }
//        获取后台开启会员自定义字段设置
        $set = \Setting::get('shop.form');
        $set = json_decode($set, true);
        $this->apiData['form_open'] = $set['base']['form_open'];
        if (empty($this->apiErrMsg)) {
            return $this->successJson('', $this->apiData);
        } else {
            return $this->errorJson($this->apiErrMsg[0]);
        }


    }
    /**
     * 发送短信验证码
     *
     *
     */
    public function sendCode()
    {
        $mobile = \YunShop::request()->mobile;

        $reset_pwd = \YunShop::request()->reset;

        $state = \YunShop::request()->state;

        if (empty($mobile)) {
            return $this->errorJson('请填入手机号');
        }

        $info = MemberModel::getId(\YunShop::app()->uniacid, $mobile);

        if (!empty($info) && empty($reset_pwd)) {
            return $this->errorJson('该手机号已被注册！不能获取验证码');
        }
        $sms = app('sms')->sendCode($mobile, $state);

        if(0 == $sms['status']){
            return $this->errorJson($sms['json']);
        }

        return $this->successJson();
    }

    public function alySendCode()
    {
        $mobile = \YunShop::request()->mobile;

        $state = \YunShop::request()->state ?: '86';

        $sms_type = \YunShop::request()->sms_type;

        if (empty($mobile)) {
            return $this->errorJson('请填入手机号');
        }

        $type = \YunShop::request()->type;
        if (empty($type)) {
            $type = Client::getType();
        }

        if(2 == $sms_type){
            $sms = app('sms')->sendPwd($mobile, $state,0);
        }elseif(3 == $sms_type){
            $sms = app('sms')->sendLog($mobile, $state,0);
        }else{
            $sms = app('sms')->sendCode($mobile, $state,0);
        }

        if(0 == $sms['status']){
            return $this->errorJson($sms['json']);
        }

        return $this->successJson();

    }

    public function appSendCode()
    {
        $mobile = \YunShop::request()->mobile;

        $state = \YunShop::request()->state ?: '86';

        if (empty($mobile)) {
            return $this->errorJson('请填入手机号');
        }

        $sms = app('sms')->sendLog($mobile, $state);

        if(0 == $sms['status']){
            return $this->errorJson($sms['json']);
        }

        return $this->successJson();
    }
    public function sendCodeV2()
    {
        $mobile = \YunShop::request()->mobile;
        $reset_pwd = \YunShop::request()->reset;
        $state = \YunShop::request()->state ?: '86';
        $sms_type = \YunShop::request()->sms_type;
        if (empty($mobile)) {
            return $this->errorJson('请填入手机号');
        }
        $type = \YunShop::request()->type;
        if (empty($type)) {
            $type = Client::getType();
        }
        if (Setting::get('shop.sms.status')) {
            $captcha = request()->captcha;
            if (!$captcha) {
                return $this->errorJson('图形验证码不能为空');
            }
            if (!app('captcha')->check($captcha)) {
                return $this->errorJson('图形验证码错误');
            }
        }
        //微信登录绑定已存在的手机号
        $member_info = MemberModel::getId(\YunShop::app()->uniacid, $mobile);
        if ($type == 1) {
            if (!empty($member_info['uid'])) {
                $fans_info = McMappingFans::getFansById($member_info['uid']);
                if ($fans_info && empty($reset_pwd)) {
                    return $this->errorJson('该手机号已被绑定！不能获取验证码');
                }
            }
        }
        //小程序登录绑定已存在的手机号
        if ($type == 2) {
            if (!empty($member_info['uid'])) {
                $fans_info = MemberMiniAppModel::getFansById($member_info['uid']);
                if ($fans_info && empty($reset_pwd)) {
                    return $this->errorJson('该手机号已被绑定！不能获取验证码');
                }
            }
        }
        //app登录绑定已存在的手机号
        if ($type == 7) {
            if (!empty($member_info['uid'])) {
                $fans_info = MemberWechatModel::getFansById($member_info['uid']);
                if ($fans_info && empty($reset_pwd)) {
                    return $this->errorJson('该手机号已被绑定！不能获取验证码');
                }
            }
        }
        //支付宝登录绑定已存在的手机号
        if ($type == 8) {
            if (!empty($member_info['uid'])) {
                $fans_info = MemberAlipay::getFansById($member_info['uid']);
                if ($fans_info && empty($reset_pwd)) {
                    return $this->errorJson('该手机号已被绑定！不能获取验证码');
                }
            }
        }
        if ($type == 5) {
            if (!empty($member_info) && empty($reset_pwd)) {
                return $this->errorJson('该手机号已被注册！不能获取验证码');
            }
        }

        try {
            if (2 == $sms_type) {
                $sms = app('sms')->sendPwd($mobile, $state);
            } elseif (3 == $sms_type) {
                $sms = app('sms')->sendLog($mobile, $state);
            } else {
                $sms = app('sms')->sendCode($mobile, $state);
            }
        } catch (\Exception $e) {
            return $this->errorJson('请检查后台短信配置');
        }

        if (0 == $sms['status']) {
            return $this->errorJson('请检查后台短信配置');
        }

        return $this->successJson();

    }

    public function sendWithdrawCode()
    {
        $mobile = \YunShop::request()->mobile;
        $reset_pwd = \YunShop::request()->reset;

        if (empty($mobile)) {
            return $this->errorJson('请填入手机号');
        }
        $sms = app('sms')->sendCode($mobile);

        if(0 == $sms['status']){
            return $this->errorJson($sms['json']);
        }

        return $this->successJson();
    }

    /**
     * 发送短信
     *
     * @param $mobile
     * @param $code
     * @param string $templateType
     * @return array|mixed
     */
    public function sendSmsV2($mobile, $code, $state, $templateType = 'reg', $sms_type = 1)
    {
        //增加验证码验证
        $captcha_status = Setting::get('shop.sms.status');
        if ($captcha_status == 1) {
            if (app('captcha')->check(Input::get('captcha')) == false) {
                return $this->errorJson('图形验证码错误');
            }
        }

        $sms = app('sms')->sendCode($mobile, $state);

        if(0 == $sms['status']){
            return $this->errorJson($sms['json']);
        }

        return $this->successJson();

    }


    /**
     * 短信验证
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkCode()
    {
        $mobile = \YunShop::request()->mobile;
        $uniacid = \YunShop::app()->uniacid;

        $check_code = MemberService::checkCode();
        $member_info = MemberModel::getId($uniacid, $mobile);

        if (empty($member_info)) {
            return $this->errorJson('手机号不存在');
        }

        if ($check_code['status'] != 1) {
            return $this->errorJson($check_code['json']);
        }

        return $this->successJson('ok');
    }

    /**
     * 修改密码
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function changePassword()
    {
        $mobile = \YunShop::request()->mobile;
        $password = \YunShop::request()->password;
        $confirm_password = \YunShop::request()->confirm_password;
        $uniacid = \YunShop::app()->uniacid;
        $code = \YunShop::request()->code;
        if ((\Request::getMethod() == 'POST')) {

            $check_code = app('sms')->checkCode($mobile, $code);

            if ($check_code['status'] != 1) {
                return $this->errorJson($check_code['json']);
            }

            $msg = MemberService::validate($mobile, $password, $confirm_password);

            if ($msg['status'] != 1) {
                return $this->errorJson($msg['json']);
            }

            $member_info = MemberModel::getId($uniacid, $mobile);

            if (empty($member_info)) {
                return $this->errorJson('该手机号不存在');
            }

            //更新密码
            $data['salt'] = Str::random(8);
            $data['password'] = md5($password . $data['salt']);

            MemberModel::updataData($member_info->uid, $data);
            $member_id = $member_info->uid;

            $password = $data['password'];
            $member_info = MemberModel::getUserInfo($uniacid, $mobile, $password)->first();
            $yz_member = MemberShopInfo::getMemberShopInfo($member_id)->toArray();

            $data = MemberModel::userData($member_info, $yz_member);

            return $this->successJson('修改密码成功', $data);
        } else {
            return $this->errorJson('手机号或密码格式错误');
        }
    }

    public function getInviteCode(Request $request, $integrated = null)
    {
        $close = \YunShop::request()->close;
        $required = intval(\Setting::get('shop.member.required'));
        //  $is_invite = Member::chkInviteCode();
        $is_invite = intval(\Setting::get('shop.member.is_invite'));
        $mid = \YunShop::request()->get('mid');
        $member_id = \YunShop::app()->getMemberId();
        $default_invite = \Setting::get('shop.member.default_invite');//默认邀请码
        if($is_invite == 1){
            if($mid == $member_id){
                $invitation_code = '';
            }else{
                $invitation_code = MemberShopInfo::select('invite_code')->where('member_id',$mid)->first();
            }
        }
        // 国家区号是否显示
        $country_code = 0; // 默认关闭
        $sms = \Setting::get('shop.sms');
        if (isset($sms['country_code'])) {
            $country_code = $sms['country_code'];
        }

        if (isset($close) && 1 == $close) {
            $is_invite = 0;
            $required = 0;
        }

        $data = [
            'status' => $is_invite,
            'required' => $required,
            'country_code' => $country_code,
            'invitation_code'=>$invitation_code,
            'default_invite' => $default_invite ?: '',
        ];
        if(is_null($integrated)){
            return $this->successJson('返回数据成功',$data);
        }else{
            return show_json(1,$data);
        }
    }

    public function chkRegister()
    {
        $member = Setting::get('shop.member');
        $shop_reg_close = !empty($member['get_register']) ? $member['get_register'] : 0;
        $app_reg_close = 0;
        $msg = $member["Close_describe"] ?: '注册已关闭';//关闭原因
        $list = [];
        //$list['state']= $shop_reg_close;
        $list['state'] = $list['state'] = $shop_reg_close;
        if (!is_null($app_set = \Setting::get('shop_app.pay')) && 0 == $app_set['phone_oauth']) {
            $app_reg_close = 1;
        }

        if (($shop_reg_close && !Client::is_app()) || ($app_reg_close && Client::is_app())) {
            $list['reason'] = $msg;
            return $this->errorJson('失败', $list);

        }
        return $this->successJson('返回数据成功',$list);

    }
}
