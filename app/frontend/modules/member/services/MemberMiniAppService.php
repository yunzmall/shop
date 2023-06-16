<?php
/**
 * Created by PhpStorm.
 * Author:
 * Date: 17/2/23
 * Time: 上午11:20
 */

namespace app\frontend\modules\member\services;

use app\common\exceptions\MemberNotLoginException;
use app\common\exceptions\ShopException;
use app\common\facades\EasyWeChat;
use app\common\helpers\Cache;
use app\common\helpers\Client;
use app\common\services\Session;
use app\frontend\modules\member\models\MemberMiniAppModel;
use app\frontend\modules\member\models\MemberUniqueModel;
use app\frontend\modules\member\models\McMappingFansModel;
use app\frontend\modules\member\models\MemberModel;
use EasyWeChat\Factory;
use EasyWeChat\MiniProgram\Application;

class MemberMiniAppService extends MemberService
{
    const LOGIN_TYPE = 2;  //小程序

    public function __construct()
    {
    }

    public function login()
    {
        $ver = \YunShop::request()->ver;

        if ($ver == 2) {
            return $this->newLogin();
        } else {
            return $this->oldLogin();
        }
    }

    /**
     * 小程序登录态
     *
     * @param $user_info
     * @return string
     */
    function wx_app_session($user_info)
    {
        if (empty($user_info['session_key']) || empty($user_info['openid'])) {
            return show_json(0, '用户信息有误');
        }

        $random = md5(uniqid(mt_rand()));

        $_SESSION['wx_app'] = array($random => iserializer(array('session_key' => $user_info['session_key'], 'openid' => $user_info['openid'])));

        return $random;
    }

    public function createMiniMember($json_user, $arg)
    {
        $user_info = MemberMiniAppModel::getUserInfo($json_user['openid']);

        if (!empty($user_info)) {
            MemberMiniAppModel::updateUserInfo($json_user['openid'], array(
                'nickname' => $json_user['nickname'],
                'avatar' => $json_user['headimgurl'],
                'gender' => $json_user['sex'],
            ));
        } else {
            MemberMiniAppModel::replace(array(
                'uniacid' => $arg['uniacid'],
                'member_id' => $arg['member_id'],
                'openid' => $json_user['openid'],
                'nickname' => $json_user['nickname'],
                'avatar' => $json_user['headimgurl'],
                'gender' => $json_user['sex'],
            ));
        }
    }

    /**
     * 公众号开放平台授权登陆
     *
     * @param $uniacid
     * @param $userinfo
     * @return array|int|mixed
     */
    public function unionidLogin($uniacid, $userinfo, $upperMemberId = NULL)
    {
        $member_id = parent::unionidLogin($uniacid, $userinfo, $upperMemberId = NULL, self::LOGIN_TYPE);

        return $member_id;
    }

    public function updateMemberInfo($member_id, $userinfo)
    {
		parent::updateMemberInfo($member_id, $userinfo);

        $record = array(
            'openid' => $userinfo['openid'],
            'nickname' => stripslashes($userinfo['nickname'])
        );

		MemberMiniAppModel::updateData($member_id, $record);
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
        if ($userinfo['openid']) {
        MemberMiniAppModel::replace(array(
            'uniacid' => $uniacid,
            'member_id' => $uid,
            'openid' => $userinfo['openid'],
            'nickname' => $userinfo['nickname'],
            'avatar' => $userinfo['headimgurl'],
            'gender' => $userinfo['sex'],
        ));
        }
    }

    public function getFansModel($openid)
    {
        $model = MemberMiniAppModel::getUId($openid);

        if (!is_null($model)) {
            $model->uid = $model->member_id;
        }

        return $model;
    }

