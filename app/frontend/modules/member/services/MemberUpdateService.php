<?php
/**
 * Created by PhpStorm.
 * User: weifeng
 * Date: 2020-08-12
 * Time: 16:18
 *
 *    .--,       .--,
 *   ( (  \.---./  ) )
 *    '.__/o   o\__.'
 *       {=  ^  =}
 *        >  -  <
 *       /       \
 *      //       \\
 *     //|   .   |\\
 *     "'\       /'"_.-~^`'-.
 *        \  _  /--'         `
 *      ___)( )(___
 *     (((__) (__)))     梦之所想,心之所向.
 */

namespace app\frontend\modules\member\services;


use app\common\exceptions\ShopException;
use app\common\models\AccountWechats;
use app\common\models\McMappingFans;
use app\frontend\modules\member\models\MemberModel;

class MemberUpdateService
{
    public $type;

    public function __construct($type)
    {
        $this->type = $type;
    }

    public function update()
    {
        $result = [];
        switch ($this->type) {
            case 1 :
                $result = $this->fans();
                break;
            case 2 :
                $result = $this->mini();
                break;
        }

        return $result;
    }

    public function fans()
    {
        $uniacid = \YunShop::app()->uniacid;

        $McFans = McMappingFans::uniacid()->where('uid', \YunShop::app()->getMemberId())->first()->toArray();
        if (empty($McFans)) {
            return [
                'status' => 0,
                'message' => '登录信息不完整',
            ];
        }

        if ($McFans['follow'] == 0) {
            return [
                'status' => 0,
                'message' => '未关注当前公众号，无法获取会员信息',
            ];
        }

        $account = AccountWechats::getAccountByUniacid($uniacid);
        $appId = $account->key;
        $appSecret = $account->secret;

        $global_access_token_url = $this->_getAccessToken($appId, $appSecret);

        $global_token = \Curl::to($global_access_token_url)
            ->asJsonResponse(true)
            ->get();

        $global_userinfo_url = $this->_getInfo($global_token['access_token'], $McFans['openid']);

        $user_info = \Curl::to($global_userinfo_url)
            ->asJsonResponse(true)
            ->get();

        if (isset($user_info['errcode'])) {
            return [
                'status' => 0,
                'message' => $user_info['errmsg']
            ];
        }

        //todo 更新会员信息
        $this->updateMemberInfo(\YunShop::app()->getMemberId(), $user_info);

        return [
            'status' => 1,
            'message' => '更新成功',
        ];
    }

    public function mini()
    {
        $min_set = \Setting::get('plugin.min_app');

        if (is_null($min_set) || 0 == $min_set['switch']) {
            return [
                'status' => 0,
                'message' => '未开启小程序',
            ];
        }

        $para = \YunShop::request();

        $paras = json_decode($para['info']['rawData'], true);

        if (!empty($paras)) {
            $json_user['nickname']   = $paras['nickName'];
            $json_user['headimgurl'] = $paras['avatarUrl'];
            $json_user['sex']        = $paras['gender'];

            //todo 更新会员信息
            $this->updateMemberInfo(\YunShop::app()->getMemberId(), $json_user);

            return [
                'status' => 1,
                'message' => '更新成功',
            ];
        } else {
            return [
                'status' => 0,
                'message' => '获取用户信息失败',
            ];
        }
    }

    /**
     * 更新会员信息
     *
     * @param $member_id
     * @param $userinfo
     */
    public function updateMemberInfo($member_id, $userinfo)
    {
        //更新mc_members
        $mc_data = array(
            'nickname' => isset($userinfo['nickname'])  ? stripslashes($userinfo['nickname']) : '',
            'avatar' => isset($userinfo['headimgurl']) ? $userinfo['headimgurl'] : '',
            'gender' => isset($userinfo['sex']) ? $userinfo['sex'] : '-1',
            'nationality' => isset($userinfo['country']) ? $userinfo['country'] : '',
            'resideprovince' => isset($userinfo['province']) ? $userinfo['province'] : '' . '省',
            'residecity' => isset($userinfo['city']) ? $userinfo['city'] : '' . '市'
        );

        MemberModel::updataData($member_id, $mc_data);
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
        $share = \Setting::get('shop.share');
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
     * 是否关注公众号
     *
     * @param $accesstoken
     * @param $openid
     * @return string
     */
    private function _getInfo($accesstoken, $openid)
    {
        return 'https://api.weixin.qq.com/cgi-bin/user/info?access_token=' . $accesstoken . '&openid=' . $openid;
    }
}