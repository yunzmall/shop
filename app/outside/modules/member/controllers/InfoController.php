<?php
/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 2022/1/7
 * Time: 18:28
 */

namespace app\outside\modules\member\controllers;


use app\common\models\Member;
use app\outside\controllers\OutsideController;

class InfoController extends OutsideController
{
    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function query()
    {
        $mobile = request()->input('phone');


        if (empty($mobile)) {
            return $this->errorJson('搜索条件为空');
        }

//        $member = Member::select('uid', 'nickname', 'realname', 'mobile', 'avatar')
//            ->where('realname', 'like', '%' . $search['member'] . '%')
//            ->orWhere('mobile', 'like', '%' . $search['member'] . '%')
//            ->orWhere('nickname', 'like', '%' . $search['member'] . '%');

        $member_list = Member::uniacid()->select(['uid', 'avatar', 'nickname', 'realname', 'mobile', 'createtime',
            'credit1', 'credit2',])
            ->leftJoin('yz_member_del_log', 'mc_members.uid', '=', 'yz_member_del_log.member_id')
            ->join('yz_member', 'mc_members.uid', '=', 'yz_member.member_id')
            ->where('mc_members.mobile', 'like', '%' . $mobile . '%')
            ->get();

        if ($member_list->isNotEmpty()) {
            $member_list->map(function ($member) {
                $member->createtime =  date('Y-m-d H:i:s', $member->createtime);
                return $member;
            });

        }

        return $this->successJson('list', $member_list);

    }
    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function detail()
    {
        $mobile = request()->input('mobile');
        $uid = request()->input('uid');


        if (empty($mobile)) {
            return $this->errorJson('手机号为空');
        }

        $pattern = "/^1[3456789]\d{9}$/"; // 正则表达式
        if (!preg_match($pattern, $mobile)) {
            return $this->errorJson('手机号码不正确');
        }

        $member = Member::uniacid()->select(['uid', 'avatar', 'nickname', 'realname', 'mobile', 'createtime',
            'credit1', 'credit2',])
            ->leftJoin('yz_member_del_log', 'mc_members.uid', '=', 'yz_member_del_log.member_id')
            ->join('yz_member', 'mc_members.uid', '=', 'yz_member.member_id')
            ->where('mc_members.mobile', $mobile);
        if (isset($uid)) {
            $member = $member->where('uid', $uid);
        }

        $member = $member->first();

        if (empty($member)) {
            return  $this->errorJson('没有该会员');
        }

        $member->createtime =  date('Y-m-d H:i:s', $member->createtime);

        return $this->successJson('成功', $member);

    }
}