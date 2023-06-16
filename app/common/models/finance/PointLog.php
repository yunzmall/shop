<?php
/**
 * Created by PhpStorm.
 * Author:
 * Date: 2017/4/10
 * Time: 下午5:47
 */

namespace app\common\models\finance;


use app\common\models\BaseModel;
use app\common\models\Member;
use app\common\models\Order;
use app\common\observers\point\PointChangeObserver;
use app\common\services\finance\PointService;

/**
 * @method static self searchMember($search = [])
 * Class PointLog
 * @package app\common\models\finance
 */
class PointLog extends BaseModel
{
    protected $table = 'yz_point_log';

    protected $guarded = [''];

    protected $search_fields = ['id'];

    protected $appends = ['source_name'];

    public static function boot()
    {
        parent::boot();
        self::observe(PointChangeObserver::class);
    }

    public function hasOneOrder()
    {
        return $this->hasOne(Order::class, 'id', 'order_id');
    }

    public function member()
    {
        return $this->belongsTo(Member::class, 'member_id', 'uid');
    }

    public function getSourceNameAttribute()
    {
        return $this->getSourceNameComment($this->attributes['point_mode']);
    }

    public function getSourceNameComment($sourceAttribute)
    {
        return isset($this->sourceComment()[$sourceAttribute]) ? $this->sourceComment()[$sourceAttribute] : "未知变动";
    }

    /**
     * @param static $query
     * @param array $search
     */
    public function scopeSearch($query, $search = [])
    {
        if ($search['source']) {
            $query->where("point_mode", $search['source']);
        }
        if ($search['income_type']) {
            $query->where("point_income_type", $search['income_type']);
        }
        if ($search['search_time']) {
            $query->whereBetween('created_at', [strtotime($search['time']['start']), strtotime($search['time']['end'])]
            );
        }
        $query->searchMember($search);
    }

    /**
     * @param static $query
     * @param array $search
     */
    public function scopeSearchMember($query, $search = [])
    {
        if ($search['member'] || $search['level_id'] || $search['group_id'] || $search['member_id']) {
            $query->whereHas('member', function ($query) use ($search) {
                /**
                 * @var Member $query
                 */
                $query->search($search);
            });
        }
    }

