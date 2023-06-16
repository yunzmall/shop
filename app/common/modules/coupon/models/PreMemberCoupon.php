<?php
/**
 * Created by PhpStorm.
 * User: shenyang
 * Date: 2018/12/27
 * Time: 10:57 PM
 */

namespace app\common\modules\coupon\models;

use app\common\exceptions\AppException;
use app\common\models\Coupon;
use app\common\models\Member;
use app\common\models\MemberCoupon;
use app\common\models\MemberShopInfo;
use app\common\modules\coupon\events\AfterMemberReceivedCoupon;
use Illuminate\Support\Facades\DB;
use Yunshop\BindCoupon\models\BindCouponLog;

class PreMemberCoupon extends MemberCoupon
{
    /**
     * @var Member $member
     */
    private $member;
    /**
     * @var Coupon $coupon
     */
    private $coupon;

    private $exchange_total;

    private $integralCanExchange;

    /**
     * @param Member $member
     * @param Coupon $coupon
     * @param int $exchange_total
     */
    public function init(Member $member, Coupon $coupon, $exchange_total = 1)
    {
        $this->member = $member;
        $this->coupon = $coupon;
        $this->exchange_total = $exchange_total;
        $this->initAttributes();
    }

    private function initAttributes()
    {
        $data = [
            'uniacid' => $this->member->uniacid,
            'uid' => $this->member->uid,
            'coupon_id' => $this->coupon->id,
            'get_type' => 1,
            'get_time' => time(),
        ];
        $this->fill($data);
    }

    /**
     * @throws AppException
     */
    public function generate()
    {
        $this->verify($this->member->yzMember, $this->coupon, $this->exchange_total);
        $validator = $this->validator();
        if ($validator->fails()) {
            throw new AppException('领取失败', $validator->messages());
        }
        if ($this->exchange_total == 1) {
            if (request()->is_bind_coupon) {
                DB::transaction(function (){
                    if (request()->bind_coupon_share_uid){
                        if (!$share_member_coupon = MemberCoupon::uniacid()
                            ->where('used', 0)
                            ->where('is_member_deleted', 0)
                            ->where('is_expired', 0)
                            ->where('uid', request()->bind_coupon_share_uid)
                            ->where('coupon_id', $this->coupon->id)
                            ->lockForUpdate()
                            ->first()){
                            throw new AppException('分享人无可用优惠券');
                        }
                        $share_member_coupon->used = 1;
                        $share_member_coupon->use_time = time();
                        $share_member_coupon->save();
                        $share_member_coupon_id = $share_member_coupon->id;
                    }else{
                        $share_member_coupon_id = 0;
                    }

                    if (!BindCouponLog::uniacid()
                        ->where('status', 0)
                        ->where('coupon_sn', request()->bind_coupon_sn)
                        ->update([
                            'status' => 1,
                            'finish_time' => time(),
                            'uid' => $this->member->uid,
                            'share_coupon_id' => $share_member_coupon_id,
                        ])) {
                        throw new AppException('绑定优惠券修改失败');
                    }
                });
            }
            $this->save();
        } else {
            for ($i = 1; $i <= $this->exchange_total; $i++) {
                $insertData[] = [
                    'uniacid' => $this->member->uniacid,
                    'uid' => $this->member->uid,
                    'coupon_id' => $this->coupon->id,
                    'get_type' => 1,
                    'get_time' => time(),
                ];
            }
            if (empty($insertData)) {
                throw new AppException('领取优惠券失败');
            }
            static::insert($insertData);
        }

        if (!empty($this->integralCanExchange)) {
            //扣除兑换优惠券需要的消费积分
            $this->deductIntegral();
        }

        event(new AfterMemberReceivedCoupon($this,$this->exchange_total));
    }

