<?php
/**
 * Created by PhpStorm.
 * User: dingran
 * Date: 2019/7/9
 * Time: 上午11:03
 */

namespace app\frontend\modules\member\services;


use app\common\exceptions\AppException;
use app\common\facades\Setting;
use app\common\helpers\Client;
use app\common\models\AccountWechats;
use app\common\models\Store;
use app\common\services\api\WechatApi;
use app\frontend\models\Member;
use app\frontend\models\MemberShopInfo;
use app\frontend\modules\member\models\McMappingFansModel;
use app\frontend\modules\member\models\MemberModel;
use app\frontend\modules\member\models\MemberUniqueModel;
use app\frontend\modules\member\models\SubMemberModel;
use Yunshop\Room\common\TLSSigAPIv2;
use Yunshop\Room\models\Anchor;
use Yunshop\Room\services\ImService;

class MemberAnchorAppService extends MemberService
{
    public function login()
    {
        $mobile   = \YunShop::request()->mobile;
        $password = \YunShop::request()->password;

        $uniacid = \YunShop::app()->uniacid;

        if (\Request::isMethod('post')
            && MemberService::validate($mobile, $password)) {
            $has_mobile = MemberModel::checkMobile($uniacid, $mobile);

            if (!empty($has_mobile)) {
                $password = md5($password . $has_mobile->salt);

                $member_info = MemberModel::getUserInfo($uniacid, $mobile, $password)->first();

            } else {
                return show_json(7, '用户不存在');
            }
            $remain_time = $this->getLoginLimit($mobile);
            if($remain_time){
                return show_json(6, "账号锁定中，请".$remain_time."分钟后再登录");
            }

            if (!empty($member_info)) {
                MemberService::countReset($mobile);
                $member_info = $member_info->toArray();

                $yz_member = MemberShopInfo::getMemberShopInfo($member_info['uid']);

                if (!empty($yz_member)) {
                    $anchor_member = Anchor::getAnchorByMemberId($yz_member->member_id)->first();

                    if (!$anchor_member) {
                        return show_json(-1,"您不是主播");
                    }

                    if ($anchor_member->is_black == 1) {
                        return show_json(-1,"黑名单用户，请联系管理员");
                    }

                    $data = [
                        'shop_name' => \Setting::get('shop.shop.name') ?: '未设置商城名称',
                    ];

                    if (!$yz_member->access_token_2) {
                        $data['token'] = Client::create_token('yz');
                        $yz_member->access_token_2 = $data['token'];
                        $yz_member->save();
                    }
                    $data['token'] = $yz_member->access_token_2;
                    $data['member_id'] = ImService::getImUid('an'.$yz_member->member_id);
					$data['uid'] = $yz_member->member_id;
                    $data['user_sig'] = ImService::genSig('an'.$yz_member->member_id);
                } else {
                    return show_json(7, '用户不存在');
                }

                return show_json(1, '', $data);
            }
            {
                if ($password != $member_info['password']) {
                    $error_count = $this->setLoginLimit($mobile);
                    if ($error_count > 0) {
                        return show_json(6, "密码错误！你还剩" . $error_count . "次机会");
                    } else {
                        return show_json(6, "密码错误次数已达5次，您的账号已锁定，请30分钟之后登录！");
                    }
                }
                return show_json(6, '手机号或密码错误');
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
        \Log::debug('---------native checkLogged--------', [$token, $member->member_id]);
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

    public function PostRegister($openid, $mid)
    {

        $account = AccountWechats::getAccountByUniacid(\YunShop::app()->uniacid);

        $userInfo = $this->getUserInfo($account->key, $account->secret, $openid);

        $member_id = $this->memberLogin($userInfo, $mid);

        return $member_id;
    }

    public function getUserInfo($appId, $appSecret, $openid)
    {
        $share = Setting::get('shop.share');
        $user_info = [];

        if (is_null($share) || $share['follow'] == 1 || ($share && is_null($share['follow']))) {
            $global_access_token_url = app(WechatApi::class)->_getAccessToken($appId, $appSecret);

            $global_token = \Curl::to($global_access_token_url)
                ->asJsonResponse(true)
                ->get();

            $global_userinfo_url = app(WechatApi::class)->_getInfo($global_token['access_token'], $openid);

            $user_info = \Curl::to($global_userinfo_url)
                ->asJsonResponse(true)
                ->get();
        }

        return $user_info;
    }

    /**
     * 公众号开放平台授权登陆
     *
     * @param $uniacid
     * @param $userinfo
     * @return array|int|mixed
     */
    public function unionidLogin($uniacid, $userinfo, $upperMemberId = null)
    {
        $member_id = parent::unionidLogin($uniacid, $userinfo, $upperMemberId, 1);

        return $member_id;
    }

    public function updateMemberInfo($member_id, $userinfo)
    {
        parent::updateMemberInfo($member_id, $userinfo);
        \Log::debug('----update_mapping_fans----', $member_id);
        $record = array(
            //'openid' => $userinfo['openid'],
            'nickname' => stripslashes($userinfo['nickname']),
            'follow' => $userinfo['subscribe'] ?: 0,
            'tag' => base64_encode(serialize($userinfo))
        );

        McMappingFansModel::updateData($member_id, $record);
    }

    public function addMemberInfo($uniacid, $userinfo)
    {
        $uid = parent::addMemberInfo($uniacid, $userinfo);

        \Log::debug('----mapping_fans----', $uid);
        //添加mapping_fans表
        $this->addFansMember($uid, $uniacid, $userinfo);

        return $uid;
    }

    public function addFansMember($uid, $uniacid, $userinfo)
    {
        McMappingFansModel::insertData($userinfo, array(
            'uid' => $uid,
            'acid' => $uniacid,
            'uniacid' => $uniacid,
            'salt' => Client::random(8),
        ));
    }

    /**
     * app扫用户二维码注册为公众号会员
     *
     * @param $openid
     *
     * @return mixed
     */
    public function getFansModel($openid)
    {
        return McMappingFansModel::getFansData($openid);
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
            'uniacid' => $uniacid,
            'unionid' => $unionid,
            'member_id' => $member_id,
            'type' => 1
        ));
    }

    public function updateFansMember($fan, $member_id, $userinfo)
    {
        $record = array(
            'uid'       => $member_id,
            'nickname' => stripslashes($userinfo['nickname']),
            'follow' => isset($userinfo['subscribe']) ? $userinfo['subscribe'] : 0,
            'tag' => base64_encode(serialize($userinfo))
        );

        McMappingFansModel::updateDataById($fan->fanid, $record);
    }

    protected function updateSubMemberInfoV2($uid, $userinfo)
    {
        SubMemberModel::updateOpenid(
            $uid, [
                'yz_openid' => $userinfo['openid'],
                'access_token_1' => $userinfo['access_token'],
                'access_expires_in_1' => time() + $userinfo['expires_in'],
                'refresh_token_1' => $userinfo['refresh_token'],
                'refresh_expires_in_1' => time() + (28 * 24 * 3600)
            ]
        );
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
}