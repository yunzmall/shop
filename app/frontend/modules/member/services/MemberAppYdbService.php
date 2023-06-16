<?php
/**
 * Created by PhpStorm.
 * User: yangming
 * Date: 17/8/2
 * Time: 上午11:20
 */

namespace app\frontend\modules\member\services;

use app\common\helpers\Client;
use app\common\helpers\Url;
use app\common\services\Session;
use app\frontend\models\Member;
use app\frontend\modules\member\models\McMappingFansModel;
use app\frontend\modules\member\models\MemberWechatModel;
use app\frontend\modules\member\models\MemberUniqueModel;
use app\frontend\modules\member\models\MemberModel;
use Crypt;
use app\common\models\MemberShopInfo;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Redis;

class MemberAppYdbService extends MemberService
{
    const LOGIN_TYPE = 7;

    public function __construct()
    {

    }

    public function login()
    {
        $uniacid  = \YunShop::app()->uniacid;
        $mobile   = \YunShop::request()->mobile;
        $password = \YunShop::request()->password;
        $uuid     = trim($_REQUEST['uuid']);

        $redirect_url = request()->yz_redirect;

        if (!empty($mobile) && !empty($password)) {
            if (!\Request::isMethod('post') || !MemberService::validate($mobile, $password)) {
                return show_json(6, "手机号或密码错误");
            }
            $remain_time = $this->getLoginLimit($mobile);
            if($remain_time){
                return show_json(6, "账号锁定中，请".$remain_time."分钟后再登录");
            }
            $has_mobile = MemberModel::checkMobile($uniacid, $mobile);
            if (!$has_mobile) {
                return show_json(7, "用户不存在");
            }
            $password = md5($password . $has_mobile->salt);
            $member_info = MemberModel::getUserInfo($uniacid, $mobile, $password)->first();
            if (!$member_info) {
                $error_count = $this->setLoginLimit($mobile);
                if ($error_count > 0) {
                    return show_json(6, "密码错误！你还剩" . $error_count . "次机会");
                } else {
                    return show_json(6, "密码错误次数已达5次，您的账号已锁定，请30分钟之后登录！");
                }
            }
            $member_info = $member_info->toArray();
            //生成分销关系链
            Member::createRealtion($member_info['uid']);
            $yz_member = MemberShopInfo::getMemberShopInfo($member_info['uid']);
            if ($yz_member) {
                $yz_member = $yz_member->toArray();
                $data = MemberModel::userData($member_info, $yz_member);
            } else {
                $data = $member_info;
            }
            Session::set('member_id', $member_info['uid']);
            setcookie('Yz-appToken', encrypt($member_info['mobile'] . '\t' . $member_info['uid']), time() + self::TOKEN_EXPIRE);
            MemberService::countReset($mobile);

            $data['redirect_url'] = base64_decode($redirect_url);
            return show_json(1, $data);
        } else {
            $para = \YunShop::request();
            \Log::debug('获取用户信息：', print_r($para, 1));
            $member = MemberWechatModel::getUserInfo($para['openid']);
            if ($member) {
                Session::set('member_id', $member['member_id']);
                $this->redirect_link($para['openid']);
            }
            if ($para['openid'] && $para['token']) {
                $this->app_get_userinfo($para['token'], $para['openid'], $uuid);
            } elseif ($para['openid']) {
                $this->redirect_link($para['openid']);
            }
            if ($para['apptoken']) {
                $openid = Crypt::decrypt($para['apptoken']);
                $member = MemberWechatModel::getUserInfo($openid);
                if (!$member) {
                    return show_json(3, '登录失败，请重试');
                }
                Session::set('member_id', $member['member_id']);
                setcookie('Yz-appToken', encrypt($openid . '\t' . $member['member_id']), time() + self::TOKEN_EXPIRE);
                return show_json(1, $member->toArray());
            }
        }
    }

    /**
     * app获取用户信息并存储
     *
     * @param $token
     * @param $openid
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

        if (!empty($user_info) && !empty($user_info['unionid'])) {
            $this->memberLogin($user_info);
            exit('success');
        } else {
            exit('fail');
        }
    }

    /**
     * app登录跳转到前端
     *
     * @param $openid
     */
    public function redirect_link($openid)
    {
        if (!$openid) {
            $url = Url::absoluteApp('login');
        } else {
            $apptoken = Crypt::encrypt($openid);
            $url      = Url::absoluteApp('login_validate', ["apptoken" => $apptoken]);
        }

        redirect($url)->send();
        exit();
    }

    public function updateMemberInfo($member_id, $userinfo)
    {
        parent::updateMemberInfo($member_id, $userinfo);

        $record = array(
            'openid'   => $userinfo['openid'],
            'nickname' => stripslashes($userinfo['nickname']),
            'uuid'     => $userinfo['uuid']
        );
        MemberWechatModel::updateData($member_id, $record);
    }

    public function addMemberInfo($uniacid, $userinfo)
    {
        $uid = parent::addMemberInfo($uniacid, $userinfo);

        $this->addFansMember($uid, $uniacid, $userinfo);

        return $uid;
    }

    public function addMcMemberFans($uid, $uniacid, $userinfo)
    {
        McMappingFansModel::insertData($userinfo, array(
            'uid'     => $uid,
            'acid'    => $uniacid,
            'uniacid' => $uniacid,
            'salt'    => Client::random(8),
        ));
    }

    public function addFansMember($uid, $uniacid, $userinfo)
    {
        $user = MemberWechatModel::getUserInfo_memberid($uid);
        if (!empty($user)) {
            $this->updateMemberInfo($uid, $userinfo);
        } else {
            MemberWechatModel::replace(array(
                'uniacid'   => $uniacid,
                'member_id' => $uid,
                'openid'    => $userinfo['openid'],
                'nickname'  => $userinfo['nickname'],
                'avatar'    => $userinfo['headimgurl'],
                'gender'    => $userinfo['sex'],
                'province'  => '',
                'country'   => '',
                'city'      => '',
                'uuid'      => $userinfo['uuid']
            ));
        }
    }

    public function getFansModel($openid)
    {
        return McMappingFansModel::getUId($openid);
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
        MemberUniqueModel::insertData(array(
            'uniacid'   => $uniacid,
            'unionid'   => $unionid,
            'member_id' => $member_id,
            'type'      => self::LOGIN_TYPE
        ));
    }

    /**
     * 验证登录状态
     *
     * @return bool
     */
    public function checkLogged($login = null)
    {
        if (isset($_COOKIE['Yz-appToken'])) {
            try {
                $yz_token = decrypt($_COOKIE['Yz-appToken']);

                list($openid, $uuid) = explode('\t', $yz_token);

                if (preg_match('/^\d{11}/', $openid)) {
                    $member = \app\common\models\Member::uniacid()->where('mobile', $openid)->first();

                    if (!is_null($member)) {
                        $member_id = $member->uid;
                    }
                } else {
                    $member = MemberWechatModel::getUserInfo($openid);

                    if (!is_null($member)) {
                        $member_id = $member->member_id;
                    }
                }

                if (!$member) {
                    return false;
                }

                if (\YunShop::app()->getMemberId() && $member_id != \YunShop::app()->getMemberId()) {
                    setcookie(session_name(), '',time() - 3600, '/');
                    setcookie(session_name(), '',time() - 3600);
                    setcookie('Yz-appToken', '',time() - 3600, '/addons/yun_shop');

                    return false;
                }

                Session::set('member_id', $member_id);

                return true;
            } catch (DecryptException $e) {
                return false;
            }
        }

        return false;
    }
}