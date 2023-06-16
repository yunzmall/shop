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
use app\common\events\member\RegisterByMobile;
use app\common\exceptions\AppException;
use app\common\helpers\Url;
use app\common\services\Session;
use app\common\models\MemberGroup;
use app\frontend\models\Member;
use app\frontend\models\MemberShopInfo;
use app\frontend\modules\member\models\MemberModel;
use app\frontend\modules\member\models\MemberUniqueModel;
use app\frontend\modules\member\models\SubMemberModel;
use Illuminate\Support\Str;
use Yunshop\LspWalletMiddleground\models\MidMemberModel;
use Yunshop\LspWalletMiddleground\models\PushLogModel;
use Yunshop\LspWalletMiddleground\models\PushParamRecordModel;
use Yunshop\LspWalletMiddleground\models\SettingModel;
use Yunshop\LspWalletMiddleground\services\PushService;

class MemberAppLspWalletService extends MemberService
{
    public $uniacid;
    const LOGIN_TYPE = 18;

    public function __construct()
    {
        $this->uniacid = \YunShop::app()->uniacid;
    }

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
        $wallet_site = \YunShop::request()->wallet_site;
        $data = ['first_login' => 0];
        $pushType = 1;
        $is_register = false;//是否注册

        if (\Request::isMethod('post')) {
            if (!app('plugins')->isEnabled('love-speed-pool')) {
                return show_json(8, '爱心值加速池插件未开启');
            }

            $set = SettingModel::getConfig();

            //密码登录
            if (!empty($password)) {
                $log_type = 1;//手机号
                MemberService::validate($mobile, $password);
                $remain_time = $this->getLoginLimit($mobile);
                if($remain_time){
                    return show_json(6, "账号锁定中，请".$remain_time."分钟后再登录");
                }
                $has_mobile = MemberModel::checkMobile($this->uniacid, $mobile);

                if (!empty($has_mobile)) {
                    $password = md5($password . $has_mobile->salt);

                    $member_info = MemberModel::getUserInfo($this->uniacid, $mobile, $password)->first();
                    if (!$member_info) {
                        $error_count = $this->setLoginLimit($mobile);
                        if ($error_count > 0) {
                            return show_json(6, "密码错误！你还剩" . $error_count . "次机会");
                        } else {
                            return show_json(6, "密码错误次数已达5次，您的账号已锁定，请30分钟之后登录！");
                        }
                    }
                    $member_info = $member_info->toArray();
                } else {
                    return show_json(7, '用户不存在');
                }
            }

            //验证码登录 member.register.alySendCode&mobile=&captcha=&sms_type=1
            if (!empty($code)) {
                $log_type = 1;//手机号
                $data['mobile'] = $mobile;
                $data['code'] = $code;

                self::validate($data);
                $check_code = MemberService::checkAppCode();

                if ($check_code['status'] != 1) {
                    return show_json('6',$check_code['json']);
                }
                $member_info = MemberModel::checkMobile($this->uniacid, $data['mobile']);

                if (empty($member_info)) {
                    $is_register = true;
                    $member_info = $this->register($data);
                }
                if(!empty($member_info)){
                    $member_info = $member_info->toArray();
                } else {
                    return show_json(6, "手机号或验证码错误");
                }
            }

            //钱包地址登录
            if ($wallet_site) {
                $log_type = 2;//地址
                if (!\Setting::get('plugin.love_speed_pool.is_wallet_log')) {
                    return show_json(9, '未开启钱包登录');
                }
                //查询是否存在该钱包地址
                $walletSite = \Yunshop\LoveSpeedPool\model\WalletSite::where('uniacid',$this->uniacid)->where('wallet_site',$wallet_site)->first();
                $memberInfo = MemberModel::where('uid',$walletSite->uid)->first();
                if ($walletSite) {
                    //todo 登录
                    $member_info = $memberInfo->toArray();
                    $member_info['uid'] = $walletSite->uid;
                } else {
                    // todo 注册该登录方式
                    if (empty($memberInfo)) {
                        $data['wallet_site'] = $wallet_site;
                        $is_register = true;
                        $member_info = $this->register($data);

                        if(!empty($member_info)){
                            $member_info = $member_info->toArray();
                        } else {
                            return show_json(6, '钱包地址错误');
                        }
                    }

                }
            }

            if (!empty($member_info)) {
                $yz_member = MemberShopInfo::getMemberShopInfo($member_info['uid']);
                if (empty($yz_member)) {
                    return show_json(7, '用户不存在');
                }
                Session::set('member_id', $member_info['uid']);
                $data = MemberModel::userData($member_info, $yz_member);

                //没有钱包中台会员ID && 没有上级 则显示邀请码页面
                if (!MidMemberModel::uniacid()->where('uid',$member_info['uid'])->value('mid_uid') && empty($yz_member->parent_id)) {
                    $data['first_login'] = 1;//首次登录
                }

                //注册登录进行推送
//                $hasMidMember = MidMemberModel::where('uniacid',$this->uniacid)->select('id','mid_uid')->where('uid',$member_info['uid'])->first();
                //不存在中台会员ID && 注册操作
//                if (!$hasMidMember && $is_register) {
//
//                    //手机号
//                    if ($log_type == 1) {
//                        $pushParam = [
//                            'from_id' => $set->merchant_id,//商户ID
//                            'mobile' => $mobile,
//                            'nickname' => $member_info['nickname'],
//                            'avatar_url' => $member_info['avatar'],
//                        ];
//
//                        $paramRecordArr = [
//                            'uniacid' => $this->uniacid,
//                            'mobile' => $mobile,
//                            'push_type' => $pushType,
//                        ];
//
//                    } elseif ($log_type == 2) {
//                        $pushParam = [
//                            'from_id' => $set->merchant_id,//商户ID
//                            'address' => $wallet_site,//注册钱包地址
//                            'nickname' => $member_info['nickname'],
//                            'avatar_url' => $member_info['avatar']
//                        ];
//
//                        $paramRecordArr = [
//                            'uniacid' => $this->uniacid,
//                            'address' => $wallet_site,
//                            'push_type' => $pushType,
//                        ];
//                    }
//
//                    if (!empty($pushParam) && !empty($paramRecordArr)) {
//                        if ($member_info['parent_id']) {
//                            $parent_mobile = Member::where('uniacid',$this->uniacid)->where('uid',$member_info['parent_id'])->value('mobile') ?: '';
//                            $pushParam['re_mobile'] = $parent_mobile ?: '';//推送-推荐人手机号
//                            $paramRecordArr['parent_mobile'] = $parent_mobile ?: '';//记录-推荐人手机号
//
//                            $parentWalletSite = \Yunshop\LoveSpeedPool\model\WalletSite::where('uniacid',$this->uniacid)->where('uid',$member_info['parent_id'])->value('wallet_site');
//                            $pushParam['re_address'] = $parentWalletSite ?: '';//推送-推荐人地址
//                            $paramRecordArr['parent_address'] = $parentWalletSite ?: '';//记录-推荐人地址
//                        }
//
//                        //注册推送
//                        $result = PushService::regist($pushParam);
//
//                        //记录推送参数表
//                        $paramRecord = PushParamRecordModel::addLog($paramRecordArr);
//
//                        //返回200且返回值不为空值
//                        $pushStatus = $result['code'] == 200 && !empty($result['content']) ? 1 : -1;
//
//                        //记录推送表
//                        PushLogModel::addLog([
//                            'uniacid' => $this->uniacid,
//                            'uid' => $member_info['uid'],
//                            'param' => $pushParam,
//                            'result' => $result,
//                            'type' => $pushType,
//                            'status' => $pushStatus,
//                            'retry_num' => 0,
//                            'param_record_id' => $paramRecord->id,//参数表ID
//                        ]);
//
//                        //记录钱包中台会员ID
//                        if ($pushStatus == 1 && !empty($result['content'])) {
//                            $midMember = new MidMemberModel();
//                            $midMember->fill([
//                                'uniacid' => $this->uniacid,
//                                'uid' => $member_info['uid'],
//                                'mid_uid' => $result['content'],
//                                'merchant_id' => $set->merchant_id
//                            ]);
//                            $midMember->save();
//                        }
//                    }
//                }

                return show_json(1, '',$data);
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
        return MemberService::isLogged();
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
            'mobile' => $data['mobile'] ?: '',
            'groupid' => $array['default_groupid']->id ? $array['default_groupid']->id : 0,
            'createtime' => $_SERVER['REQUEST_TIME'],
            'nickname' => $data['mobile'] ?: '',
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

        if ($data['wallet_site']) {
            $member = MemberModel::where('uid',$array['member_id'])->first();

            //绑定钱包地址
            \Yunshop\LoveSpeedPool\model\WalletSite::saveWalletSite([
                'uniacid' => $this->uniacid,
                'uid' => $array['member_id'],
                'wallet_site' => $data['wallet_site'],
            ]);
        } else {
            $member = MemberModel::checkMobile($this->uniacid, $data['mobile']);
        }
        \Log::debug('钱包注册',[$data,$array['member_id'],$data['mobile'],$array['data']['password']]);

        event(new \app\common\events\member\RegisterMember(0, $array['member_id']));
        if ($data['mobile']) {
            \Log::debug('钱包注册-手机号注册');
            $member_info = MemberModel::getUserInfo($this->uniacid, $data['mobile'], $array['data']['password'])->first();
            event(new RegisterByMobile($member_info));
        }

        return $member;
    }

    public function updateMemberInfo($member_id, $userinfo)
    {
        if (request()->input('route') == 'member.member.bindMobile') {
            parent::updateMemberInfo($member_id, $userinfo);
        }
    }

    public function addMemberInfo($uniacid, $userinfo)
    {
        return parent::addMemberInfo($uniacid, $userinfo);
    }

    /**
     * 会员关联表操作
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
}