<?php
/**
 * Created by PhpStorm.
 * User: dingran
 * Date: 2019/7/9
 * Time: 上午11:03
 */

namespace app\frontend\modules\member\services;


use app\backend\modules\charts\modules\phone\models\PhoneAttribution;
use app\backend\modules\charts\modules\phone\services\PhoneAttributionService;
use app\common\exceptions\AppException;
use app\common\facades\Setting;
use app\common\helpers\Client;
use app\common\helpers\Url;
use app\common\models\AccountWechats;
use app\common\models\McMappingFans;
use Yunshop\AggregationCps\models\MemberAggregationAppModel;
use app\common\models\MemberGroup;
use app\common\models\Store;
use app\common\services\api\WechatApi;
use app\frontend\models\Member;
use app\frontend\models\MemberShopInfo;
use app\frontend\modules\member\models\McMappingFansModel;
use app\frontend\modules\member\models\MemberModel;
use app\frontend\modules\member\models\MemberUniqueModel;
use app\frontend\modules\member\models\MemberWechatModel;
use app\frontend\modules\member\models\SubMemberModel;
use Crypt;
use Illuminate\Support\Str;
use Yunshop\AggregationCps\services\CommonService;
use Yunshop\Commission\models\Agents;

class MemberCpsAppService extends MemberService
{
    public $uniacid;
    const LOGIN_TYPE = 15;

    /**
     * @return array
     * @throws AppException
     * @throws \app\common\exceptions\MemberErrorMsgException
     */
    public function login()
    {
        $mobile   = \YunShop::request()->mobile;
        $password = \YunShop::request()->password;
        $code = \YunShop::request()->code;
        $weixin_code = \YunShop::request()->weixin_code;
        $this->uniacid  = \YunShop::app()->uniacid;

        $uniacid = \YunShop::app()->uniacid;

        if (\Request::isMethod('post')) {
            if (!app('plugins')->isEnabled('aggregation-cps')) {
                return show_json(8, "聚合cps插件未开启");
            }
            //密码登录
            if (!empty($password)) {
                MemberService::validate($mobile, $password);

                $has_mobile = MemberModel::checkMobile($uniacid, $mobile);

                if (!empty($has_mobile)) {
                    $password = md5($password . $has_mobile->salt);

                    $member_info = MemberModel::getUserInfo($uniacid, $mobile, $password)->first();
                    if(empty($member_info)){
                        return show_json(6, "手机号或密码错误");
                    }
                    $member_info = $member_info->toArray();
                } else {
                    return show_json(7, '用户不存在');
                }

            }
            //验证码登录 member.register.alySendCode&mobile=&captcha=&sms_type=1
            if (!empty($code)) {
                $data = [
                    'mobile' => $mobile,
                    'code' => $code,
                ];
                self::validate($data);
                $check_code = MemberService::checkAppCode();

                if ($check_code['status'] != 1) {
                    return show_json('6',$check_code['json']);
                }
                $member_info = MemberModel::checkMobile($this->uniacid, $data['mobile']);

                if (empty($member_info)) {
                    $member_info = $this->register($data);
                }
                if(!empty($member_info)){
                    $member_info = $member_info->toArray();
                } else {
                    return show_json(6, "手机号或验证码错误");
                }
            }
            //微信授权登录
            if ($weixin_code) {
                $set = \Setting::get('plugin.aggregation-cps');

                $tokenurl = $this->_getTokenUrl($set['weixin_appid'], $set['weixin_secret'], $weixin_code);
                $token = \Curl::to($tokenurl)
                    ->asJsonResponse(true)
                    ->get();

                if (!empty($token) && !empty($token['errmsg']) && $token['errmsg'] == 'invalid code') {
                    return show_json(5, 'token请求错误');
                }

                $userinfo = $this->getUserInfo($set['weixin_appid'], $set['weixin_secret'], $token);

                if (is_array($userinfo) && !empty($userinfo['errcode'])) {
                    \Log::debug('微信登陆授权失败-', $userinfo);
                    return show_json(5, '微信登陆授权失败');
                }
                $member_id = $this->memberLogin($userinfo);
                $member_info['uid'] = $member_id;

            }

            if (!empty($member_info)) {
                $yz_member = MemberShopInfo::getMemberShopInfo($member_info['uid']);
                if (!empty($yz_member)) {
                    if (!$yz_member->access_token_2) {
                        $data['token'] = Client::create_token('yz');
                        $yz_member->access_token_2 = $data['token'];
                        $yz_member->save();
                    }
                    $set = \Setting::get('plugin.aggregation-cps');
                    $data['token'] = $yz_member->access_token_2;
                    $data['uid'] = $yz_member->member_id;
                    $data['shop_name'] = \Setting::get('shop.shop.name') ?: '未设置商城名称';
                    $data['ratio'] = (float)CommonService::getBuyRatio($set);
                    $data['ratio_name'] = CommonService::getBuyName($set);
                    $data['ratio_type'] = $set['buy_show'];
                    $data['download_url'] = $set['download_url'];
                    if (app('plugins')->isEnabled('commission')){
                        $agent = Agents::uniacid()->where('member_id', $yz_member->member_id)->first();
                    }
                    $data['agent_ratio'] = empty($agent) ? 0 : (float)$set['commission']['rule']['level_'.$agent->agent_level_id]['first_level_rate'];

                } else {
                    return show_json(7, '用户不存在');
                }

                return show_json(1, '', $data);
            }

        } else {
            return show_json(6, '手机号或密码错误');
        }
    }

