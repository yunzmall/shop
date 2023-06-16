<?php
/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 2022-10-12
 * Time: 16:43
 */

namespace app\common\services\member;


use app\common\facades\Setting;
use app\common\models\CouponLog;
use app\common\models\member\MemberMerge;
use app\common\models\MemberCoupon;
use app\common\services\credit\ConstService;
use app\common\services\finance\BalanceChange;
use app\common\services\finance\PointService;
use Illuminate\Support\Facades\DB;
use Yunshop\Love\Common\Models\MemberLove;
use Yunshop\Love\Common\Services\LoveChangeService;

class MemberMergeService
{
    public $merge_data;
    public $hold_uid;
    public $give_up_uid;

    public function __construct($hold_uid, $give_up_uid, $merge_data)
    {
        $this->hold_uid = $hold_uid;
        $this->give_up_uid = $give_up_uid;
        $this->merge_data = $merge_data;
    }
    
    public function handel()
    {
        $this->changePoint();
        $this->changeLove();
        $this->changeAmount();
        $this->changeCoupon();
        $this->changeProductMarket();
        \Log::debug('---会员合并服务处理---数据---', $this->merge_data);
        MemberMerge::create($this->merge_data);//合并记录
    }

    public function changePoint()
    {
        //积分
        $change_point = $this->merge_data['before_point'];
        $point_data = [
            'point_mode' => PointService::POINT_MODE_MEMBER_MERGE,
            'member_id' => $this->hold_uid,
            'point' => $change_point,
            'remark' => '[会员合并转入：会员ID:'. $this->hold_uid . '积分' . $change_point .']',
            'point_income_type' => PointService::POINT_INCOME_GET
        ];
        $pointService = new PointService($point_data);
        $pointService->changePoint();
    }

    public function changeAmount()
    {
        //余额
        $change_amount = $this->merge_data['before_amount'];
        $data = [
            'member_id' => $this->hold_uid,
            'remark' => '[会员合并转入：会员ID:' . $this->hold_uid . '余额' . $change_amount . '元]',
            'source' => ConstService::MEMBER_MERGE,
            'relation' => '',
            'operator' => ConstService::OPERATOR_SHOP,
            'operator_id' => $this->hold_uid,
            'change_value' => $change_amount,
        ];
        (new BalanceChange())->memberMerge($data);
    }

    public function changeLove()
    {
        if (app('plugins')->isEnabled('love')) {
            $hold_member_love_usable = MemberLove::uniacid()->where('member_id', $this->hold_uid)->value('usable');
            $give_up_member_love_usable = MemberLove::uniacid()->where('member_id', $this->give_up_uid)->value('usable');
            $hold_member_love_froze = MemberLove::uniacid()->where('member_id', $this->hold_uid)->value('froze');
            $give_up_member_love_froze = MemberLove::uniacid()->where('member_id', $this->give_up_uid)->value('froze');
            $after_love_usable = bcadd($hold_member_love_usable, $give_up_member_love_usable, 2);
            $after_love_froze = bcadd($hold_member_love_froze, $give_up_member_love_froze, 2);
            $this->merge_data = array_merge($this->merge_data, [
                'before_love_usable' => $give_up_member_love_usable?:0.00,
                'after_love_usable' => $after_love_usable?:0.00,
                'before_love_froze' => $give_up_member_love_froze?:0.00,
                'after_love_froze' => $after_love_froze?:0.00,
            ]);
            //可用
            $change_usable_love = $this->merge_data['before_love_usable'];
            $love_set = Setting::get('love');
            if ($change_usable_love > 0) {
                $usable_love_name = $love_set['usable_name'] ?: $love_set['name'] ?: '爱心值';
                $usable_love_data = [
                    'member_id' => $this->hold_uid,
                    'change_value' => $change_usable_love,
                    'operator' => 0,
                    'operator_id' => 0,
                    'remark' => '[会员合并转入：会员ID:'.$this->hold_uid.$usable_love_name.$change_usable_love.']',
                    'relation' => ''
                ];
                (new LoveChangeService('usable'))->memberMerge($usable_love_data);
            }
            //冻结
            $change_froze_love = $this->merge_data['before_love_froze'];
            if ($change_froze_love > 0) {
                $froze_love_name = $love_set['unable_name'] ?: $love_set['name'] ? '白'.$love_set['name'] : '白爱心值';
                $froze_love_data = [
                    'member_id' => $this->hold_uid,
                    'change_value' => $change_froze_love,
                    'operator' => 0,
                    'operator_id' => 0,
                    'remark' => '[会员合并转入：会员ID:'.$this->hold_uid.$froze_love_name.$change_froze_love.']',
                    'relation' => ''
                ];
                (new LoveChangeService('froze'))->memberMerge($froze_love_data);
            }
        }
    }

    public function changeCoupon()
    {
        $give_up_coupons = MemberCoupon::uniacid()->where(['uid'=>$this->give_up_uid])->get();
        foreach ($give_up_coupons as $coupon) {
            $coupon->update(['uid'=>$this->hold_uid]);
            CouponLog::create([
                'uniacid' => $coupon->uniacid,
                'logno' => '会员合并转入: 会员【ID:' . $this->hold_uid . '】获得优惠券 1张【优惠券ID:' . $coupon->coupon_id . '】',
                'member_id' => $this->hold_uid,
                'couponid' => $coupon->coupon_id,
                'paystatus' => 0,
                'creditstatus' => 0,
                'paytype' => 0,
                'getfrom' => CouponLog::MEMBER_MERGE,
                'status' => 0,
                'createtime' => time(),
            ]);
        }
    }

    //处理应用市场客户管理数据
    private function changeProductMarket()
    {
        if (app('plugins')->isEnabled('product-market')) {
            DB::table('yz_product_market_client')->where('member_id', $this->give_up_uid)->update(['member_id'=>$this->hold_uid]);
        }
    }
}