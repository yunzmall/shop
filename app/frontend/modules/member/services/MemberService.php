<?php
/**
 * Created by PhpStorm.
 * Author:
 * Date: 17/2/28
 * Time: 上午5:16
 */

namespace app\frontend\modules\member\services;

use app\common\events\member\MergeMemberEvent;
use app\common\exceptions\AppException;
use app\common\exceptions\MemberErrorMsgException;
use app\common\exceptions\MemberNotLoginException;
use app\common\exceptions\ShopException;
use app\common\facades\Setting;
use app\common\helpers\Cache;
use app\common\helpers\Client;
use app\common\helpers\Url;
use app\common\models\McMappingFans;
use app\common\models\Member;
use app\common\models\member\MemberChangeLog;
use app\common\models\member\MemberDel;
use app\common\models\member\MemberMarkLog;
use app\common\models\member\MemberMerge;
use app\common\models\MemberAlipay;
use app\common\models\MemberGroup;
use app\common\models\MemberShopInfo;
use app\common\services\credit\ConstService;
use app\common\services\finance\BalanceChange;
use app\common\services\finance\PointService;
use app\common\services\member\MemberMergeService;
use app\common\services\Session;
use app\frontend\models\McGroupsModel;
use app\frontend\modules\member\models\McMappingFansModel;
use app\frontend\modules\member\models\MemberMiniAppModel;
use app\frontend\modules\member\models\MemberModel;
use app\frontend\modules\member\models\MemberUniqueModel;
use app\frontend\modules\member\models\MemberWechatModel;
use app\frontend\modules\member\models\smsSendLimitModel;
use app\frontend\modules\member\models\SubMemberModel;
use Illuminate\Support\Facades\Cookie;
use app\common\events\member\RegisterMember;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Schema;
use Yunshop\Love\Common\Models\MemberLove;
use Yunshop\Love\Common\Services\LoveChangeService;

class MemberService
{
    const TOKEN_EXPIRE = 2160000;

    /**
     * @var \app\frontend\models\Member
     */
    private static $_current_member;

    /**
     * @return \app\frontend\models\Member
     * @throws AppException
     */
    public static function getCurrentMemberModel()
    {
        if (isset(self::$_current_member)) {
            return self::$_current_member;
        }
        $uid = \YunShop::app()->getMemberId();
        if (!isset($uid)) {
            throw new AppException('uid不存在');
        }
        self::setCurrentMemberModel($uid);
        return self::$_current_member;
    }

    /**
     * @param $member_id
     * @throws AppException
     */
    public static function setCurrentMemberModel($member_id)
    {
        /**
         * @var \app\frontend\models\Member $member
         */
        $member = \app\frontend\models\Member::find($member_id);
        if (!isset($member)) {
            throw new AppException('(ID:' . $member_id . ')用户不存在');
        }
        self::$_current_member = $member;
    }

    /**
     * 用户是否登录
     *
     * @return bool
     */
    public static function isLogged()
    {
        if (\YunShop::app()->getMemberId()) {
            if (\app\frontend\models\Member::current()->hasOneDel) {
                MemberDel::delUpdate(\YunShop::app()->getMemberId());
                Session::clear('member_id');
            }
        }
        return \YunShop::app()->getMemberId() && \YunShop::app()->getMemberId() > 0;
    }