    /**
     * todo 原有机制优化，临时使用，可以优化为 Key => value，自动加载模式
     *
     * @return array
     */
    public function sourceComment()
    {
        return [
            PointService::POINT_MODE_GOODS                              => '商品赠送',
            PointService::POINT_MODE_ORDER                              => '订单赠送',
            PointService::POINT_MODE_POSTER                             => '超级海报',
            PointService::POINT_MODE_ARTICLE                            => '文章营销',
            PointService::POINT_MODE_ADMIN                              => '后台充值',
            PointService::POINT_MODE_BY                                 => '购物抵扣',
            PointService::POINT_MODE_TEAM                               => '团队奖励',
            PointService::POINT_MODE_LIVE                               => '生活缴费奖励',
            PointService::POINT_MODE_AIR                                => '飞机票奖励',
            PointService::POINT_MODE_CASHIER                            => '收银台奖励',
            PointService::POINT_MODE_STORE                              => '门店奖励',
            PointService::POINT_MODE_HOTEL_CASHIER                      => '酒店收银台奖励',
            PointService::POINT_MODE_HOTEL                              => '酒店奖励',
            PointService::POINT_MODE_RECHARGE                           => '话费充值奖励',
            PointService::POINT_MODE_FLOW                               => '流量充值奖励',
            PointService::POINT_MODE_TRANSFER                           => '转让-转出',
            PointService::POINT_MODE_RECIPIENT                          => '转让-转入',
            PointService::POINT_MODE_ROLLBACK                           => '返还',
            PointService::POINT_MODE_COUPON_DEDUCTION_AWARD             => '优惠券抵扣奖励',
            PointService::POINT_MODE_TRANSFER_LOVE                      => $this->transferLoveName(),
            PointService::POINT_MODE_RECHARGE_CODE                      => '充值码充值',
            PointService::POINT_MODE_TASK_REWARD                        => '任务奖励',
            PointService::POINT_MODE_SIGN_REWARD                        => $this->signAwardName(),
            PointService::POINT_MODE_COURIER_REWARD                     => '快递单奖励',
            PointService::POINT_MODE_FROZE_AWARD                        => $this->frozeAwardName(),
            PointService::POINT_MODE_COMMUNITY_REWARD                   => '圈子签到奖励',
            PointService::POINT_MODE_CREATE_ACTIVITY                    => '创建活动',
            PointService::POINT_MODE_ACTIVITY_OVERDUE                   => '活动失效',
            PointService::POINT_MODE_RECEIVE_ACTIVITY                   => '领取活动',
            PointService::POINT_MODE_RECEIVE_OVERDUE                    => '领取失效',
            PointService::POINT_MODE_COMMISSION_TRANSFER                => '分销佣金转入',
            PointService::POINT_MODE_EXCEL_RECHARGE                     => 'EXCEL充值',
            PointService::POINT_MODE_CARD_VISIT_REWARD                  => '名片访问奖励',
            PointService::POINT_MODE_CARD_REGISTER_REWARD               => '名片新增会员奖励',
            PointService::POINT_MODE_PRESENTATION                       => '推荐客户奖励',
            PointService::POINT_MODE_LOVE_WITHDRAWAL_DEDUCTION          => $this->loveDeductionName(),
            PointService::POINT_MODE_FIGHT_GROUPS_TEAM_SUCCESS          => '拼团活动团长奖励',
            PointService::POINT_MODE_DRAW_CHARGE_GET                    => '抽奖获得',
            PointService::POINT_MODE_DRAW_CHARGE_DEDUCTION              => '抽奖使用扣除',
            PointService::POINT_MODE_DRAW_REWARD_GET                    => '抽奖奖励',
            PointService::POINT_MODE_CONVERT                            => '兑换',
            PointService::POINT_MODE_THIRD                              => '第三方变动',
            PointService::POINT_MODE_CONSUMPTION_POINTS                 => '消费充值奖励',
            PointService::POINT_MODE_ROOM_MEMBER_ACTIVITY_POINTS        => '直播会员观看奖励',
            PointService::POINT_MODE_ROOM_ACTIVITY_POINTS               => '直播间会员奖励',
            PointService::POINT_MODE_ROOM_ANCHOR_ACTIVITY_POINTS        => '直播主播奖励',
            PointService::POINT_MODE_ROOM_REWARD_TRANSFER_POINTS        => '直播打赏-支出',
            PointService::POINT_MODE_ROOM_REWARD_RECIPIENT_POINTS       => '直播打赏-收入',
            PointService::POINT_AUCTION_REWARD_RECIPIENT_POINTS         => '拍卖奖励',
            PointService::POINT_INCOME_WITHDRAW_AWARD                   => '收入提现奖励',
            PointService::POINT_MODE_TRANSFER_BALANCE                   => "自动转入余额",
            PointService::POINT_MODE_BIND_MOBILE                        => "绑定手机号奖励",
            PointService::POINT_MODE_LAYER_CHAIN                        => "关系链等级奖励",
            PointService::POINT_MODE_LAYER_CHAIN_RECHARGE               => "层链充值",
            PointService::POINT_MODE_DRAW_NEW_MEMBER_PRIZE              => '新人奖奖励',
            PointService::POINT_MODE_LAYER_CHAIN_QUESTIONNAIRE          => "问卷奖励",
            PointService::POINT_MODE_HEALTH_ASSESSMENT                  => "健康测评奖励",
            PointService::POINT_MODE_MICRO_COMMUNITIES                  => "微社区发帖奖励",
            PointService::POINT_MODE_CONFERENCE                         => "会务活动签到奖励",
            PointService::POINT_INCOME_WITHDRAW_AWARD_SCALE             => "收入提现奖励比例",
            PointService::POINT_MODE_STORE_SHAREHOLDER                  => "门店股东升级奖励",
            PointService::POINT_MODE_ANSWER_REWARD                      => "短视频答题奖励",
            PointService::POINT_MODE_ANSWER_REWARD_PARENT               => "短视频答题上级奖励",
            PointService::POINT_MODE_POINT_EXCHANGE                     => "蓝牛积分兑换",
            PointService::POINT_MODE_SNATCH_REGIMENT                    => "抢团奖励",
            PointService::POINT_MODE_FIGHT_GROUPS_LOTTERY_WIN           => "拼团抽奖成功奖励",
            PointService::POINT_MODE_FIGHT_GROUPS_LOTTERY_LOSER         => "拼团抽奖失败奖励",
            PointService::POINT_MODE_COMMUNITY_RELAY                    => "社群接龙奖励",
            PointService::POINT_MODE_REGISTRATION_REWARDS_PARENT        => "分享会员注册奖励上级",
            PointService::POINT_MODE_REGISTRATION_AWARD                 => "会员注册奖励",
            PointService::POINT_MODE_OPEN_GROUP_DEDUCTION               => "拼团开团扣除",
            PointService::POINT_MODE_STAR_SPELL                         => "星拼乐奖励",
            PointService::POINT_MODE_STAR_LOST_SPELL                    => "星拼乐参团抵扣",
            PointService::POINT_MODE_CPS                                => (defined('CPS_PLUGIN_NAME') ? CPS_PLUGIN_NAME : '聚合CPS') ."奖励",
            PointService::POINT_MODE_LOCK_DRAW_REWARD                   => "抽奖奖励",
            PointService::TEAM_POINTS_REWARD                            => "经销商积分奖励",
            PointService::POINT_MODE_CIRCLE_ADD_REWARD                  => "加入圈子奖励",
            PointService::POINT_MODE_BLIND_BOX_LOST                     => "盲盒提示抵扣",
            PointService::POINT_MODE_CONSUMER_REWARD                    => "消费奖励",
            PointService::POINT_MODE_LINK_SERVICE_REWARD                => "积分对接奖励",
            PointService::POINT_MODE_DEPOSIT_LADDER_REWARD              => "定金阶梯团定金奖励",
            PointService::POINT_MODE_STORE_RESERVE                      => "门店预约商品",
            PointService::POINT_MODE__ZHUZHER_CREDIT_REWARD             => "酒店积分对接",
            PointService::POINT_MODE_ZHP_LOST                           => "珍惠拼",
            PointService::POINT_MODE_TEAM_DIVIDEND                      => "经销商提成",
            PointService::POINT_MODE_FIGHT_GROUP_LOTTERY_COMFORT_REWARD => "拼团抽奖安慰余额",
            PointService::POINT_MODE_LOVE_REDPACK                       => "拼团抽奖安慰余额",
            PointService::CPS_SUB_PLATFORM                              => "芸CPS奖励",
            PointService::POINT_MODE_NEW_MEDIA_LIKE                     => '新媒体-点赞奖励',
            PointService::POINT_MODE_NEW_MEDIA_ATTENTION                => '新媒体-关注奖励',
            PointService::POINT_MODE_NEW_MEDIA_READ                     => '新媒体-阅读奖励',
            PointService::POINT_MODE_NEW_MEDIA_FORWARD                  => '新媒体-转发奖励',
            PointService::POINT_MODE_NEW_MEDIA_FAVORITES                => '新媒体-收藏奖励',
            PointService::POINT_MODE_NEW_MEDIA_COMMENT                  => '新媒体-评论奖励',
            PointService::POINT_MODE_NEW_MEDIA_REWARD                   => '新媒体-打赏奖励',
            PointService::POINT_MODE_NEW_MEDIA_SUPERIOR                 => '新媒体-上级奖励',
            PointService::POINT_MODE_NEW_MEDIA_EXCHANGE                 => '新媒体-兑换流量值',
            PointService::GROUP_WORK_AWARD                              => '拼团等级未中奖励',
            PointService::GROUP_WORK_HEAD_AWARD                         => '拼团等级限制团长奖励',
            PointService::GROUP_WORK_PARENT_AWARD                       => '拼团等级限制未中上级奖励',
            PointService::POINT_MODE_VIDEO_WATCH_REWARD                 => '视频奖励-观看视频',
            PointService::POINT_MODE_VIDEO_TEAM_REWARD                  => '视频奖励-团队上级奖励',
            PointService::POINT_MODE_FLYERS_ADVERTISE                   => 'APP广告-广告奖励',
            PointService::POINT_MODE_GOODS_REFUND                       => '商品赠送退回',
            PointService::POINT_MODE_ORDER_REFUND                       => '订单赠送退回',
            PointService::POINT_MODE_POINT_MIDDLE_SYNC                  => '积分中台-积分同步',
            PointService::POINT_MODE_LOVE_TRANSFER                      => $this->loveTransferName(),
            PointService::POINT_MODE_QQ_ADVERTISE_POINT                 => '优量汇-奖励扣除',
            PointService::POINT_MODE_BALANCE_RECHARGE_REWARD            => '余额充值奖励',
            PointService::POINT_MODE_ORDER_SHOPKEEPER_REWARD            => '门店消费奖励店长',
            PointService::POINT_MODE_HAND_SIGN_PROTOCOL                 => '手签协议奖励',
            PointService::POINT_MODE_GROUP_CHAT_ACTIVITY_REWARD         => '群拓客活动奖励',
            PointService::INTEGRAL_POINT                                => $this->integralTransferName(),
            PointService::YS_SYSTEM_POINT_SYNC                          => '线下同步',
            PointService::POINT_MODE_VIDEO_WATCH_TAKE                   => '视频奖励-扣除积分',
            PointService::POINT_MODE_PARKING_PAY_COUPON                 => '停车缴费兑换优惠券',
            PointService::POINT_MODE_LOVE_WITHDRAW_FINAL_REDUCE         => $this->loveActualReceipt(),
            PointService::POINT_MODE_STORE_BALANCE_RECHARGE             => '门店余额充值奖励',
            PointService::POINT_MODE_LOVE_BUY_DEDUCTE_REDUCE            => $this->LoveBuyDeductName(),
            PointService::POINT_MODE_YWM_FIGHT_GROUPS_TEAM_SUCCESS      => '(新拼团)拼团活动团长奖励',
            PointService::POINT_MODE_LOVE_FROZE_ACTIVE                  => $this->LoveFrozeActiveName(),
            PointService::POINT_MODE_ROOM_RED_PACK_SEND                 => '直播发送拼手气红包扣除',
            PointService::POINT_MODE_ROOM_RED_PACK_RECEIVE              => '直播领取拼手气红包',
            PointService::POINT_MODE_ROOM_RED_PACK_REFUND               => '直播拼手气红包返还',
            PointService::POINT_MODE_SUBSCRIPTION                       => "认购-认购活动",
            PointService::POINT_MODE_NEWCOMER_FISSION_ACTIVE            => "新客裂变",
            PointService::POINT_MODE_TRANSFER_INTEGRAL                  => $this->pointTransferIntegralName(),
            PointService::POINT_MODE_BLB_CASHIER                        => "收银系统积分同步",
            PointService::FACE_TO_FACE_BUY                              => "面对面服务购买",
            PointService::FACE_TO_FACE_MEMBER_GIFT                      => "面对面服务购买赠送",
            PointService::FACE_TO_FACE_MERCHANT_GIFT                    => "面对面服务出售",
            PointService::POINT_MODE_FIRST_PARENT_REWARD                => "购物赠送上级(一级)",
            PointService::POINT_MODE_SECOND_PARENT_REWARD               => "购物赠送上级(二级)",
            PointService::POINT_MODE_FIRST_PARENT_REFUND                => "商品退款上级(一级)赠送回退",
            PointService::POINT_MODE_SECOND_PARENT_REFUND               => "商品退款上级(二级)赠送回退",
            PointService::POINT_MODE_POINT_EXCHANGE_LOVE               => "手动转入".(LOVE_NAME ? : '爱心值'),
            PointService::POINT_MODE_COUPON_STORE_REWARD                => (defined('COUPON_STORE_PLUGIN_NAME') ? COUPON_STORE_PLUGIN_NAME : '消费券联盟').'核销奖励',
            PointService::POINT_MODE_POOL_RESET                         => $this->poolResetName(),
            PointService::ACTIVITY_REWARD_INTEGRAL                      => '拓客活动奖励',
            PointService::POINT_MODE_AREA_DIVIDEND                      => '区域分红转入',
            PointService::POINT_EXCHANGE_OUT                            => PointService::POINT_EXCHANGE_OUT_ATTACHED,
            PointService::POINT_EXCHANGE_IN                             => PointService::POINT_EXCHANGE_IN_ATTACHED,
            PointService::POINT_MODE_LOVE_SPEED_POOL_CLEAR              => $this->poolClearName(),
            PointService::SIGN_BUY_ALLOWANCE                            => '签到购物津贴',
            PointService::POINT_MODE_MEMBER_MERGE                       => '会员合并转入',
            PointService::POINT_MODE_NEW_BLIND_BOX_EXCHANGE             => '新盲盒兑换',
            PointService::POINT_MODE_AREA_DIVIDEND_AWARD                => PointService::POINT_MODE_AREA_DIVIDEND_AWARD_ATTACHED,
            PointService::POINT_MODE_AREA_MERCHANT_AWARD                => PointService::POINT_MODE_AREA_MERCHANT_AWARD_ATTACHED,
            PointService::POINT_MODE_FACE_TO_FACE_AWARD                 => PointService::POINT_MODE_FACE_TO_FACE_AWARD_ATTACHED,
            PointService::STAFF_AUDIT_REWARD                            => PointService::STAFF_AUDIT_REWARD_ATTACHED,
            PointService::STATIC_POINT_DIVIDEND                         => (defined('STATIC_POINT_DIVIDEND_NAME') ? STATIC_POINT_DIVIDEND_NAME : '静态积分分红'),
            PointService::WISE_YUAN_TRADE_ACTIVITY_GIVE                 => PointService::WISE_YUAN_TRADE_ACTIVITY_GIVE_ATTACHED,
            PointService::WISE_YUAN_TRADE_REOPEN_GIVE                   => app('plugins')->isEnabled('wise-yuan-trade') ?
                    \Yunshop\WiseYuanTrade\common\WiseYuanTradeSet::instance()->customName('reopen_name').'转入' : PointService::WISE_YUAN_TRADE_REOPEN_GIVE_ATTACHED,
        ] + PointService::$otherSource;
    }

