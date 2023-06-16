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
use app\common\facades\EasyWeChat;
use app\common\models\AccountWechats;
use app\common\models\McMappingFans;
use app\frontend\modules\member\models\McMappingFansModel;
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
        $McFans = McMappingFans::uniacid()->where('uid', \YunShop::app()->getMemberId())->first();
        if (isset($McFans)) {
            $McFans = $McFans->toArray();
        }
        
        if (empty($McFans)) {
            return [
                'status' => 0,
                'message' => '登录信息不完整',
            ];
        }

        $app = EasyWeChat::officialAccount();

        $user_info = $app->user->get($McFans['openid']);

        if ($user_info['subscribe'] == 0) {
            $this->updateFansInfo(\YunShop::app()->getMemberId(), $user_info);

            return [
                'status' => 0,
                'message' => '未关注当前公众号，无法获取会员信息',
            ];
        }

        //todo 更新会员信息
        $this->updateMemberInfo(\YunShop::app()->getMemberId(), $user_info);
        $this->updateFansInfo(\YunShop::app()->getMemberId(), $user_info);

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

        $paras = $para['info'];

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
//            'gender' => isset($userinfo['sex']) ? $userinfo['sex'] : '-1',
            'nationality' => isset($userinfo['country']) ? $userinfo['country'] : '',
            'resideprovince' => isset($userinfo['province']) ? $userinfo['province'] : '' . '省',
            'residecity' => isset($userinfo['city']) ? $userinfo['city'] : '' . '市'
        );

        MemberModel::updataData($member_id, $mc_data);
    }

    /**
     * 更新关注信息
     *
     * @param $member_id
     * @param $userinfo
     */
    public function updateFansInfo($member_id, $userinfo)
    {
        //更新mc_members
        $data = array(
            'follow' => $userinfo['subscribe'],
            'followtime' => $userinfo['subscribe_time'] ?: ''
        );

        McMappingFansModel::updateData($member_id, $data);
    }
}