    /**
     * 验证手机号和密码
     *
     * @return bool
     */
    public static function validate($mobile, $password, $confirm_password = '')
    {
        //兼容港澳台手机号 把验证规则改为必填+纯数字
        if ($confirm_password == '') {
            $data = array(
                'mobile' => $mobile,
                'password' => $password,
            );
            $rules = array(
                'mobile' => 'required|numeric',
                'password' => 'required|min:6|regex:/^[A-Za-z0-9@.!#\$%\^&\*]+$/',
            );
            $message = array(
                'regex' => ':attribute 格式错误',
                'required' => ':attribute 不能为空',
                'number' => ':attribute 格式错误',
                'min' => ':attribute 最少6位'
            );
            $attributes = array(
                "mobile" => '手机号',
                'password' => '密码',
            );
        } else {
            $data = array(
                'mobile' => $mobile,
                'password' => $password,
                'confirm_password' => $confirm_password,
            );
            $rules = array(
                'mobile' => 'required|numeric',
                'password' => 'required|min:6|regex:/^[A-Za-z0-9@.!#\$%\^&\*]+$/',
                'confirm_password' => 'same:password',
            );
            $message = array(
                'regex' => ':attribute 格式错误',
                'required' => ':attribute 不能为空',
                'number' => ':attribute 格式错误',
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

            return show_json('0', $show_warning);
        } else {
            return show_json('1');
        }
    }

    public static function mobileValidate($validate_data = [])
    {
        if (!$validate_data['mobile']) {
            throw new ShopException('手机号为空');
        }
        if (!is_numeric($validate_data['mobile'])) {
            throw new ShopException('手机号格式错误');
        }
        if ($validate_data['state'] && !is_numeric($validate_data['state']) && mb_strlen($validate_data['state']) > 5) {
            throw new ShopException('国际号格式错误');
        }

        //没开启国家区号统一按 86 算
        if (!\Setting::get('shop.sms')['country_code'] || $validate_data['state'] == 86) {
            $pre_str = '/^(13[0-9]|14[01456879]|15[0-35-9]|16[2567]|17[0-8]|18[0-9]|19[0-35-9])\d{8}$/';
            $pre_res = preg_match($pre_str, $validate_data['mobile']);
            if (!$pre_res || mb_strlen($validate_data['mobile']) != 11) {
                throw new ShopException('手机号格式错误');
            }
        }
    }

    /**
     * 短信发送限制
     *
     * 每天最多5条
     */
    public static function smsSendLimit($uniacid, $mobile)
    {
        $curr_time = time();

        $mobile_info = smsSendLimitModel::getMobileInfo($uniacid, $mobile);

        if (!empty($mobile_info)) {
            $update_time = $mobile_info['created_at'];
            $total = $mobile_info['total'];

            if ((date('Ymd', $curr_time) != date('Ymd', $update_time))) {

                $total = 0;
            }
        } else {
            $total = 0;
        }

        if ($total < 5) {
            return true;
        } else {
            return false;
        }

    }

    /**
     * 更新发送短信条数
     *
     * 每天最多5条
     */
    public static function udpateSmsSendTotal($uniacid, $mobile)
    {
        $curr_time = time();

        $mobile_info = smsSendLimitModel::getMobileInfo($uniacid, $mobile);

        if (!empty($mobile_info)) {
            $update_time = $mobile_info['created_at'];
            $total = $mobile_info['total'];

            if ($update_time <= $curr_time) {
                if (date('Ymd', $curr_time) == date('Ymd', $update_time)) {
                    if ($total <= 5) {
                        ++$total;

                        smsSendLimitModel::updateData(array(
                            'uniacid' => $uniacid,
                            'mobile' => $mobile), array(
                            'total' => $total,
                            'created_at' => $curr_time));
                    }
                } else {
                    smsSendLimitModel::updateData(array(
                        'uniacid' => $uniacid,
                        'mobile' => $mobile), array(
                        'total' => 1,
                        'created_at' => $curr_time));
                }
            }
        } else {
            smsSendLimitModel::insertData(array(
                    'uniacid' => $uniacid,
                    'mobile' => $mobile,
                    'total' => 1,
                    'created_at' => $curr_time)
            );
        }
    }

    /**
     * 阿里大鱼
     *
     * @param $sms
     * @param $templateType
     * @return array
     */
    public static function send_sms_alidayu($sms, $templateType)
    {
        switch ($templateType) {
            case 'reg':
                $templateCode = $sms['templateCode'];
                $params = @explode("\n", $sms['product']);
                break;
            case 'forget':
                $templateCode = $sms['templateCodeForget'];
                $params = @explode("\n", $sms['forget']);
                break;
            case 'login':
                $templateCode = $sms['templateCodeLogin'];
                $params = @explode("\n", $sms['login']);
                break;
            default:
                $params = array();
                $templateCode = $sms['templateCode'];
                break;
        }
        return array('templateCode' => $templateCode, 'params' => $params);
    }

    /**
     * 互亿无线
     *
     * @param $account
     * @param $pwd
     * @param $mobile
     * @param $code
     * @param string $type
     * @param $name
     * @param $title
     * @param $total
     * @param $tel
     * @return mixed
     */
    public static function send_sms($account, $pwd, $mobile, $code, $type = 'check', $name = '', $title = '', $total = '', $tel = '')
    {
        if ($type == 'check') {
            $content = "您的验证码是：" . $code . "。请不要把验证码泄露给其他人。如非本人操作，可不用理会！";

        } elseif ($type == 'verify') {
            $verify_set = $sms = \Setting::get('shop.sms');
            $allset = iunserializer($verify_set['plugins']);
            if (is_array($allset) && !empty($allset['verify']['code_template'])) {
                $content = sprintf($allset['verify']['code_template'], $code, $title, $total, $name, $mobile, $tel);
            } else {
                $content = "提醒您，您的核销码为：" . $code . "，订购的票型是：" . $title . "，数量：" . $total . "张，购票人：" . $name . "，电话：" . $mobile . "，门店电话：" . $tel . "。请妥善保管，验票使用！";

            }

        }

        $smsrs = file_get_contents('http://106.ihuyi.cn/webservice/sms.php?method=Submit&account=' . $account . '&password=' . $pwd . '&mobile=' . $mobile . '&content=' . urldecode($content));
        return xml_to_array($smsrs);
    }

    public static function send_smsV2($account, $pwd, $mobile, $code, $state = '86', $type = 'check', $name = '', $title = '', $total = 0, $tel = '')
    {
        if ($type == 'check') {
            //$content = "您的验证码是：" . $code . "。请不要把验证码泄露给其他人。如非本人操作，可不用理会！";
            $content = "您的验证码是：" . $code . "。请不要把验证码泄露给其他人。";

        } elseif ($type == 'verify') {
            $verify_set = $sms = \Setting::get('shop.sms');
            $allset = iunserializer($verify_set['plugins']);
            if (is_array($allset) && !empty($allset['verify']['code_template'])) {
                $content = sprintf($allset['verify']['code_template'], $code, $title, $total, $name, $mobile, $tel);
            } else {
                $content = "提醒您，您的核销码为：" . $code . "，订购的票型是：" . $title . "，数量：" . $total . "张，购票人：" . $name . "，电话：" . $mobile . "，门店电话：" . $tel . "。请妥善保管，验票使用！";

            }
        }

        if ($state == '86') {
            $url = 'http://106.ihuyi.cn/webservice/sms.php?method=Submit';

            $smsrs = file_get_contents($url . '&account=' . $account . '&password=' . $pwd . '&mobile=' . $mobile . '&content=' . rawurlencode($content));
        } else {
            $url = 'http://api.isms.ihuyi.com/webservice/isms.php?method=Submit';
            $mobile = $state . ' ' . $mobile;

            $data = array(
                'account' => $account,
                'password' => $pwd,
                'mobile' => $mobile,
                'content' => $content,
            );
            $query = http_build_query($data);
            $smsrs = file_get_contents($url . '&' . $query);
        }

        return xml_to_array($smsrs);
    }

    function xml_to_array($xml)
    {
        $reg = "/<(\w+)[^>]*>([\\x00-\\xFF]*)<\\/\\1>/";
        if (preg_match_all($reg, $xml, $matches)) {
            $count = count($matches[0]);
            for ($i = 0; $i < $count; $i++) {
                $subxml = $matches[2][$i];
                $key = $matches[1][$i];
                if (preg_match($reg, $subxml)) {
                    $arr[$key] = xml_to_array($subxml);
                } else {
                    $arr[$key] = $subxml;
                }
            }
        }
        return $arr;
    }

    /**
     * pc端注册 保存信息
     *
     * @param $member_info
     * @param $uniacid
     */
    protected function save($member_info, $uniacid)
    {
        Session::set('member_id', $member_info['uid']);

        setcookie('Yz-appToken', encrypt($member_info['mobile'] . '\t' . $member_info['uid'] . '\t' .md5($member_info['password'])), time() + self::TOKEN_EXPIRE, '/');
    }

    /**
     * 检查验证码
     *
     * @return array
     */
    public static function checkCode()
    {
        $code = \YunShop::request()->code;
        $mobile = \YunShop::request()->mobile;
		return app('sms')->checkCode($mobile, $code);
    }

    /**
     * 检查验证码
     *
     * @return array
     */
    public static function checkAppCode()
    {
        $code = \YunShop::request()->code;
        $mobile = \YunShop::request()->mobile;

        $res = app('sms')->checkAppCode($mobile, $code);

        return $res;
    }

    /**
     * 检查邀请码
     *
     * @return array
     */
    public static function inviteCode()
    {
        $invite_code = \YunShop::request()->invite_code;
        \Log::info('invite_code', $invite_code);

        $status = \Setting::get('shop.member');
        \Log::info('status', $status);

        $status['is_invite'] = Member::chkInviteCode();
        \Log::info('is_invite', $status['is_invite']);

        if ($status['is_invite'] == 1) {//判断邀请码是否开启 1开启 0关闭
            \Log::info('is_invite == 1');

            if ($status['required'] == 1 && empty($invite_code)) { //判断邀请码是否必填，1必填 0可选填 判断邀请码是否为空
                \Log::info('empty--invite_code');

                return show_json('0', '请输入邀请码');
            } elseif ($status['required'] == 1 && !empty($invite_code)) {  //判断邀请码是否必填，1必填 0可选填 判断邀请码是否为空
                $data = MemberShopInfo:: getInviteCode($invite_code);  //查询邀请码是否存在
                \Log::info('data', $data);

                if (!$data) {
                    \Log::info('invalited--invite_code');
                    return show_json('0', '邀请码无效');
                }
            }
        }

        return show_json('1');
    }

    /**
     * 公众号开放平台授权登陆
     *
     * @param $uniacid
     * @param $userinfo
     * @return array|int|mixed
     */
    public function unionidLogin($uniacid, $userinfo, $upperMemberId = null, $loginType = null)
    {
        \Log::debug('----userinfo2----', $userinfo);
        $member_id = 0;
        $userinfo['nickname'] = $this->filteNickname($userinfo);
        $UnionidInfo = MemberUniqueModel::getUnionidInfo($uniacid, $userinfo['unionid'])->first();
        \Log::debug('----unique uid----', $UnionidInfo->member_id);
        $mc_mapping_fans_model = $userinfo['openid'] ? $this->getFansModel($userinfo['openid']) : null;
        if (request()->type == 1) {
            \Log::debug('----fans uid----', $mc_mapping_fans_model->uid);
        } else {
            \Log::debug('----fans uid----', $mc_mapping_fans_model->member_id);
        }
        if (!is_null($UnionidInfo)) {
            $member_id = $UnionidInfo->member_id;
        }

        $yz_member = MemberShopInfo::getMemberShopInfo($member_id);
        if (!empty($yz_member)) {
            if (!empty($yz_member->is_old)) {
                throw new MemberErrorMsgException("会员ID:{$member_id}数据有冲突，请联系客服");
            }
        }
        $this->checkFansUid($mc_mapping_fans_model, $userinfo);

        //检查member_id是否一致
        if (!is_null($UnionidInfo) && !is_null($mc_mapping_fans_model)) {
            $member_id = $this->checkMember($UnionidInfo, $mc_mapping_fans_model, $userinfo);

            if ($member_id > 0 && $UnionidInfo->member_id != $member_id) {
                $UnionidInfo->member_id = $member_id;
            }
        }

        if (empty($member_id) && !empty($mc_mapping_fans_model)) {
            $member_id = $mc_mapping_fans_model->uid;
        }

        $member_model = Member::getMemberById($member_id);
        $member_shop_info_model = MemberShopInfo::getMemberShopInfo($member_id);

        if (!empty($UnionidInfo->unionid) && !empty($member_model)
            && !empty($mc_mapping_fans_model) && !empty($member_shop_info_model)) {
            \Log::debug('微信登陆更新');

            $types = explode('|', $UnionidInfo->type);
            $member_id = $UnionidInfo->member_id;

            if (!is_null($loginType) && !in_array($loginType, $types)) {
                //更新ims_yz_member_unique表
                MemberUniqueModel::updateData(array(
                    'unique_id' => $UnionidInfo->unique_id,
                    'member_id' => $member_id,
                    'type' => $UnionidInfo->type . '|' . $loginType
                ));
            }

            $this->checkMemberInfo($member_model, $mc_mapping_fans_model, $member_shop_info_model);

            if ((!empty($userinfo['nickname']) && $member_model->nickname != $userinfo['nickname']) || ($userinfo['headimgurl'] && $member_model->avatar != $userinfo['headimgurl'])) {
				$this->updateMemberInfo($member_id, $userinfo);
            }
            $this->updateSubMemberInfoV2($member_id, $userinfo);
        } else {
            \Log::debug('添加新会员');
            \Log::debug('----添加会员前 uid----', $member_id);
            //DB::transaction(function () use (&$member_id, $member_model, $mc_mapping_fans_model, $member_shop_info_model, $uniacid, $userinfo, $UnionidInfo, $upperMemberId) {
            if (empty($member_model) && empty($mc_mapping_fans_model)) {
                $member_id = $this->addMemberInfo($uniacid, $userinfo);

                if ($member_id === false) {
                    return show_json(8, '保存用户信息失败');
                }
            } elseif (empty($member_model) && 0 === $mc_mapping_fans_model->uid) {
                $member_id = $this->addMcMemberInfo($uniacid, $userinfo);
                $this->updateFansMember($mc_mapping_fans_model, $member_id, $userinfo);
            } elseif ($member_model && 0 === $mc_mapping_fans_model->uid) {
                $this->updateFansMember($mc_mapping_fans_model, $member_id, $userinfo);
            } elseif ($member_model && empty($member_model->nickname)) {  // 更新用户信息(门店静默、无关注登陆)
                $this->updateMemberInfo($member_id, $userinfo);
            } elseif (empty($member_model) && $mc_mapping_fans_model->uid) {
                $this->updateFansMember($mc_mapping_fans_model, $member_id, $userinfo);
            } elseif (empty($mc_mapping_fans_model)) {
                //开放平台 先小程序后微信 更新微信粉丝
                if ($userinfo['openid']) {
                    $this->addFansMember($member_id, $uniacid, $userinfo);
                }
                $this->updateHeadPic($member_id, $userinfo);
            }

            if (empty($member_shop_info_model)) {
                if (0 == $member_id) {
                    \Log::debug(sprintf('----用户数据异常---%s-%s', $userinfo['openid'], $userinfo['nickname']));
                    throw new AppException('用户数据异常, 注册失败');
                }
                $this->addSubMemberInfoV2($uniacid, $member_id, $userinfo);
            } else {
                $this->updateSubMemberInfo($member_id, $userinfo['openid']);
            }

            if (empty($UnionidInfo->unionid)) {
                //添加ims_yz_member_unique表
                $this->addMemberUnionid($uniacid, $member_id, $userinfo['unionid']);
            }
            //生成分销关系链
            if ($upperMemberId) {
                \Log::debug(sprintf('----海报生成分销关系链----%d', $upperMemberId));
                Member::createRealtion($member_id, $upperMemberId);
            } else {
                \Log::debug(sprintf('----生成分销关系链----%d-%d', $upperMemberId, $member_id));
                Member::createRealtion($member_id);
            }

			$mid = $upperMemberId ? $upperMemberId : 0;
			$mid = Member::getMid() && empty($mid) ? Member::getMid() : $mid;
            event(new RegisterMember($mid, $member_id));
        }
        \Log::debug('--------return_member_id---------', $member_id);
        return $member_id;
    }

    /**
     * 公众号平台授权登陆
     *
     * @param $uniacid
     * @param $userinfo
     * @return array|int|mixed
     */
    public function openidLogin($uniacid, $userinfo, $upperMemberId = NULL)
    {
        \Log::debug('----userinfo1----', $userinfo);
        $member_id = 0;
        $userinfo['nickname'] = $this->filteNickname($userinfo);
        $fans_mode = $this->getFansModel($userinfo['openid']);
        $this->checkFansUid($fans_mode, $userinfo);

        if ($fans_mode) {
            $member_model = Member::getMemberById($fans_mode->uid);
            $member_shop_info_model = MemberShopInfo::getMemberShopInfo($fans_mode->uid);

            $member_id = $fans_mode->uid;
        }

        if ($yz_member_id = $this->checkYzMember($member_model, $fans_mode, $member_shop_info_model, $userinfo)) {
            $member_id = $yz_member_id;

            $member_shop_info_model = MemberShopInfo::getMemberShopInfo($member_id);
        }

        if ((!empty($member_model)) && (!empty($fans_mode) && !empty($member_shop_info_model))) {
            \Log::debug('微信登陆更新');
            $this->checkMemberInfo($member_model, $fans_mode, $member_shop_info_model);

            if ((!empty($userinfo['nickname']) && $member_model->nickname != $userinfo['nickname']) || ($userinfo['headimgurl'] && $member_model->avatar != $userinfo['headimgurl'])) {
                $this->updateMemberInfo($member_id, $userinfo);
            }

            $this->updateSubMemberInfoV2($member_id, $userinfo);
        } else {
            \Log::debug('添加新会员');

            //DB::transaction(function () use (&$member_id, $uniacid, $userinfo, $member_model, $fans_mode, $member_shop_info_model, $upperMemberId){
            if (empty($member_model) && empty($fans_mode)) {
                $member_id = $this->addMemberInfo($uniacid, $userinfo);

                if ($member_id === false) {
                    return show_json(8, '保存用户信息失败');
                }
            } elseif (empty($member_model) && 0 === $fans_mode->uid) {
                $member_id = $this->addMcMemberInfo($uniacid, $userinfo);
                $this->updateFansMember($fans_mode, $member_id, $userinfo);
            } elseif (empty($member_model) && $fans_mode->uid) {
                $this->updateFansMember($fans_mode, $member_id, $userinfo);
            } elseif ($member_model && empty($member_model->nickname)) {  // 更新用户信息(门店静默、无关注登陆)
                $this->updateMemberInfo($member_id, $userinfo);
            }

            if (empty($member_shop_info_model)) {
                if (0 == $member_id) {
                    \Log::debug(sprintf('----用户数据异常---%s-%s', $userinfo['openid'], $userinfo['nickname']));
                    throw new AppException('用户数据异常, 注册失败');
                }
                $this->addSubMemberInfoV2($uniacid, $member_id, $userinfo);
            }

            //生成分销关系链
            if ($upperMemberId) {
                \Log::debug(sprintf('----海报生成分销关系链----%d', $upperMemberId));
                Member::createRealtion($member_id, $upperMemberId);
            } else {
                \Log::debug(sprintf('----生成分销关系链----%d-%d', $upperMemberId, $member_id));
                Member::createRealtion($member_id);
            }

			$mid = $upperMemberId ? $upperMemberId : 0;
			$mid = Member::getMid() && empty($mid) ? Member::getMid() : $mid;
            event(new RegisterMember($mid, $member_id));
        }
        \Log::debug('--------return_member_id---------', $member_id);
        return $member_id;
    }

    /**
     * 过滤微信用户名特殊符号
     *
     * @param $userinfo
     * @return mixed
     */
    public function filteNickname($userinfo)
    {
        $nickname = $userinfo;
        if (is_array($userinfo)) {
            $nickname = $userinfo['nickname'];
        }
        $nickname = preg_replace('/[\x{1F600}-\x{1F64F}]/u', '', $nickname);
        $nickname = preg_replace('/[\x{1F300}-\x{1F5FF}]/u', '', $nickname);
        $nickname = preg_replace('/[\x{1F680}-\x{1F6FF}]/u', '', $nickname);
        $nickname = preg_replace('/[\x{2600}-\x{26FF}]/u', '', $nickname);
        $nickname = preg_replace('/[\x{2700}-\x{27BF}]/u', '', $nickname);
        $nickname = str_replace(array('"', '\''), '', $nickname);
        /* 去除表情字符串 */
        $nickname = preg_replace("#(\\\ud[0-9a-f]{3})#i", "", json_encode($nickname));
        $nickname = json_decode($nickname);
        \Log::debug('post', [$nickname]);
        $nickname = $this->cutNickname($nickname);
        \Log::debug('json', [$nickname]);
        return addslashes(trim($nickname));
    }

    /**
     * 截取字符串长度
     *
     * @param $nickname
     * @return string
     */
    public function cutNickname($nickname)
    {
        if (mb_strlen($nickname) > 18) {
            return mb_substr($nickname, 0, 18);
        }

        return $nickname;
    }

    /**
     * 会员基础表操作
     *
     * @param $uniacid
     * @param $userinfo
     * @return mixed
     */
    public function addMemberInfo($uniacid, $userinfo)
    {
        \Log::debug('---addMemberInfo---');
        //添加mc_members表
        $default_group = McGroupsModel::getDefaultGroupId();
        $uid = MemberModel::insertData($userinfo, array(
            'uniacid' => $uniacid,
            'groupid' => $default_group->groupid
        ));

        return $uid;
    }

    /**
     * 会员辅助表操作
     *
     * @param $uniacid
     * @param $member_id
     */
    public function addSubMemberInfo($uniacid, $member_id, $openid = 0)
    {
        //添加yz_member表
        $default_sub_group_id = MemberGroup::getDefaultGroupId()->first();

        if (!empty($default_sub_group_id)) {
            $default_subgroup_id = $default_sub_group_id->id;
        } else {
            $default_subgroup_id = 0;
        }

        SubMemberModel::replace(array(
            'member_id' => $member_id,
            'uniacid' => $uniacid,
            'group_id' => $default_subgroup_id,
            'level_id' => 0,
            'pay_password' => '',
            'salt' => '',
            'yz_openid' => $openid,
        ));
    }

    public function addSubMemberInfoV2($uniacid, $member_id, $userinfo)
    {
        //添加yz_member表
        $default_sub_group_id = MemberGroup::getDefaultGroupId()->first();

        if (!empty($default_sub_group_id)) {
            $default_subgroup_id = $default_sub_group_id->id;
        } else {
            $default_subgroup_id = 0;
        }

        $invite_code = MemberModel::getInviteCode($member_id);

        SubMemberModel::replace(array(
            'member_id' => $member_id,
            'uniacid' => $uniacid,
            'group_id' => $default_subgroup_id,
            'level_id' => 0,
            'pay_password' => '',
            'salt' => '',
            'yz_openid' => $userinfo['openid'],
            'access_token_1' => isset($userinfo['access_token']) ? $userinfo['access_token'] : '',
            'access_expires_in_1' => isset($userinfo['expires_in']) ? time() + $userinfo['expires_in'] : '',
            'refresh_token_1' => isset($userinfo['refresh_token']) ? $userinfo['refresh_token'] : '',
            'refresh_expires_in_1' => time() + (28 * 24 * 3600),
            'invite_code' => $invite_code
        ));
    }

    private function updateSubMemberInfo($uid, $userinfo)
    {
        SubMemberModel::updateOpenid(
            $uid, ['yz_openid' => $userinfo['openid']]
        );
    }

    protected function updateSubMemberInfoV2($uid, $userinfo)
    {

    }

    /**
     * 检查会员信息是否一致
     *
     * 微擎会员被删除导致商城会员不匹配
     *
     * @param $mcMember
     * @param $fansMember
     * @param $yzMember
     */
    protected function checkMemberInfo($mcMember, $fansMember, $yzMember)
    {

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
     * 更新微信用户信息
     *
     * @param $member_id
     * @param $userinfo
     */
    public function updateMemberInfo($member_id, $userinfo)
    {
        //更新mc_members
        $mc_data = array(
            'nickname' => isset($userinfo['nickname']) ? stripslashes($userinfo['nickname']) : '',
            'avatar' => isset($userinfo['headimgurl']) ? $userinfo['headimgurl'] : '',
            'gender' => isset($userinfo['sex']) ? $userinfo['sex'] : '-1',
            'nationality' => isset($userinfo['country']) ? $userinfo['country'] : '',
            'resideprovince' => isset($userinfo['province']) ? $userinfo['province'] : '' . '省',
            'residecity' => isset($userinfo['city']) ? $userinfo['city'] : '' . '市'
        );
        MemberModel::updataData($member_id, $mc_data);
    }

    /**
     * 登陆处理
     * @param $userinfo
     * @param null $upperMemberId
     * @return int
     * @throws AppException
     * @throws MemberErrorMsgException
     */
    public function memberLogin($userinfo, $upperMemberId = NULL)
    {
        \Log::debug('--------login_type--------', request()->type);
        if (is_array($userinfo) && !empty($userinfo['unionid'])) {
            $member_id = $this->unionidLogin(\YunShop::app()->uniacid, $userinfo, $upperMemberId);
        } elseif (is_array($userinfo) && !empty($userinfo['openid'])) {
            $member_id = $this->openidLogin(\YunShop::app()->uniacid, $userinfo, $upperMemberId);
        }
        return $member_id;
    }

    public function memberInfoAttrStatus($member)
    {
        $form = [];
        $set = \Setting::get('shop.form');

        if (!is_null($set)) {
            $set = json_decode($set, true);

            if (!empty($set['form'])) {
                $form = array_values(array_sort($set['form'], function ($value) {
                    return $value['sort'];
                }));

                if (!empty($member['member_form'])) {
                    $member_form = json_decode($member['member_form'], true);
                    $form = self::getMemberForm($form, $member_form);
                }
            }
        } else {
            $set['base'] = [
                'sex' => 1,
                'address' => 1,
                'birthday' => 1
            ];
        }

        $set['form'] = $form;

        return $set;
    }

    private function getMemberForm($form, $member_form)
    {
        foreach ($form as &$rows) {
            foreach ($member_form as $item) {
                if ($item['pinyin'] == $rows['pinyin']) {
                    $rows['value'] = $item['value'];
                }
            }
        }

        return $form;
    }

    public function updateMemberForm($data)
    {
        $member_form = [];
        $set = \Setting::get('shop.form');
        $set = json_decode($set, true);

        // echo '<pre>';print_r($data['customDatas']);exit;

        if (!empty($set['form'])) {
            $member_form = $form = array_values(array_sort($set['form'], function ($value) {
                return $value['sort'];
            }));

            foreach ($form as $key => &$item) {
                foreach ($data['customDatas'] as $rows) {
                    if ($rows['pinyin'] == $item['pinyin']) {

                        $item['del'] = 1;

                        $member_form[$key]['value'] = $rows['value'];
                    }
                }
            }
        }

        $set['form'] = $form;
        \Setting::set('shop.form', json_encode($set));

        return $member_form;
    }

    /**
     * 检查同步登录凭证和统一表
     * @param $UnionidInfo 统一表会员信息
     * @param $fansInfo 当前登录凭证会员信息
     * @param $userInfo 当前授权会员信息
     * @return mixed
     * @throws MemberErrorMsgException
     */
    public function checkMember($UnionidInfo, $fansInfo, $userInfo)
    {
        $relation_set = Setting::get('relation_base');
        \Log::debug('----unionid---', $UnionidInfo->member_id);
        \Log::debug('----fans----', $fansInfo->uid);
        if ($UnionidInfo->member_id != $fansInfo->uid) {
            if ($UnionidInfo->member_id == 0) {
                return $fansInfo->uid;
            }
            if ($fansInfo->uid == 0) {
                return $UnionidInfo->member_id;
            }
            $merge_choice = $relation_set['is_merge_save_level'];
            $merge_choice_uids = $this->memberChoice($merge_choice, $UnionidInfo, $fansInfo);
            list($main_member_id, $abandon_member_id) = $merge_choice_uids;
            $abandon_member = Member::getMemberById($abandon_member_id);
            if ($abandon_member && isset($relation_set['is_member_merge']) && $relation_set['is_member_merge'] != 1 && time() > ($abandon_member->createtime + 5 * 60)) {
                //新会员合并按钮
                $yz_main_member = MemberShopInfo::getMemberShopInfo($main_member_id);
                $yz_abandon_member = MemberShopInfo::getMemberShopInfo($abandon_member_id);
                if (!$yz_main_member) {
                    $this->addYzMember($main_member_id);
                }
                if (!$yz_abandon_member) {
                    $this->addYzMember($abandon_member_id);
                }
                MemberShopInfo::uniacid()->where('member_id', $main_member_id)->update(['is_old' => 1, 'mark_member_id' => $abandon_member_id]);
                MemberShopInfo::uniacid()->where('member_id', $abandon_member_id)->update(['is_old' => 1, 'mark_member_id' => $main_member_id]);
                throw new MemberErrorMsgException('会员数据异常，请联系客服');
            } else {
                //全自动合并按钮
                $exception = DB::transaction(function () use ($main_member_id, $abandon_member_id, $fansInfo, $userInfo, $relation_set) {
                    $abandon_member = Member::getMemberById($abandon_member_id);
                    $main_member = Member::getMemberById($main_member_id);
                    $merge_data = [
                        'uniacid' => \YunShop::app()->uniacid,
                        'before_uid' => $abandon_member_id,
                        'after_uid' => $main_member_id,
                        'before_mobile' => $abandon_member->mobile,
                        'after_mobile' => $main_member->mobile,
                        'before_point' => $abandon_member->credit1?:0.00,
                        'after_point' => bcadd($main_member->credit1, $abandon_member->credit1, 2)?:0.00,
                        'before_amount' => $abandon_member->credit2?:0.00,
                        'after_amount' => bcadd($main_member->credit2, $abandon_member->credit2, 2)?:0.00,
                        'set_content' => json_encode($relation_set),
                        'merge_type' => 3,
                    ];
                    //删除重复微擎会员
                    $mc_member = Member::getMemberById($main_member_id);
                    if ($mc_member) {
                        Member::uniacid()->where('uid', $abandon_member_id)->delete();
                    } else {
                        Member::uniacid()->where('uid', $abandon_member_id)->update(['uid' => $main_member_id]);
                    }
                    //删除重复商城会员
                    $sub_member = MemberShopInfo::getMemberShopInfo($main_member_id);
                    if ($sub_member) {
                        MemberShopInfo::uniacid()->where('member_id', $abandon_member_id)->delete();
                    } else {
                        MemberShopInfo::uniacid()->where('member_id', $abandon_member_id)->update(['member_id' => $main_member_id]);
                    }
                    //合并处理服务
                    (new MemberMergeService($main_member_id, $abandon_member_id, $merge_data))->handel();
                    //小程序
                    MemberMiniAppModel::updateData($abandon_member_id, ['member_id' => $main_member_id]);
                    //app
                    MemberWechatModel::updateData($abandon_member_id, ['member_id' => $main_member_id]);
                    //公众号
                    McMappingFans::where('uid', $abandon_member_id)->update(['uid' => $main_member_id]);
                    //聚合cps
                    if (Schema::hasTable('yz_member_aggregation_app')) {
                        DB::table('yz_member_aggregation_app')->where('member_id', $abandon_member_id)->update(['member_id' => $main_member_id]);
                    }
                    //企业微信
                    if (Schema::hasTable('yz_member_customer')) {
                        DB::table('yz_member_customer')->where('uid',$abandon_member_id)->update(['uid' => $main_member_id]);
                    }
                    //unionid
                    MemberUniqueModel::where('member_id', $abandon_member_id)->update(['member_id' => $main_member_id]);
                    //支付宝
                    MemberAlipay::where('member_id', $abandon_member_id)->update(['member_id' => $main_member_id]);
                    $this->updateFansMember($fansInfo, $main_member_id, $userInfo);
                });
                if (!is_null($exception)) {
                    throw new MemberErrorMsgException('sql执行错误，需回滚');
                }
                event(new MergeMemberEvent($main_member_id, $abandon_member_id));
                return $main_member_id;
            }
        }
        return $UnionidInfo->member_id;
    }

    private function addYzMember($member_id)
    {
        $default_group = McGroupsModel::getDefaultGroupId();
        $invite_code = MemberModel::getInviteCode($member_id);
        $yz_member = SubMemberModel::uniacid()->where('member_id', $member_id)->first();
        if (!$yz_member) {
            $add_arr = [
                'member_id' => $member_id,
                'uniacid' => \Yunshop::app()->uniacid,
                'group_id' => $default_group->id ?: 0,
                'level_id' => 0,
                'pay_password' => '',
                'salt' => '',
                'yz_openid' => '',
                'access_token_1' => '',
                'access_expires_in_1' => '',
                'refresh_token_1' => '',
                'refresh_expires_in_1' => '',
                'invite_code' => $invite_code
            ];
            SubMemberModel::create($add_arr);
        }
    }

    private function memberChoice($merge_choice, $UnionidInfo, $fansInfo)
    {
        $unique_uid = $UnionidInfo->member_id;
        $fans_uid = $fansInfo->uid;
        switch ($merge_choice) {
            case 1 : //手机号
                $member_uni = Member::getMemberById($unique_uid);
                $member_fans = Member::getMemberById($fans_uid);
                if ((empty($member_uni->mobile) && empty($member_fans->mobile)) || ($member_uni->mobile && $member_fans->mobile)) {
                    if ($unique_uid < $fans_uid) {
                        $main_member_id = $unique_uid;
                        $abandon_member_id = $fans_uid;
                    } else {
                        $main_member_id = $fans_uid;
                        $abandon_member_id = $unique_uid;
                    }
                } elseif (empty($member_uni->mobile) && !empty($member_fans->mobile)) {
                    $main_member_id = $fans_uid;
                    $abandon_member_id = $unique_uid;
                } else {
                    $main_member_id = $unique_uid;
                    $abandon_member_id = $fans_uid;
                }
                break;
            case 2 : //公众号
                $member_uni = McMappingFans::getFansById($unique_uid);
                $member_fans = McMappingFans::getFansById($fans_uid);
                list($main_member_id, $abandon_member_id) = $this->handleMemberChoice($member_uni, $member_fans, $unique_uid, $fans_uid);
                break;
            case 3 : //小程序
                $member_uni = MemberMiniAppModel::getFansById($unique_uid);
                $member_fans = MemberMiniAppModel::getFansById($fans_uid);
                list($main_member_id, $abandon_member_id) = $this->handleMemberChoice($member_uni, $member_fans, $unique_uid, $fans_uid);
                break;
            case 4 : //app
                $member_uni = MemberWechatModel::getFansById($unique_uid);
                $member_fans = MemberWechatModel::getFansById($fans_uid);
                list($main_member_id, $abandon_member_id) = $this->handleMemberChoice($member_uni, $member_fans, $unique_uid, $fans_uid);
                break;
            case 5 : //alipay
                $member_uni = MemberAlipay::getFansById($unique_uid);
                $member_fans = MemberAlipay::getFansById($fans_uid);
                list($main_member_id, $abandon_member_id) = $this->handleMemberChoice($member_uni, $member_fans, $unique_uid, $fans_uid);
                break;
            default : //注册时间
                if ($unique_uid < $fans_uid) {
                    $main_member_id = $unique_uid;
                    $abandon_member_id = $fans_uid;
                } else {
                    $main_member_id = $fans_uid;
                    $abandon_member_id = $unique_uid;
                }
                break;
        }
        return [$main_member_id, $abandon_member_id];
    }

    private function handleMemberChoice($member_uni, $member_fans, $unique_uid, $fans_uid)
    {
        if (($member_uni && $member_fans) || (empty($member_uni) && empty($member_fans))) {
            if ($unique_uid < $fans_uid) {
                $main_member_id = $unique_uid;
                $abandon_member_id = $fans_uid;
            } else {
                $main_member_id = $fans_uid;
                $abandon_member_id = $unique_uid;
            }
        } elseif (empty($member_uni) && !empty($member_fans)) {
            $main_member_id = $fans_uid;
            $abandon_member_id = $unique_uid;
        } else {
            $main_member_id = $unique_uid;
            $abandon_member_id = $fans_uid;
        }
        return [$main_member_id, $abandon_member_id];
    }

    private function updateHeadPic($member_id, $userInfo)
    {
        if (!empty($userInfo)) {
            Member::getMemberByUid($member_id)->update([
                'nickname' => $this->filteNickname($userInfo),
                'avatar' => $userInfo['headimgurl'],
            ]);
        }
    }

    public function updateFansMember($fan, $member_id, $userinfo)
    {
        //TODO
    }

    /**
     * 扫海报关注
     *
     * 关注->微擎注册->商城注册
     *
     * 接口延迟，商城无法监控微擎行为导致会员注册重复1(fans->uid=0; mc_members=null)
     *
     * @param $fansModel
     * @param $userInfo
     */
    private function checkFansUid($fansModel, $userInfo)
    {
        if ($fansModel && (0 == $fansModel->uid || 1 == $fansModel->uid)) {
            $member_id = SubMemberModel::getMemberId($userInfo['openid']);

            if (!is_null($member_id)) {
                $fansModel->uid = $member_id;

                $this->updateFansMember($fansModel, $member_id, $userInfo);
            }
        }
    }

    /**
     * 扫海报关注
     *
     * 关注->微擎注册->商城注册
     *
     * 接口延迟，商城无法监控微擎行为导致会员注册重复2(fans->uid被更新; mc_members存在)
     *
     * @param $mc_members
     * @param $fans
     * @param $yz_member
     * @param $userInfo
     * @return int
     */
    private function checkYzMember($mc_members, $fans, $yz_member, $userInfo)
    {
        if (!is_null($mc_members) && !is_null($fans) && is_null($yz_member)) {
            $member_id = SubMemberModel::getMemberId($userInfo['openid']);

            if (!is_null($member_id) && $member_id != 0 && $member_id != $fans->uid) {
                if (Member::getMemberById($member_id)) {
                    Member::deleted($fans->uid);
                    $this->updateFansMember($fans, $member_id, $userInfo);
                }

                return $member_id;
            }
        }

        return 0;
    }

    /**
     * @param $member_id
     * @param string $key
     * @param int $minute
     * @throws MemberNotLoginException
     */
    public function chkAccount($member_id, $key = 'chekAccount', $minute = 30)
    {
        $type = \YunShop::request()->type;
        $mid = Member::getMid();

        if (1 == $type && !Cache::has($member_id . ':' . $key)) {
            Cache::put($member_id . ':' . $key, 1, \Carbon\Carbon::now()->addMinutes($minute));
            $queryString = ['type' => $type, 'session_id' => session_id(), 'i' => \YunShop::app()->uniacid, 'mid' => $mid];

            throw new MemberNotLoginException('请登录', ['login_status' => 0, 'login_url' => Url::absoluteApi('member.login.chekAccount', $queryString)]);
        }
    }

    public function newMemberInfoAttrStatus($member_info)
    {
        $set = \Setting::get('shop.form');
        if (!is_null($set)) {
            $set = json_decode($set, true);
            if (!empty($set['form']))  unset($set['form']);
            $set['form'] = [
                [
                    'del' => 0,
                    'name' => '会员姓名',
                    'pinyin' => 'huiyuannicheng',
                    'sort' => 1,
                    'value' => $member_info->realname
                ],
                [
                    'del' => 0,
                    'name' => '手机号码',
                    'pinyin' => 'shoujihaoma',
                    'sort' => 2,
                    'value' => $member_info->mobile
                ]
            ];
        } else {
            $set = [
                'base' => [
                    'sex' => 1,
                    'address' => 1,
                    'birthday' => 1
                ],
                'form' => [
                    [
                        'del' => 0,
                        'name' => '会员姓名',
                        'pinyin' => 'huiyuannicheng',
                        'sort' => 1,
                        'value' => $member_info->realname
                    ],
                    [
                        'del' => 0,
                        'name' => '手机号码',
                        'pinyin' => 'shoujihaoma',
                        'sort' => 2,
                        'value' => $member_info->mobile
                    ]
                ]
            ];
        }

        return  $set;
    }

    public function setLoginLimit($mobile)
    {
        Redis::incr('login_error_count_'.$mobile);
        Redis::set('last_error_time_'.$mobile,time());
        $error_times = Redis::get('login_error_count_'.$mobile)?:0;
        $error_count = 5 - $error_times;

        if ($error_count <=0) {
            Redis::set('login_error_count_'.$mobile, 0);
            Redis::setex('login_error_time_'.$mobile, 30*60, true);
        }
        return $error_count;
    }

    public function getLoginLimit($mobile)
    {
        $login_error_time = Redis::get('login_error_time_'.$mobile);
        if ($login_error_time) {
            $last_error_time = Redis::get('last_error_time_'.$mobile);
            $remain_time = ($last_error_time+30*60) - time();
            return intval(($remain_time/60));
       }
        return 0;
    }

    public static function countReset($mobile)
    {
        return Redis::set('login_error_count_'.$mobile, 0);
    }
}