    /**
     * 添加会员主表信息
     *
     * @param $uniacid
     * @param $userinfo
     * @return mixed
     */
    public function addMcMemberInfo($uniacid, $userinfo)
    {
        $uid = parent::addMemberInfo($uniacid, $userinfo);

        return $uid;
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
     * 验证登录状态
     *
     * @return bool
     */
    public function checkLogged($login = null)
    {
		if (MemberService::isLogged()) {
			if (!empty(Session::get('mini_openid'))) {
				return true;
			}
			$member_mini = MemberMiniAppModel::getFansById(\YunShop::app()->getMemberId());
			if (empty($member_mini)) {
				return false;
			}
			Session::set('mini_openid',$member_mini['openid']);
			return true;
		}
		return false;
    }

    public function updateFansMember($fan, $member_id, $userinfo)
    {
        //小程序不更新用户信息
        /*$record = array(
            'member_id' => $member_id,
            'nickname' => stripslashes($userinfo['nickname']),
            'avatar' => isset($userinfo['headimgurl']) ? $userinfo['headimgurl'] : '',
            'gender' => isset($userinfo['sex']) ? $userinfo['sex'] : '-1',
        );

        MemberMiniAppModel::where('mini_app_id', $fan->mini_app_id)->update($record);*/
    }

    public function oldLogin()
    {
        include dirname(__FILE__) . "/../vendors/wechat/wxBizDataCrypt.php";
        $min_set = \Setting::get('plugin.min_app');
        if (is_null($min_set) || 0 == $min_set['switch']) {
            return show_json(0, '未开启小程序');
        }
        $para = \YunShop::request();
        $data = array(
            'appid' => $min_set['key'],
            'secret' => $min_set['secret'],
            'js_code' => $para['code'],
            'grant_type' => 'authorization_code',
        );
        $url = 'https://api.weixin.qq.com/sns/jscode2session';
        $user_info = \Curl::to($url)->withData($data)->asJsonResponse(true)->get();
        $data = '';  //json
        if (!empty($para['info'])) {
            $json_data = json_decode($para['info'], true);
            $pc = new \WXBizDataCrypt($min_set['key'], $user_info['session_key']);
            $errCode = $pc->decryptData($json_data['encryptedData'], $json_data['iv'], $data);
        }
        \Log::debug('-------------min errcode-------', [$errCode]);
        if ($errCode == 0) {
            $json_user = json_decode($data, true);
        } else {
            if (isset($_GET['test'])) {
                $json_user = json_decode($_GET['user_info'], true);
                $user_info = [
                    'openid' => $json_user['openid'],
                    'session_key' => $json_user['openid']
                ];
            } else {
                return show_json(0, '登录认证失败');
            }
        }
        if (!empty($json_user)) {
            if (isset($json_user['unionId'])) {
                $json_user['unionid'] = $json_user['unionId'];
            }
            if (isset($json_user['openId'])) {
                $json_user['openid'] = $json_user['openId'];
                $json_user['nickname'] = $json_user['nickName'];
                $json_user['headimgurl'] = $json_user['avatarUrl'];
                $json_user['sex'] = $json_user['gender'];
            }
            //Login
            try {
                $member_id = $this->memberLogin($json_user);
            } catch (ShopException $exception) {
                return show_json(0, $exception->getMessage());
            }
            $is_first_time_bind = $this->verifyFirstTimeLogin($member_id,(bool)$min_set['is_first_time_bind']);
            Session::set('member_id', $member_id);
			Session::set('mini_openid', $json_user['openid']);
            $random = $this->wx_app_session($user_info);
            $nickname = MemberModel::select('nickname')->where('uid',$member_id)->first();
            $result = array(
                'session' => $random,
                'wx_token' => session_id(),
                'uid' => $member_id,
                'user_info' => ['nickname' => $nickname['nickname'] ?: ''],
                'is_first_time_bind' => $is_first_time_bind
            );
            return show_json(1, $result, $result);
        } else {
            return show_json(0, '获取用户信息失败');
        }
    }

    public function newLogin()
    {
        include dirname(__FILE__) . "/../vendors/wechat/wxBizDataCrypt.php";
        $min_set = \Setting::get('plugin.min_app');
        if (is_null($min_set) || 0 == $min_set['switch']) {
            return show_json(0, '未开启小程序');
        }
        $para = json_decode(\YunShop::request()->info, 1);
        $code = \YunShop::request()->code;
        $data = array(
            'appid' => $min_set['key'],
            'secret' => $min_set['secret'],
            'js_code' => $code,
            'grant_type' => 'authorization_code',
        );
        $url = 'https://api.weixin.qq.com/sns/jscode2session';
        $user_info = \Curl::to($url)->withData($data)->asJsonResponse(true)->get();
        \Log::debug('-------------min errcode-------', ['code: ' . $user_info['errcode'] . ' errmsg: ' . $user_info['errmsg']]);
        if (!empty($user_info) && 0 == $user_info['errcode']) {
            if (isset($user_info['unionid'])) {
                $json_user['unionid'] = $user_info['unionid'];
            }
            $json_user['openid'] = $user_info['openid'];
            $json_user['nickname'] = $para['nickName'] ? $this->filteNickname($para['nickName']) : '';
            $json_user['headimgurl'] = $para['avatarUrl'] ?: '';
            $json_user['sex'] = $para['gender'] ?: 0;
            //Login
            try {
                $member_id = $this->memberLogin($json_user);
            } catch (ShopException $exception) {
                return show_json(0, $exception->getMessage());
            }
            $is_first_time_bind = $this->verifyFirstTimeLogin($member_id,(bool)$min_set['is_first_time_bind']);
            Session::set('member_id', $member_id);
			Session::set('mini_openid', $json_user['openid']);
            $random = $this->wx_app_session($user_info);
            $nickname = MemberModel::select('nickname')->where('uid',$member_id)->first();
            $result = array(
                'session' => $random,
                'wx_token' => session_id(),
                'uid' => $member_id,
                'user_info' => ['nickname' => $nickname['nickname'] ?: ''],
                'is_first_time_bind' => $is_first_time_bind
            );
            return show_json(1, $result, $result);
        } else {
            return show_json(0, '获取用户信息失败');
        }
    }

    /**
     * @param $member_id
     * @param $first_time_bind_set
     * @return int
     */
    protected function verifyFirstTimeLogin($member_id, $first_time_bind_set): int
    {
        $member_model = MemberModel::getMemberById($member_id);
        $is_first_time_bind = 1;//首次登录显示授权手机号按钮:1-开启

        //已绑定手机
        if ($member_model->mobile) {
            $is_first_time_bind = 0;
        }

        //未开启
        if (!$first_time_bind_set) {
            $is_first_time_bind = 0;
        }

        return $is_first_time_bind;
    }

    /**
     * 获取授权用户手机号
     *
     * @param $code
     * @return array|mixed|\stdClass
     */
    public function getPhoneNumber($code)
    {
        $min_set = \Setting::get('plugin.min_app');

        if (is_null($min_set) || 0 == $min_set['switch']) {
            return show_json(0, '未开启小程序');
        }

        $token = Cache::remember('mini:phonenumber:' . \YunShop::app()->uniacid, 90, function () use ($min_set) {
            $gtoken = \Curl::to("https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={$min_set['key']}&secret={$min_set['secret']}")->get();

            return json_decode($gtoken, 1);
        });

        $data = \Curl::to("https://api.weixin.qq.com/wxa/business/getuserphonenumber?access_token={$token['access_token']}")
            ->withData(json_encode(['code' => $code]))
            ->asJsonResponse(true)
            ->post();

        return $data;
    }
}
