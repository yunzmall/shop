<?php


namespace app\backend\modules\coupon\controllers;

use app\backend\modules\coupon\models\CouponLog;
use app\common\components\BaseController;
use app\common\models\coupon\CouponUseLog;
use app\common\models\coupon\ShoppingShareCouponLog;
use app\backend\modules\coupon\models\MemberCoupon;

class MemberCouponController extends BaseController
{
    public function index()
    {
        if (request()->type) {
            return view('coupon.member-unused', [
                'member_id' => request()->member_id,
            ])->render();
        }
        return view('coupon.member')->render();
    }

    public function getData()
    {
        $search = request()->search;
        $list = MemberCoupon::search($search)
            ->select(['yz_member_coupon.uid','used','get_time'])
            ->with(['member' => function($query){
                return $query->select(['uid','avatar', 'nickname','mobile','realname']);
            }])
//            ->withCount([
//                //未使用总数
//                'self as unused_total'=> function($table){
//                    $table->where(['used'=>0,'is_expired'=>0,'is_member_deleted'=>0])->where('uniacid', \YunShop::app()->uniacid);
//                },
//                //已发放总数
//                'couponLog as get_total' => function($table){
//                    $table->where('getfrom', 0)->where('uniacid', \YunShop::app()->uniacid);
//                },
//                //已领取总数
//                'couponLog as get_from_total' => function($table){
//                    $table->where('getfrom', 1)->where('uniacid', \YunShop::app()->uniacid);
//                },
//                //分享领取总数
//                'shareCouponLog as receive_total' => function($table){
//                    $table->where('uniacid', \YunShop::app()->uniacid);
//                },
//                //已使用总数
//                'couponUseLog as used_total' => function($table){
//                    $table->where('uniacid', \YunShop::app()->uniacid);
//                },
//            ])
            ->groupBy('yz_member_coupon.uid')
            ->orderBy('uid', 'desc')
            ->paginate(15);

        $list->map(function ($item) {//这种比上面的更快，还能进一步优化
            $item['unused_total'] = MemberCoupon::uniacid()->where(['used'=>0,'is_expired'=>0,'is_member_deleted'=>0])->where('uid',$item->uid)->count();
            $item['get_total'] = CouponLog::uniacid()->where('getfrom', 0)->where('member_id',$item->uid)->count();
            $item['get_from_total'] = CouponLog::uniacid()->where('getfrom', 1)->where('member_id',$item->uid)->count();
            $item['receive_total'] = ShoppingShareCouponLog::uniacid()->where('receive_uid',$item->uid)->count();
            $item['used_total'] = CouponUseLog::uniacid()->where('member_id',$item->uid)->count();
            return $item;
        });

        $data = [
            'list' => $list,
        ];
        return $this->successJson('ok', $data);
    }

    public function deleteCoupon()
    {
        $coupon_id = (int)request()->id;
        $uid = (int)request()->uid;
        if (!$coupon_id || !$uid) {
            return $this->errorJson('请传入正确参数');
        }
        $member_coupon = MemberCoupon::uniacid()->where(['coupon_id'=>$coupon_id,'uid'=>$uid])->get();
        if ($member_coupon->isEmpty()) {
            return $this->errorJson('优惠券不存在');
        }
        MemberCoupon::uniacid()->where(['coupon_id'=>$coupon_id,'uid'=>$uid])->delete();
        foreach ($member_coupon as $item) {
            $this->createUseLog($item, $coupon_id);
        }
        return $this->successJson('删除成功');
    }

    private function createUseLog($member_coupon, $coupon_id)
    {
        CouponUseLog::create([
            'uniacid' => \YunShop::app()->uniacid,
            'member_id' => $member_coupon->uid,
            'detail' => '后台作废会员(ID为' . $member_coupon->uid . ')一张(ID为' . $coupon_id . '的优惠券)',
            'coupon_id' => $member_coupon->belongsToCommonCoupon->id,
            'member_coupon_id' => $member_coupon->id,
            'type' => CouponUseLog::TYPE_BACKEND_DEL
        ]);
    }

    public function getUnused()
    {
        $search = request()->search;
        $list = MemberCoupon::search($search)
            ->select(['id','uid','used','get_time','coupon_id'])
            ->with(['belongsToCoupon' => function($query){
                return $query->select(['id','name','display_order']);
            }])
            ->groupBy('coupon_id')
            ->orderBy('coupon_id', 'desc')
            ->paginate(15);
        $list->map(function ($item) {
            $item->unused_total = MemberCoupon::uniacid()
                ->where(['uid'=>$item->uid,'coupon_id'=>$item->coupon_id,'used'=>0,'is_expired'=>0,'is_member_deleted'=>0])
                ->count();
        });
        $data = [
            'list' => $list,
        ];
        return $this->successJson('ok', $data);
    }
}