    /**
     * @param MemberShopInfo $yzMember
     * @param Coupon $coupon
     * @param $exchange_total
     * @throws AppException
     */
    public function verify(MemberShopInfo $yzMember, Coupon $coupon, $exchange_total = 1)
    {
        $special_type = 0;
        if (app('plugins')->isEnabled('bind-coupon')
            && request()->bind_coupon_sn
            && ($this->exchange_total == 1)
        ) {
            if ($bind_coupon = BindCouponLog::uniacid()
                ->where('status', 0)
                ->where('coupon_sn', request()->bind_coupon_sn)
                ->where('coupon_id', $coupon->id)
                ->first()) {
                $special_type = 1;
                request()->offsetSet('is_bind_coupon', 1);
                request()->offsetSet('bind_coupon_share_uid', $bind_coupon->share_uid ?: 0);
            }
        }
        if (!$coupon->available($special_type)) {
            throw new AppException('没有该优惠券或者优惠券不可用');
        }
        if (!empty($coupon->level_limit) && ($coupon->level_limit != -1)) { //优惠券有会员等级要求
            // 通过会员记录的level_id找到会员等级
            $memberLevel = \app\common\models\MemberLevel::find($yzMember->level_id)->level;
            // 通过优惠券记录的level_id找到会员等级,level_limit实际就是level_id
            $couponMemberLevel = \app\common\models\MemberLevel::find($coupon->level_limit)->level;
            if (empty($yzMember->level_id)) {
                throw new AppException('该优惠券有会员等级要求,但该用户没有会员等级');
            } elseif ((!empty($memberLevel) ? $memberLevel : 0) < $couponMemberLevel) {
                throw new AppException('没有达到领取该优惠券的会员等级要求');
            }
        }

        //判断优惠券是否过期
        $timeLimit = $coupon->time_limit;

        if ($timeLimit == 1 && (time() > $coupon->time_end->timestamp)) {
            throw new AppException('优惠券已过期');

        }

        //是否达到个人领取上限
        $counts = self::where('uid', $yzMember->member_id)->where('coupon_id', $coupon->id)->where('get_type',1);
        $count = $counts->count();
        if ($exchange_total > 1) {
            $count += ($exchange_total-1);//领取多张需要增加当前数量进行判断
        }

        if ($count >= $coupon->get_max && ($coupon->get_max != -1)) {
            throw new AppException('已经达到个人领取上限',['reason' => '每人限领' . $coupon->get_max . '张,当前已领取' . $counts->count() . '张']);
        }

        $today_count = $counts->where('get_time','>',strtotime(date('Y-m-d',time())))->count();
        if($coupon->get_limit_type == 1 and $coupon->get_limit_max != -1 and $today_count >= $coupon->get_limit_max){
            throw new AppException('今日领取已达上限',['reason' => '每人每天限领' . $coupon->get_limit_max . '张']);
        }

        //验证是否达到优惠券总数上限
        if ($coupon->getReceiveCount() >= $coupon->total && ($coupon->total != -1)) {
            throw new AppException('该优惠券已经被抢光');
        }

        //验证会员标签
        if (app('plugins')->isEnabled('member-tags')) {
            $memberTags = \Yunshop\MemberTags\Common\models\MemberTagsRelationModel::uniacid()->where('member_id', $yzMember->member_id)->pluck('tag_id');
            if (!empty($coupon->member_tags_ids) && empty(array_intersect($memberTags->toArray(), $coupon->member_tags_ids))) {
                throw new AppException('不符合领取该优惠券的标签分组要求');
            }
        }

        //消费积分兑换优惠券
        if (app('plugins')->isEnabled('integral')) {
            $integralName = \Yunshop\Integral\Common\Services\SetService::getIntegralName();
            $integralMember = (float)\Yunshop\Integral\Common\Models\IntegralMemberModel::uniacid()->where('uid', $yzMember->member_id)->pluck('integral')->first(); //用户消费积分余额
            if ($coupon->is_integral_exchange_coupon && !empty($coupon->exchange_coupon_integral)) {
                if (bccomp($coupon->exchange_coupon_integral, $integralMember) == 1) {
                    $exchangeIntegralMsg = str_replace('消费积分', $integralName, '兑换需要' . $coupon->exchange_coupon_integral . '消费积分，当前消费积分 ' . $integralMember . '，消费积分不足，不能进行兑换');
                    $this->integralCanExchange = 0;
                    throw new AppException($exchangeIntegralMsg);
                } else {
                    $this->integralCanExchange = 1;
                }
            }
        }
    }

    private function deductIntegral()
    {
        $changeValue = bcmul($this->coupon->exchange_coupon_integral,$this->exchange_total,2);
        $plugin_name = INTEGRAL_NAME ?: '消费积分';
        try {
            $order_sn = MemberCoupon::createOrderSn('DIEC');//DIEC扣除消费积分兑换优惠券
            $changeData = [
                'uid' => $this->member->uid,
                'uniacid' => $this->member->uniacid,
                'change_value' => $changeValue,
                'order_sn' => $order_sn,
                'source_type' => self::class,
                'source_id' => \Yunshop\Integral\Common\Services\ConstService::EXCHANGE_COUPON_DEDUCT,
                'remark' => $plugin_name.'兑换优惠券',
                'type' => 0,//0为减
            ];
            (new \Yunshop\Integral\Common\Services\IntegralChangeServer())->exchangeCouponDeduct($changeData);
        } catch (\Exception $e) {
            \Log::error('消费积分兑换优惠券错误：' . $e->getMessage());
        }
    }
}