    /**
     * 验证登录状态
     *
     * @return bool
     */
    public function checkLogged($login = null)
    {
        $token = \Yunshop::request()->yz_token;

        if (empty($token)) {
            return false;
        }

        $member = SubMemberModel::getMemberByNativeToken($token);
        \Log::debug('---------cps checkLogged--------', [$token, $member->member_id]);
        if (!is_null($member)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param $token
     * @return int
     * @throws AppException
     */
    public function getMemberId($token)
    {
        if (!$token) {
            return 0;
        }
        $member = SubMemberModel::getMemberByNativeToken($token);


        if (is_null($member)) {
            throw new AppException('token_invalid');

        }
        return $member->member_id;
    }

    public static function validate($data)
    {
        $data = array(
            'mobile' => $data['mobile'],
            'code' => $data['code'],
        );
        $rules = array(
            'mobile' => 'regex:/^1\d{10}$/',
            'code' => 'required|min:4|regex:/^[A-Za-z0-9@!#\$%\^&\*]+$/',
        );
        $message = array(
            'regex'    => ':attribute 格式错误',
            'required' => ':attribute 不能为空',
            'min' => ':attribute 最少4位'
        );
        $attributes = array(
            "mobile" => '手机号',
            'code' => '短信验证码',
        );

        $validate = \Validator::make($data,$rules,$message,$attributes);
        if ($validate->fails()) {
            $warnings = $validate->messages();
            $show_warning = $warnings->first();

            return show_json('0', $show_warning);
        } else {
            return show_json('1');
        }
    }

    //注册
    public function register($data)
    {
        $array = array();
        //获取分组
        $array['default_groupid']= MemberGroup::getDefaultGroupId()->first();
        $array['member_set'] = \Setting::get('shop.member');
        if (isset($array['member_set']) && $array['member_set']['headimg']) {
            $array['avatar'] = replace_yunshop(tomedia($array['member_set']['headimg']));
        } else {
            $array['avatar'] = Url::shopUrl('static/images/photo-mr.jpg');
        }
        $array['data'] = array(
            'uniacid' => $this->uniacid,
            'mobile' => $data['mobile'],
            'groupid' => $array['default_groupid']->id ? $array['default_groupid']->id : 0,
            'createtime' => $_SERVER['REQUEST_TIME'],
            'nickname' => $data['mobile'],
            'avatar' => $array['avatar'],
            'gender' => 0,
            'residecity' => '',
        );
        $array['data']['salt'] = Str::random(8);

        $array['data']['password'] = md5(str_random(8) . $data['salt']);
        $array['memberModel'] = MemberModel::create($array['data']);
        $array['member_id'] =  $array['memberModel']->uid;
        //手机归属地查询插入
        $array['phoneData'] = file_get_contents((new PhoneAttributionService())->getPhoneApi($data['mobile']));
        $array['phoneArray'] = json_decode($array['phoneData']);
        $array['phone']['uid'] = $array['member_id'];
        $array['phone']['uniacid'] = $this->uniacid;
        $array['phone']['province'] = $array['phoneArray']->data->province;
        $array['phone']['city'] = $array['phoneArray']->data->city;
        $array['phone']['sp'] = $array['phoneArray']->data->sp;
        $phoneModel = new PhoneAttribution();
        $phoneModel->updateOrCreate(['uid' => $data['mobile']], $array['phone']);
        //添加yz_member表
        $array['default_sub_group_id'] = MemberGroup::getDefaultGroupId()->first();

        if (!empty($array['default_sub_group_id'])) {
            $array['default_subgroup_id'] = $array['default_sub_group_id']->id;
        } else {
            $array['default_subgroup_id'] = 0;
        }
        $array['sub_data'] = array(
            'member_id' => $array['member_id'],
            'uniacid' => $this->uniacid,
            'group_id' => $array['default_subgroup_id'],
            'level_id' => 0,
            'invite_code' => \app\frontend\modules\member\models\MemberModel::getUniqueInviteCode(),
        );
        SubMemberModel::insertData($array['sub_data']);
        //生成分销关系链
        Member::createRealtion($array['member_id']);
        $member = MemberModel::checkMobile($this->uniacid, $data['mobile']);
        //dd($array);
        return $member;
    }


    /**
     * app获取用户信息并存储
     * @param $token
     * @param $openid
     * @param $uuid
     * @return int
     * @throws AppException
     * @throws \app\common\exceptions\MemberErrorMsgException
     */
    public function app_get_userinfo($token, $openid, $uuid)
    {
        //通过接口获取用户信息
        $url       = 'https://api.weixin.qq.com/sns/userinfo?access_token=' . $token . '&openid=' . $openid;
        $user_info = \Curl::to($url)
            ->asJsonResponse(true)
            ->get();

        if (!empty($uuid)) {
            $user_info['uuid'] = $uuid;
        }

        if (!empty($user_info)) {
            return $this->memberLogin($user_info);
        } else {
            throw new AppException('微信授权验证失败');
        }
    }

    /**
     * 获取用户信息
     *
     * @param $appId
     * @param $appSecret
     * @param $token
     * @return mixed
     */
    public function getUserInfo($appId, $appSecret, $token)
    {
        $scope     = \YunShop::request()->scope ?: '';
        $subscribe = 0;
        $share = Setting::get('shop.share');
        $user_info = [];

        if (is_null($share) || $share['follow'] == 1 || ($share && is_null($share['follow']))) {
            $global_access_token_url = $this->_getAccessToken($appId, $appSecret);

            $global_token = \Curl::to($global_access_token_url)
                ->asJsonResponse(true)
                ->get();

            $global_userinfo_url = $this->_getInfo($global_token['access_token'], $token['openid']);

            $user_info = \Curl::to($global_userinfo_url)
                ->asJsonResponse(true)
                ->get();

            $subscribe = $user_info['subscribe'];
        }

        if (0 == $subscribe && $scope != 'base') { //未关注拉取不到用户信息
            $userinfo_url = $this->_getUserInfoUrl($token['access_token'], $token['openid']);

            $user_info = \Curl::to($userinfo_url)
                ->asJsonResponse(true)
                ->get();

            $user_info['subscribe'] = $subscribe;
        }

        return array_merge($user_info, $token);
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
        return "https://api.weixin.qq.com/sns/oauth2/access_token?appid=" . $appId . "&secret=" . $appSecret . "&code=" . $code . "&grant_type=authorization_code";
    }

    /**
     * 获取用户信息 api
     *
     * 无需关注
     *
     * @param $accesstoken
     * @param $openid
     * @return string
     */
    private function _getUserInfoUrl($accesstoken, $openid)
    {
        return "https://api.weixin.qq.com/sns/userinfo?access_token={$accesstoken}&openid={$openid}&lang=zh_CN";
    }

    /**
     * 获取全局ACCESS TOKEN
     * @return string
     */
    private function _getAccessToken($appId, $appSecret)
    {
        return 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=' . $appId . '&secret=' . $appSecret;
    }

    /**
     * 获取用户信息
     *
     * 需要关注
     *
     * @param $accesstoken
     * @param $openid
     * @return string
     */
    private function _getInfo($accesstoken, $openid)
    {
        return 'https://api.weixin.qq.com/cgi-bin/user/info?access_token=' . $accesstoken . '&openid=' . $openid;
    }


    /**
     * 公众号开放平台授权登陆
     * @param $uniacid
     * @param $userinfo
     * @param null $upperMemberId
     * @return array|int|mixed
     * @throws AppException
     * @throws \app\common\exceptions\MemberErrorMsgException
     */
    public function unionidLogin($uniacid, $userinfo, $upperMemberId = NULL)
    {
        $member_id = parent::unionidLogin($uniacid, $userinfo, $upperMemberId = NULL, self::LOGIN_TYPE);

        return $member_id;
    }

    public function updateMemberInfo($member_id, $userinfo)
    {
        if (request()->input('route') == 'member.member.bindMobile') {
            parent::updateMemberInfo($member_id, $userinfo);
        }

        $record = array(
            'openid' => $userinfo['openid'],
            'nickname' => stripslashes($userinfo['nickname'])
        );

        if (request()->input('route') == 'member.member.bindMobile') {
            MemberAggregationAppModel::updateData($member_id, $record);
        }
    }

    public function addMemberInfo($uniacid, $userinfo)
    {
        $uid = parent::addMemberInfo($uniacid, $userinfo);

        //$this->addMcMemberFans($uid, $uniacid, $userinfo);
        $this->addFansMember($uid, $uniacid, $userinfo);

        return $uid;
    }

    public function addMcMemberFans($uid, $uniacid, $userinfo)
    {
        McMappingFansModel::insertData($userinfo, array(
            'uid' => $uid,
            'acid' => $uniacid,
            'uniacid' => $uniacid,
            'salt' => Client::random(8),
        ));
    }


    public function addFansMember($uid, $uniacid, $userinfo)
    {
        MemberAggregationAppModel::replace(array(
            'uniacid' => $uniacid,
            'member_id' => $uid,
            'openid' => $userinfo['openid'],
            'nickname' => $userinfo['nickname'],
            'avatar' => $userinfo['headimgurl'],
            'gender' => $userinfo['sex'],
        ));
    }

    public function getFansModel($openid)
    {
        $model = MemberAggregationAppModel::getUId($openid);

        if (!is_null($model)) {
            $model->uid = $model->member_id;
        }

        return $model;
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

    public function updateFansMember($fan, $member_id, $userinfo)
    {
        $record = array(
            'member_id' => $member_id,
            'nickname' => stripslashes($userinfo['nickname']),
            'avatar' => isset($userinfo['headimgurl']) ? $userinfo['headimgurl'] : '',
            'gender' => isset($userinfo['sex']) ? $userinfo['sex'] : '-1',
        );

        MemberAggregationAppModel::where('id', $fan->id)->update($record);
    }

}