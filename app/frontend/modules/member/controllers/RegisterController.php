<?php
/**
 * Created by PhpStorm.
 * Author:
 * Date: 17/2/22
 * Time: 上午11:56
 */

namespace app\frontend\modules\member\controllers;

use app\backend\modules\charts\modules\phone\models\PhoneAttribution;
use app\backend\modules\charts\modules\phone\services\PhoneAttributionService;
use app\common\components\ApiController;
use app\common\events\member\MemberBindMobile;
use app\common\events\member\RegisterByMobile;
use app\common\exceptions\MemberNotLoginException;
use app\common\exceptions\ShopException;
use app\common\facades\RichText;
use app\common\helpers\Cache;
use app\common\helpers\Client;
use app\common\helpers\Url;
use app\common\models\Address;
use app\common\models\Member;
use app\common\models\member\MemberInvitationCodeLog;
use app\common\models\MemberAlipay;
use app\common\models\MemberGroup;
use app\common\models\MemberLevel;
use app\common\models\MemberShopInfo;
use app\common\models\Protocol;
use app\common\modules\sms\SmsService;
use app\common\services\aliyun\AliyunSMS;
use app\common\services\Session;
use app\common\services\txyunsms\SmsSingleSender;
use app\framework\Http\Request;
use app\frontend\modules\member\models\MemberMiniAppModel;
use app\frontend\modules\member\models\MemberModel;
use app\frontend\modules\member\models\MemberUniqueModel;
use app\frontend\modules\member\models\SubMemberModel;
use app\frontend\modules\member\models\MemberWechatModel;
use app\frontend\modules\member\services\factory\MemberFactory;
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
    protected $publicAction = ['newApiData', 'index', 'sendCode', 'sendCodeV2', 'checkCode', 'sendSms', 'changePassword', 'getInviteCode', 'chkRegister', 'alySendCode', 'appSendCode','registerPage','register'];
    protected $ignoreAction = ['newApiData', 'index', 'sendCode', 'sendCodeV2', 'checkCode', 'sendSms', 'changePassword', 'getInviteCode', 'chkRegister', 'alySendCode', 'appSendCode','registerPage','register'];

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
        $realname = request()->input('realname', '');
        $set = json_decode(\Setting::get('shop.form'), true);

        if ((\Request::getMethod() == 'POST')) {
            if ($set['base']['name_must'] == 1 && empty($realname)) {
                return $this->errorJson('请填写姓名');
            }
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
                'realname' => request()->input('realname', ''),
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

            if(\YunShop::request()->positioning_success == 1)
            {
                \Log::info($member_id.'注册定位' . \YunShop::app()->uniacid);
                if (!is_null(\app\common\modules\shop\ShopConfig::current()->get('set_location'))) {
                    $class    = array_get(\app\common\modules\shop\ShopConfig::current()->get('set_location'), 'class');
                    $function = array_get(\app\common\modules\shop\ShopConfig::current()->get('set_location'), 'function');
                    $class::$function($member_id,\Yunshop\RegistrationArea\Common\models\MemberLocation::TYPE_REGISTER,\YunShop::request()->register_province,\YunShop::request()->register_city);
                }
            }
            event(new \app\common\events\member\RegisterMember(0, $member_id));
            event(new RegisterByMobile($member_info));
            return $this->successJson('', $data);
        } else {
            return $this->errorJson('手机号或密码格式错误');
        }
    }

    /**
     * 注册（new） todo 旧注册接口保留
     * @return \Illuminate\Http\JsonResponse
     */
    public function register()
    {
        try {
            $uniacid = \YunShop::app()->uniacid;
            if (request()->getMethod() != 'POST') {
                throw new \Exception('手机号或密码格式错误');
            }
            $request = request()->all();
            list($data,$sub_data) = $this->registerVerify($request);
            \Log::debug('---新注册---',[$data,$sub_data]);

            //添加mc_members表
            $memberModel = new MemberModel();
            $memberModel->fill($data);
            //todo 模型可批量赋值不包含生日相关字段，这里手动赋值上去
            $memberModel->birthyear = $data['birthyear'];
            $memberModel->birthmonth = $data['birthmonth'];
            $memberModel->birthday = $data['birthday'];
            $memberModel->realname = $data['realname'];
            if (!$memberModel->save()) {
                throw new \Exception('注册失败');
            }
            $member_id = $memberModel->uid;
            //添加yz_member表
            $sub_data['member_id'] = $member_id;
            SubMemberModel::insertData($sub_data);
            //生成分销关系链
            Member::createRealtion($member_id);

            $cookieid = "__cookie_yun_shop_userid_{$uniacid}";
            Cookie::queue($cookieid, $member_id);
            Session::set('member_id', $member_id);

            $password = $data['password'];
            $member_info = MemberModel::getUserInfo($uniacid, $data['mobile'], $password)->first();
            $yz_member = MemberShopInfo::getMemberShopInfo($member_id)->toArray();

            $data = MemberModel::userData($member_info, $yz_member);

            if(request()->positioning_success == 1)
            {
                \Log::info($member_id.'注册定位' . \YunShop::app()->uniacid);
                if (!is_null(\app\common\modules\shop\ShopConfig::current()->get('set_location'))) {
                    $class    = array_get(\app\common\modules\shop\ShopConfig::current()->get('set_location'), 'class');
                    $function = array_get(\app\common\modules\shop\ShopConfig::current()->get('set_location'), 'function');
                    $class::$function($member_id,\Yunshop\RegistrationArea\Common\models\MemberLocation::TYPE_REGISTER,request()->register_province,request()->register_city);
                }
            }
            event(new \app\common\events\member\RegisterMember(0, $member_id));
            event(new RegisterByMobile($member_info));
            return $this->successJson('', $data);
        } catch (\Exception $e) {
            return $this->errorJson($e->getMessage());
        }
    }

    /**
     * 注册信息验证
     * @param $request
     * @return array[]
     * @throws \Exception
     */
    private function registerVerify($request)
    {
        $uniacid = \YunShop::app()->uniacid;
        $formSet = json_decode(Setting::get('shop.form'),true);
        if ($formSet['base']['basic_register']) {
            //基础信息-注册填写
            if ($formSet['base']['name'] && $formSet['base']['name_must'] && !$request['name']) {
                throw new \Exception('请填写姓名');
            }
            if ($formSet['base']['sex'] && $formSet['base']['sex_must'] && !$request['gender']) {
                throw new \Exception('请填写性别');
            }
            if ($formSet['base']['address'] && $formSet['base']['address_must'] && !$request['address']) {
                throw new \Exception('请填写详细地址');
            }
            if ($formSet['base']['birthday'] && $formSet['base']['birthday_must'] && !$request['birthday']) {
                throw new \Exception('请填写生日');
            }
        }
        //验证码
        $check_code = app('sms')->checkCode($request['mobile'], $request['code']);
        if ($check_code['status'] != 1) {
            throw new \Exception($check_code['json']);
        }
        //邀请码
        $invite_code = MemberService::inviteCode();
        if ($invite_code['status'] != 1) {
            throw new \Exception($invite_code['json']);
        }

        //手机号&密码
        $validateData = array(
            'mobile' => $request['mobile'],
        );
        $validateRules = array(
            'mobile' => 'required|numeric',
        );
        $validateMessage = array(
            'regex' => ':attribute 格式错误',
            'required' => ':attribute 不能为空',
            'number' => ':attribute 格式错误',
            'min' => ':attribute 最少6位'
        );
        $validateAttributes = array(
            "mobile" => '手机号',
            'password' => '密码',
        );
        $registerSet = Setting::get('shop.register');
        $password = '';
        if (!isset($registerSet['is_password']) || $registerSet['is_password']) {
            $password = $request['password'];
            $validateData['password'] = $password;
            $validateRules['password'] = 'required|min:6|regex:/^[A-Za-z0-9@!#\$%\^&\*]+$/';
        }
        $validate = \Validator::make($validateData, $validateRules, $validateMessage, $validateAttributes);
        if ($validate->fails()) {
            $warnings = $validate->messages();
            $show_warning = $warnings->first();
            throw new \Exception($show_warning?:'手机号或密码格式错误');
        }
        $member_info = MemberModel::getId($uniacid, $request['mobile']);
        if (!empty($member_info)) {
            throw new \Exception('该手机号已被注册');
        }

        $memberSet = Setting::get('shop.member');
        //自定义字段-固定
        $custom_value = $request['custom_value'] ? : '';
        if (!$memberSet['is_custom'] || !$memberSet['is_custom_register']) {
            $custom_value = '';
        }
        //自定义字段
        $member_form = '';
        if ($formSet['base']['form_register']) {//注册填写开启
            $member_form = $form = array_values(array_sort($formSet['form'],function ($value) {
                return $value['sort'];
            }));
            foreach ($form as $key => &$item) {
                $item['del'] = 1;
                $member_form[$key]['value'] = $request['customDatas'][$item['pinyin']];
                if ($formSet['base']['form_open'] && !$member_form[$key]['value']) {
                    throw new \Exception('自定义字段必填');
                }
            }
            $formSet['form'] = $form;
            Setting::set('shop.form', json_encode($formSet));
            unset($item);
            $member_form = $member_form ? json_encode($member_form) : '';
        }

        //添加mc_members表
        if (isset($member_set) && $member_set['headimg']) {
            $avatar = replace_yunshop(tomedia($member_set['headimg']));
        } else {
            $avatar = Url::shopUrl('static/images/photo-mr.jpg');
        }
        $birthday = $request['birthday'] ? explode('-', $request['birthday']) : [];

        $default_group = MemberGroup::getDefaultGroupId($uniacid)->first();
        $member = [
            'uniacid' => \YunShop::app()->uniacid,
            'mobile'  =>  $request['mobile'],
            'groupid' => $default_group->id ? : 0,
            'createtime' => time(),
            'nickname' => $request['mobile'],
            'realname' => $request['name'] ? : '',
            'avatar' => $avatar,
            'gender' => $request['gender'] ? : 0,
            'birthyear' => $birthday ? $birthday[0] : 0,
            'birthmonth' => $birthday ? $birthday[1] : 0,
            'birthday' => $birthday ? $birthday[2] : 0,
            'residecity' => '',
            'salt' => Str::random(8),
        ];
        $member['password'] = md5($password . $member['salt']);

        //添加yz_member表
        $default_sub_group = MemberGroup::getDefaultGroupId()->first();
        $yz_member = [
            'uniacid' => $uniacid,
            'group_id' => $default_sub_group ? $default_sub_group->id : 0,
            'level_id' => 0,
            'invite_code' => \app\frontend\modules\member\models\MemberModel::generateInviteCode(),
            'member_form' => $member_form,
            'province' => $request['address']['province'] ? : '',
            'city' => $request['address']['city']?:'',
            'area' => $request['address']['area']?:'',
            'province_name' => $request['address']['province_name']?:'',
            'city_name' => $request['address']['city_name']?:'',
            'area_name' => $request['address']['area_name']?:'',
            'address' => $request['address']['address']?:'',
            'custom_value' => $custom_value,
            'system_type' => $request['system_type'],
        ];
        return [$member,$yz_member];
    }

    public function newApiData()
    {
        if (!miniVersionCompare('1.1.137') || !versionCompare('1.1.137')) {
            return $this->newApiData1();
        }

        return $this->newApiData2();
    }

    public function newApiData1()
    {
        $request = request();
        $this->dataIntegrated($this->getInviteCode($request, true),'getInviteCode');
        $this->dataIntegrated(\app\frontend\controllers\SettingController::getRegisterDiyForm($request, true),'get_register_diy_form');
        $this->dataIntegrated(\app\frontend\controllers\SettingController::getMemberProtocol($request, true),'get_member_protocol');
        $this->apiData['get_captcha'] = $this->getCaptcha();
        if (app('plugins')->isEnabled('diyform') && $this->apiData['get_register_diy_form']['status'] == 1) {
            $this->dataIntegrated(\Yunshop\Diyform\api\DiyFormController::getDiyFormById($request, true, $this->apiData['get_register_diy_form']['form_id']),'get_diy_form_by_id');
        }
//        获取后台开启会员自定义字段设置
        $set = \Setting::get('shop.form');
        $set = json_decode($set, true);
        $this->apiData['form_open'] = $set['base']['form_open'];
        $this->apiData['name_must'] = !isset($set['base']['name_must']) ? 0 : $set['base']['name_must'];
        if (empty($this->apiErrMsg)) {
            return $this->successJson('', $this->apiData);
        } else {
            return $this->errorJson($this->apiErrMsg[0]);
        }

    }

    public function newApiData2()
    {
        if (Client::is_weixin() && Setting::get('shop.member')['wechat_login_mode'] != '1') { //非手机号登陆
            return $this->successJson('', $this->redirectUrl());
        }

        $request = request();
        $this->dataIntegrated($this->getInviteCode($request, true),'getInviteCode');
        $this->dataIntegrated(\app\frontend\controllers\SettingController::getRegisterDiyForm($request, true),'get_register_diy_form');
        $this->dataIntegrated(\app\frontend\controllers\SettingController::getMemberProtocol($request, true),'get_member_protocol');
        $this->apiData['get_captcha'] = $this->getCaptcha();
        if (app('plugins')->isEnabled('diyform') && $this->apiData['get_register_diy_form']['status'] == 1) {
            $this->dataIntegrated(\Yunshop\Diyform\api\DiyFormController::getDiyFormById($request, true, $this->apiData['get_register_diy_form']['form_id']),'get_diy_form_by_id');
        }
//        获取后台开启会员自定义字段设置
        $set = \Setting::get('shop.form');
        $set = json_decode($set, true);
        $this->apiData['form_open'] = $set['base']['form_open'];
        $this->apiData['name_must'] = !isset($set['base']['name_must']) ? 0 : $set['base']['name_must'];
        if (empty($this->apiErrMsg)) {
            return $this->successJson('', $this->apiData);
        } else {
            return $this->errorJson($this->apiErrMsg[0]);
        }
    }

    /**
     * todo 新版注册页信息接口
     * @return \Illuminate\Http\JsonResponse
     * @throws MemberNotLoginException
     * @throws \app\common\exceptions\ShopException
     */
    public function registerPage()
    {
        $memberSet = Setting::get('shop.member');
        if (Client::is_weixin() &&  $memberSet['wechat_login_mode'] != '1') { //非手机号登陆
            return $this->successJson('', $this->redirectUrl());
        }
        $returnData = [];
        $shop_setting = Setting::get('shop.shop');
        $returnData['name'] = $shop_setting['name'] ? : '商城';
        $registerSet = Setting::get('shop.register');
        $returnData['getInviteCode'] = $this->getInviteCode(request(), true);
        //引导标题
        $returnData['title1'] = $registerSet['title1'] ? : '欢迎来到['.$returnData['name'].']';
        $returnData['title2'] = $registerSet['title2'] ? : '登录尽享各种优惠权益！';
        $returnData['is_password'] = (!isset($registerSet['is_password']) || $registerSet['is_password']) ? 1 : 0;
        $returnData['top_img'] = $registerSet['top_img'] ? yz_tomedia($registerSet['top_img']) : '';
        $returnData['get_captcha'] = $this->getCaptcha();
        //基本信息&自定义字段
        $returnData['register_basic_info'] = $this->registerBasicInfo();
        $returnData['fixed_diy_field'] = $this->fixedDiyField();
        $returnData['diy_field'] = $this->diyField() ?: null;
        $returnData['diy_form'] = $this->diyForm() ?: null;

        $returnData['get_register_diy_form'] =  [
            'form_id' => $memberSet['form_id'] ? : 0,
            'status' => (app('plugins')->isEnabled('diyform') && $memberSet['form_id_register'] && $memberSet['form_id']) ? 1 : 0,
        ];

        //协议
        $protocol = Protocol::uniacid()->first();
        $returnData['agreement'] = [
            'status' => $protocol->status ? : 0,
            'title'  => $protocol->title ? : "会员注册协议",
            'default_tick'  => $protocol->default_tick ? : 0,
            'content'  => $protocol->content ? : ""
        ];

        $shopSet = Setting::get('shop.shop');
        $agreement = RichText::get('shop.agreement');
        $returnData['platform_agreement'] = [
            'status' => $shopSet['is_agreement'] ? 1 : 0,
            'title'  => $shopSet['agreement_name'] ? : "平台协议",
            'content'  => $agreement ? : ""
        ];

        return $this->successJson('', $returnData);
    }

    /**
     * 新绑定手机号页面接口-member.register.bindApiData接口需废弃
     * @return \Illuminate\Http\JsonResponse
     */
    public function bindMobilePage()
    {
        $member_id = \YunShop::app()->getMemberId();
        $member = MemberModel::getUserInfos_v2($member_id)->first();
        $returnData = [
            'mobile' => $member['mobile']
        ];
        $registerSet = Setting::get('shop.register');
        $returnData['is_password'] = $registerSet['is_password'] ? : 0;
        $returnData['top_img'] = $registerSet['top_img'] ? yz_tomedia($registerSet['top_img']) : '';
        $returnData['get_captcha'] = $this->getCaptcha();
        //基本信息&自定义字段
        $returnData['register_basic_info'] = $this->registerBasicInfo($member);
        $returnData['fixed_diy_field'] = $this->fixedDiyField($member);
        $returnData['diy_field'] = $this->diyField($member);
        $returnData['diy_form'] = $this->diyForm();

        $memberSet = Setting::get('shop.member');
        $returnData['get_register_diy_form'] =  [
            'form_id' => $memberSet['form_id'] ? : 0,
            'status' => (app('plugins')->isEnabled('diyform') && $memberSet['form_id_register'] && $memberSet['form_id']) ? 1 : 0,
        ];

        //协议
        $shopSet = Setting::get('shop.shop');
        $agreement = RichText::get('shop.agreement');
        $returnData['agreement'] = [
            'status' => $shopSet['is_agreement'] ? : 0,
            'title'  => $shopSet['agreement_name'] ? : "平台用户协议",
            'content'  => $agreement ? : ""
        ];
        return $this->successJson('', $returnData);
    }

    private function registerBasicInfo($member=[])
    {
        $formSet = json_decode(Setting::get('shop.form'),true);
        $show = (bool)$formSet['base']['basic_register'];
        $returnData[] = [
            'name' => '姓名',
            'field' => 'name',
            'must' => (int)$formSet['base']['name_must'] ?: 0,
            'value' => $member['realname'] ? : '',
            'show' => ($show && $formSet['base']['name'])
        ];
        $returnData[] = [
            'name' => '性别',
            'field' => 'gender',
            'must' => (int)$formSet['base']['sex_must'] ? 1 : 0,
            'value' => $member['gender'] ? : 0,
            'show' => ($show && $formSet['base']['sex'])
        ];
        $returnData[] = [
            'name' => '详细地址',
            'field' => 'address',
            'must' => (int)$formSet['base']['address_must'] ? 1 : 0,
            'value' => [
                'province' => $member['yz_member']['province'] ? : '',
                'city' => $member['yz_member']['city'] ? : '',
                'area' => $member['yz_member']['area'] ? : '',
                'province_name' => $member['yz_member']['province_name'] ? : '',
                'city_name' => $member['yz_member']['city_name'] ? : '',
                'area_name' => $member['yz_member']['area_name'] ? : '',
                'address' => $member['yz_member']['address'] ? : '',
            ],
            'show' => ($show && $formSet['base']['address'])
        ];
        $returnData[] = [
            'name' => '生日',
            'field' => 'birthday',
            'must' => (int)$formSet['base']['birthday_must'] ? 1 : 0,
            'value' => [
                'birthyear' => $member['birthyear'] ? : '',
                'birthmonth' => $member['birthmonth'] ? : '',
                'birthday' => $member['birthday'] ? : '',
            ],
            'show' => ($show && $formSet['base']['birthday'])
        ];
        return $returnData;
    }

    private function fixedDiyField($member=[])
    {
        $memberSet = Setting::get('shop.member');
        if (!$memberSet['is_custom_register'] || !$memberSet['is_custom']) {
            return [];
        }
        return [
            [
                'name' => $memberSet['custom_title'] ? : "",
                'field' => 'custom_value',
                'must' => 1,
                'value' => $member['yz_member']['custom_value'] ? : '',
            ]
        ];
    }

    private function diyField($member=[])
    {
        $formSet = json_decode(Setting::get('shop.form'),true);
        if (!$formSet['base']['form_register']) {
            return [];
        }
        $form = array_values(array_sort($formSet['form'], function ($value) {
            return $value['sort'];
        }));
        $returnData = [
            'form_edit' => $formSet['base']['form_edit'] ? 1 : 0,
            'form_open' => $formSet['base']['form_open'] ? 1 : 0,
            'form' => []
        ];
        $member_form = $member['yz_member']['member_form']?json_decode($member['yz_member']['member_form'],true):[];
        $member_form = collect($member_form);
        foreach ($form as $item) {
            $has = $member_form->where('pinyin',$item['pinyin'])->first();
            $returnData['form'][] = [
                'name' => $item['name'],
                'field' => $item['pinyin'],
                'value' => $has ? $has['value'] : '',
            ];
        }
        return $returnData;
    }

    private function diyForm()
    {
        $memberSet = Setting::get('shop.member');
        if (!app('plugins')->isEnabled('diyform') || !$memberSet['form_id_register'] || !$memberSet['form_id']) {
            return [];
        }
        $data = \Yunshop\Diyform\api\DiyFormController::getDiyFormById(request(), true,  $memberSet['form_id']);
        $fields = [];
        foreach ($data['json']->form_type as $key => $item) {
            $item['tp_title'] = $key;
            $fields[] = $item;
        }
        $data['json'] = $data['json']->toArray();
        $data['json']['form_type'] = $fields;
        return $data['json'] ? : [];
    }

    public function bindApiData()
    {
        $request = request();
        $this->dataIntegrated($this->getInviteCode($request, true),'getInviteCode');
        $this->dataIntegrated(\app\frontend\controllers\SettingController::getRegisterDiyForm($request, true),'get_register_diy_form');
        $this->dataIntegrated(\app\frontend\controllers\SettingController::getMemberProtocol($request, true),'get_member_protocol');
        $this->apiData['get_captcha'] = $this->getCaptcha();
        if (app('plugins')->isEnabled('diyform') && $this->apiData['get_register_diy_form']['status'] == 1) {
            $this->dataIntegrated(\Yunshop\Diyform\api\DiyFormController::getDiyFormById($request, true, $this->apiData['get_register_diy_form']['form_id']),'get_diy_form_by_id');
        }
//        获取后台开启会员自定义字段设置
        $set = \Setting::get('shop.form');
        $set = json_decode($set, true);
        $this->apiData['form_open'] = $set['base']['form_open'];
        $this->apiData['name_must'] = !isset($set['base']['name_must']) ? 0 : $set['base']['name_must'];
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
        $state = \YunShop::request()->state ?: '86';
        if (empty($mobile)) {
            return $this->errorJson('请填入手机号');
        }
        try {
            MemberService::mobileValidate([
                'mobile' => $mobile,
                'state' => $state,
            ]);
        } catch (ShopException $exception) {
            return $this->errorJson($exception->getMessage());
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
        try {
            MemberService::mobileValidate([
                'mobile' => $mobile,
                'state' => $state,
            ]);
        } catch (ShopException $exception) {
            return $this->errorJson($exception->getMessage());
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
        try {
            MemberService::mobileValidate([
                'mobile' => $mobile,
                'state' => $state,
            ]);
        } catch (ShopException $exception) {
            return $this->errorJson($exception->getMessage());
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
        try {
            MemberService::mobileValidate([
                'mobile' => $mobile,
                'state' => $state,
            ]);
        } catch (ShopException $exception) {
            return $this->errorJson($exception->getMessage());
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
        $unique_info = MemberUniqueModel::getUnionidInfoByMemberId(\YunShop::app()->uniacid, $member_info['uid'])->first();
        $fans_info = McMappingFans::getFansById($member_info['uid']);
        $mini_info = MemberMiniAppModel::getFansById($member_info['uid']);
        $wechat_info = MemberWechatModel::getFansById($member_info['uid']);
        $ali_info = MemberAlipay::getFansById($member_info['uid']);
        if ($type!=8&&($unique_info||$fans_info||$mini_info||$wechat_info)&&!$reset_pwd) {
            return $this->errorJson('该手机号已被绑定！不能获取验证码');
        }
        if ($type==8&&$ali_info&&!$reset_pwd) {
            return $this->errorJson('该手机号已被注册！不能获取验证码');
        }
        if ($type==5&&!request()->scope&&$member_info&&!$reset_pwd) { //request()->scope tjpcps
            return $this->errorJson('该手机号已被注册！不能获取验证码');
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
            return $this->errorJson($sms['json']);
        }
        return $this->successJson();
    }

    public function sendWithdrawCode()
    {
        $mobile = \YunShop::request()->mobile;
        $state = \YunShop::request()->state ?: '86';
        if (empty($mobile)) {
            return $this->errorJson('请填入手机号');
        }
        try {
            MemberService::mobileValidate([
                'mobile' => $mobile,
                'state' => $state,
            ]);
        } catch (ShopException $exception) {
            return $this->errorJson($exception->getMessage());
        }
        $sms = app('sms')->sendCode($mobile);
        if (0 == $sms['status']) {
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
//        $confirm_password = \YunShop::request()->confirm_password;
        $uniacid = \YunShop::app()->uniacid;
        $code = \YunShop::request()->code;
        if ((\Request::getMethod() == 'POST')) {

            $check_code = app('sms')->checkCode($mobile, $code);

            if ($check_code['status'] != 1) {
                return $this->errorJson($check_code['json']);
            }

            $msg = MemberService::validate($mobile, $password);

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
        $is_invite = intval(\Setting::get('shop.member.is_invite'));
        $mid = \YunShop::request()->get('mid');
        $member_id = \YunShop::app()->getMemberId();
        $default_invite = \Setting::get('shop.member.default_invite');//默认邀请码
        if($is_invite == 1){
            $up_yz_member = MemberShopInfo::select(['invite_code','status','is_agent'])->where('member_id',$mid)->first();
            if($mid == $member_id || ($up_yz_member->status != 2 && $up_yz_member->is_agent != 1)){
                $invitation_code = '';
            }else{
                $invitation_code = $up_yz_member;
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

    private function getCaptcha()
	{
		//增加验证码功能
		$status = \Setting::get('shop.sms.status');
		if ($status == 1) {
			$result['captcha'] = app('captcha')->create('default', true);
			$result['captcha']['status'] = $status;
		} else {
			$result['captcha']['status'] = $status;
		}
		return $result;
	}

    /**
     * @throws MemberNotLoginException
     * @throws \app\common\exceptions\ShopException
     */
    private function redirectUrl()
    {
        $client = 1;
        $uniacid = \YunShop::app()->uniacid;
        $member = MemberFactory::create($client);

        if ($member->checkLogged()) {
            return ['status' => 'redirec', 'url' => Url::absoluteApp('member/editmobile', ['i' => $uniacid, 'mid' => Member::getMid()])];
        } else {
            throw new MemberNotLoginException('请登录', $_SERVER['QUERY_STRING']);
        }
    }
}
