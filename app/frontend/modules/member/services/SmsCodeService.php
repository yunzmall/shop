<?php


namespace app\frontend\modules\member\services;


use app\common\models\MemberShopInfo;
use app\frontend\models\Member;
use app\frontend\modules\member\models\MemberModel;
use app\backend\modules\charts\modules\phone\models\PhoneAttribution;
use app\backend\modules\charts\modules\phone\services\PhoneAttributionService;
use app\common\helpers\Url;
use app\common\models\MemberGroup;
use app\common\services\Session;
use app\frontend\modules\member\models\MemberWechatModel;
use app\frontend\modules\member\models\SubMemberModel;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;

class SmsCodeService extends MemberService
{
    private $uniacid = 0;

    /**
     * @return array
     */
    public function login()
    {
        $this->uniacid  = \YunShop::app()->uniacid;
        $data = request()->input();
        $redirect_url = request()->yz_redirect;
        if (\Request::isMethod('post')) {
            $this->validate($data);
            //检测验证码
            $checkCode = self::checkCode();
            if ($checkCode['status'] != 1) {
                return show_json(6, $checkCode['json']);
            }
            $memberInfo = MemberModel::checkMobile($this->uniacid, $data['mobile']);
            if (empty($memberInfo)) {
                $memberInfo = $this->register($data);
            }
            if (!empty($memberInfo)) {
                $memberInfo = $memberInfo->toArray();
                $this->save($memberInfo, $this->uniacid);
                $yz_member = MemberShopInfo::getMemberShopInfo($memberInfo['uid']);
                if (!empty($yz_member)) {
                    $yz_member = $yz_member->toArray();
                    $data = MemberModel::userData($memberInfo, $yz_member);
                } else {
                    $data = $memberInfo;
                }
                $data['redirect_url'] = base64_decode($redirect_url);
                return show_json(1, $data);
            } else {
                return show_json(6, "手机号或验证码错误");
            }
        } else {
            return show_json(6,"手机号或验证码错误");
        }
    }

    public static function validate($data)
    {
        $data = array(
            'mobile' => $data['mobile'],
            'password' => $data['code'],
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
        //获取分组
        $default_group = MemberGroup::getDefaultGroupId()->first();
        $member_set = \Setting::get('shop.member');
        if (isset($member_set) && $member_set['headimg']) {
            $head_img = replace_yunshop(tomedia($member_set['headimg']));
        } else {
            $head_img = Url::shopUrl('static/images/photo-mr.jpg');
        }
        $salt = Str::random(8);
        $password = md5(str_random(8) . $salt);
        $mc_member_data = [
            'uniacid' => $this->uniacid,
            'mobile' => $data['mobile'],
            'groupid' => $default_group->id ?: 0,
            'createtime' => $_SERVER['REQUEST_TIME'],
            'nickname' => $data['mobile'],
            'avatar' => $head_img,
            'gender' => 0,
            'residecity' => '',
            'salt' => $salt,
            'password' => $password,
        ];
        $mc_res = MemberModel::create($mc_member_data);
        $member_id = $mc_res->uid;
        $yz_member_data = [
            'member_id' => $member_id,
            'uniacid' => $this->uniacid,
            'group_id' => $default_group->id ?: 0,
            'level_id' => 0,
            'invite_code' => \app\frontend\modules\member\models\MemberModel::getUniqueInviteCode(),
        ];
        SubMemberModel::insertData($yz_member_data);
        //生成分销关系链
        Member::createRealtion($member_id);
        $member = MemberModel::checkMobile($this->uniacid, $data['mobile']);
        if ($data['type'] == 7) {
            $data = [
                'uniacid' => $this->uniacid,
                'member_id' => $member_id,
                'openid' => $data['mobile'],
                'nickname' => $data['mobile'],
                'gender' => 0,
                'avatar' => $head_img,
                'province' => '',
                'city' => '',
                'country' => '',
                'uuid' => $data['uuid']
            ];
            MemberWechatModel::insertData($data);
        }
        return $member;
    }

    /**
     * 验证登录状态
     *
     * @return bool
     */
    public function checkLogged()
    {
        return MemberService::isLogged();
    }
}