<?php
/**
 * Created by PhpStorm.
 *
 * 
 *
 * Date: 2021-08-02
 * Time: 10:47
 */

namespace app\frontend\modules\member\controllers;


use app\common\components\ApiController;
use app\common\facades\Setting;
use app\common\models\Member;
use app\common\models\member\MemberCancel;
use app\common\models\member\MemberCancelSet;
use app\common\models\Order;
use app\common\services\SystemMsgService;

class CancelController extends ApiController
{
    public function index()
    {
        $uid = \YunShop::app()->getMemberId();
        $mobile = request()->mobile;
        if ($mobile) {
            $member = Member::getMemberById($uid);
            if ($member->mobile != $mobile) {
                return $this->errorJson('输入手机号跟当前登录会员手机号不一致');
            }
        }
        $record = MemberCancel::getByUid($uid);
        if ($record) {
            return $this->errorJson('已提交审核。请等待后台审核');
        }
        $data = [
            'uniacid' => \YunShop::app()->uniacid,
            'member_id' => $uid,
            'status' => 1,
        ];
        $res = MemberCancel::create($data);
        if ($res) {
            //【系统消息通知】
            (new SystemMsgService())->applyNotice($res,'member_cancel');
            return $this->successJson('成功');
        } else {
            return $this->errorJson('失败');
        }
    }

    public function cancel()
    {
        $uid = \YunShop::app()->getMemberId();
        $record = MemberCancel::getByUid($uid);
        if (!$record) {
            return $this->errorJson('当前无未审核记录');
        }
        $record->status = 4;
        if ($record->save()) {
            return $this->successJson('取消成功');
        } else {
            return $this->errorJson('取消失败');
        }
    }

    public function getReady()
    {
        $uid = \YunShop::app()->getMemberId();
        $order = Order::where('uid', $uid)->where('status', '<', 3)->first();
        if ($order) {
            return $this->errorJson('您有未完成订单');
        } else {
            return $this->successJson('ok');
        }
    }

    public function getSet()
    {
        $uid = \YunShop::app()->getMemberId();
        $member = Member::getMemberById($uid);
        $country_code = 0;
        $shop_set = Setting::get('shop.sms');
        if ($shop_set['country_code']) {
            $country_code = 1;
        }
        $is_bind_mobile = 0;
        if ($member->mobile) {
            $is_bind_mobile = 1;
        }
        $record = MemberCancel::getByUid($uid);
        $is_record = 0;
        if ($record) {
            $is_record = 1;
        }
        $set = MemberCancelSet::uniacid()->first();
        if ($set) {
            $res = [
                'status' => $set->status,
                'tel_status' => $set->tel_status,
                'title' => $set->title,
                'content' => $set->content,
            ];
        } else {
            $res = [
                'status' => 1,
                'tel_status' => 1,
                'title' => '',
                'content' => '',
            ];
        }
        $add_arr = [
            'is_record' => $is_record,
            'is_bind_mobile' => $is_bind_mobile,
            'country_code' => $country_code,
        ];
        $res = array_merge($res, $add_arr);
        return $this->successJson('ok', [
            'set' => $res,
        ]);
    }
}