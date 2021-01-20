<?php
/**
 * Created by PhpStorm.
 * User: Merlin
 * Date: 2020/12/28
 * Time: 18:12
 */

namespace app\common\services\member;


use app\backend\modules\charts\modules\phone\models\PhoneAttribution;
use app\backend\modules\charts\modules\phone\services\PhoneAttributionService;
use app\common\exceptions\AppException;
use app\common\helpers\Url;
use app\common\models\Member as Members;
use app\common\models\MemberGroup as Member_Group;
use app\common\models\MemberShopInfo as MemberShop_Info;
use app\frontend\modules\member\models\MemberModel;
use app\frontend\modules\member\models\SubMemberModel as SubMember_Model;
use Illuminate\Support\Str;

class MemberService
{
    public static function addMember($data)
    {
        $uniacid = \YunShop::app()->uniacid;
        $mobile = $data['mobile'];
        $password = $data['password'];
        //获取图片
        $member_set = \Setting::get('shop.member');
        \Log::info('member_set', $member_set);
        if (isset($member_set) && $member_set['headimg']) {
            $avatar = yz_tomedia($member_set['headimg']);
        } else {
            $avatar = Url::shopUrl('static/images/photo-mr.jpg');
        }
        //判断是否已注册
        $member_info = MemberModel::getId($uniacid, $mobile);
        \Log::info('member_info', $member_info);

        if (!empty($member_info)) {
            throw new AppException('该手机号已被注册');
        }

        //添加mc_members表
        $default_groupid = Member_Group::getDefaultGroupId($uniacid)->first();
        \Log::info('default_groupid', $default_groupid);
        $data = array(
            'uniacid' => $uniacid,
            'mobile' => $mobile,
            'groupid' => $default_groupid->id ? $default_groupid->id : 0,
            'createtime' => time(),
            'nickname' => $mobile,
            'avatar' => $avatar,
            'gender' => 0,
            'residecity' => '',
        );
        //随机数
        $data['salt'] = Str::random(8);
        \Log::info('salt', $data['salt']);

        //加密
        $data['password'] = md5($password . $data['salt']);
        $memberModel = MemberModel::create($data);
        $member_id = $memberModel->uid;

        //手机归属地查询插入
        $phoneData = file_get_contents((new PhoneAttributionService())->getPhoneApi($mobile));
        $phoneArray = json_decode($phoneData);
        $phone['uid'] = $member_id;
        $phone['uniacid'] = $uniacid;
        $phone['province'] = $phoneArray->data->province;
        $phone['city'] = $phoneArray->data->city;
        $phone['sp'] = $phoneArray->data->sp;

        $phoneModel = new PhoneAttribution();
        $phoneModel->updateOrCreate(['uid' => $member_id], $phone);

        //默认分组表
        //添加yz_member表
        $default_sub_group_id = Member_Group::getDefaultGroupId()->first();

        if (!empty($default_sub_group_id)) {
            $default_subgroup_id = $default_sub_group_id->id;
        } else {
            $default_subgroup_id = 0;
        }

        $sub_data = array(
            'member_id' => $member_id,
            'uniacid' => $uniacid,
            'group_id' => $default_subgroup_id,
            'level_id' => 0,
            'invite_code' => \app\frontend\modules\member\models\MemberModel::generateInviteCode(),
        );

        //添加用户子表
        SubMember_Model::insertData($sub_data);
        //生成分销关系链
        Members::createRealtion($member_id);

//            $cookieid = "__cookie_yun_shop_userid_{$uniacid}";
//            Cookie::queue($cookieid, $member_id);
//            Session::set('member_id', $member_id);

        $password = $data['password'];
        $member_info = MemberModel::getUserInfo($uniacid, $mobile, $password)->first();
        $yz_member = MemberShop_Info::getMemberShopInfo($member_id)->toArray();
        $data = MemberModel::userData($member_info, $yz_member);
        return $data;
    }
}