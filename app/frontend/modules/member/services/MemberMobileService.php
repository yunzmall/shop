<?php
/**
 * Created by PhpStorm.
 * Author:
 * Date: 17/2/22
 * Time: 下午4:12
 */

namespace app\frontend\modules\member\services;

use app\common\models\MemberShopInfo;
use app\common\services\Session;
use app\frontend\models\Member;
use app\frontend\modules\member\models\MemberModel;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Redis;

class MemberMobileService extends MemberService
{
    public function login()
    {
        $mobile   = \YunShop::request()->mobile;
        $password = \YunShop::request()->password;
        $uniacid  = \YunShop::app()->uniacid;

        $redirect_url = request()->yz_redirect;
        if (\Request::isMethod('post') && MemberService::validate($mobile, $password)) {
            $has_mobile = MemberModel::checkMobile($uniacid, $mobile);

            if (!empty($has_mobile)) {
                $password = md5($password. $has_mobile->salt);
                $member_info = MemberModel::getUserInfo($uniacid, $mobile, $password)->first();

				if (is_null($member_info)) {
					$error_count = $this->setLoginLimit($mobile);
					if ($error_count > 0) {
						return show_json(6, "密码错误！你还剩" . $error_count . "次机会");
					} else {
						return show_json(6, "密码错误次数已达5次，您的账号已锁定，请30分钟之后登录！");
					}
				}
            } else {
                return show_json(7, "用户不存在");
            }

            $remain_time = $this->getLoginLimit($mobile);
            if($remain_time){
                return show_json(6, "账号锁定中，请".$remain_time."分钟后再登录");
            }

            if(!empty($member_info)){
                MemberService::countReset($mobile);
				$member_info = $member_info->toArray();
                //生成分销关系链
                Member::createRealtion($member_info['uid']);

                $this->save(array_add($member_info,'password',$password), $uniacid);

				$data['uif'] = $member_info['uid'];
                $data['redirect_url'] = base64_decode($redirect_url);

                return show_json(1, $data);
            }
        } else {
            return show_json(6,"手机号或密码错误");
        }

    }

    /**
     * 验证登录状态
     *
     * @return bool
     */
    public function checkLogged()
    {
        $member = null;
        $member_id = \YunShop::app()->getMemberId();

        if ($member_id) {
            $member = Member::getMemberByUid($member_id)->first();

            if ($member) {
                return true;
            }
        }

        if (isset($_COOKIE['Yz-appToken'])) {
            try {
                $yz_token = decrypt($_COOKIE['Yz-appToken']);

                list($mobile, $uid , $md5_password) = explode('\t', $yz_token);
            } catch (DecryptException $e) {
                return false;
            }

            $member = Member::getMemberByUid($uid)->first();

            if ($member && $md5_password == md5($member['password'])) {
                Session::set('member_id', $member->uid);

                return true;
            }
        }

        return false;
    }
}