    private function transferLoveName()
    {
        return app('plugins')->isEnabled('love') ? '转入' . LOVE_NAME : '转入爱心值';
    }

    private function loveDeductionName()
    {
        return app('plugins')->isEnabled('love') ? LOVE_NAME . '提现扣除' : '爱心值提现扣除';
    }

    private function LoveBuyDeductName()
    {
        return app('plugins')->isEnabled('love') ? LOVE_NAME . '购物抵扣扣除' : '爱心值购物抵扣扣除';
    }

    private function loveTransferName()
    {
        return app('plugins')->isEnabled('love') ? LOVE_NAME . '转赠-转入' : '爱心值转赠-转入';
    }

    private function loveActualReceipt()
    {
        return app('plugins')->isEnabled('love') ? LOVE_NAME . '提现扣除(实际到账)' : '爱心值提现扣除(实际到账)';
    }

    private function signAwardName()
    {
        return app('plugins')->isEnabled('sign') ? trans('Yunshop\Sign::sign.plugin_name') . '奖励' : '签到奖励';
    }

    private function frozeAwardName()
    {
        return app('plugins')->isEnabled('sign') ? trans('Yunshop\Froze::froze.name') . '奖励' : '冻结币奖励';
    }

    private function integralTransferName()
    {
        $point_name = \Setting::get('shop.shop')['credit1'] ?: '积分';
        return app('plugins')->isEnabled('integral') ? INTEGRAL_NAME . "转化{$point_name}" : '消费积分转化积分';
    }

    private function pointTransferIntegralName()
    {
        $point_name = \Setting::get('shop.shop')['credit1'] ?: '积分';
        return app('plugins')->isEnabled('integral') ? $point_name . '转化' . INTEGRAL_NAME : "{$point_name}转化消费积分";
    }

    private function LoveFrozeActiveName()
    {
        return app('plugins')->isEnabled('love') ? '冻结'.LOVE_NAME.'激活' : '冻结爱心值激活';
    }

    private function poolResetName()
    {
        $point_name = \Setting::get('shop.shop')['credit1'] ?: '积分';
        return '清零设置-' . $point_name . '清零';
    }

    private function poolClearName()
    {
        $point_name = \Setting::get('shop.shop')['credit1'] ?: '积分';
        return "加速池扣除({$point_name}消耗)";
    }

    //todo----------------------------- 以下代码可以优化模型使用 --------------------------------

    public function hasOneMember()
    {
        return $this->hasOne(Member::class, 'uid', 'member_id');
    }

    public static function getPointLogList($search)
    {
        return PointLog::lists($search);
    }

    public function scopeLists($query, $search)
    {
        $query->search($search);
        $builder = $query->with([
            'hasOneMember',
        ]);
        return $builder;
    }
}
