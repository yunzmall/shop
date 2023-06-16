<?php

namespace app\common\providers;



use app\backend\modules\charts\listeners\OrderStatistics;
use app\backend\modules\charts\modules\member\listeners\MemberLowerListener;
use app\backend\modules\charts\modules\phone\listeners\PhoneAttribution;
use app\backend\modules\goods\listeners\LimitBuy;
use app\backend\modules\sysMsg\services\SystemMsgDestroyService;
use app\common\events\member\MemberChangeRelationEvent;
use app\common\events\member\MemberCreateRelationEvent;
use app\common\events\member\MemberNewOfflineEvent;
use app\common\events\order\AfterOrderCreatedEvent;
use app\common\events\order\AfterOrderCreatedImmediatelyEvent;
use app\common\events\PayLog;
use app\common\events\UserActionEvent;
use app\common\events\WechatProcessor;
use app\common\listeners\charts\OrderBonusListeners;
use app\common\listeners\CollectHostListener;
use app\common\listeners\income\WithdrawPayedListener;
use app\common\listeners\member\MemberChangeRelationEventListener;
use app\common\listeners\member\MemberCreateRelationEventListener;
use app\common\listeners\member\MemberNewOfflineEventListener;
use app\common\listeners\MemberCartListener;
use app\common\listeners\order\LocationListener;
use app\common\listeners\PayLogListener;
use app\common\listeners\PluginCollectListener;
use app\common\listeners\point\PointListener;
use app\common\listeners\point\TimeParentReward;
use app\common\listeners\UpdateCache;
use app\common\listeners\UserActionListener;
use app\common\listeners\WechatProcessorListener;
use app\common\listeners\withdraw\WechatWithdrawV3Listener;
use app\common\listeners\withdraw\WithdrawAuditListener;
use app\common\listeners\withdraw\WithdrawPayListener;
use app\common\listeners\withdraw\WithdrawSuccessListener;
use app\common\modules\coupon\events\AfterMemberReceivedCoupon;
use app\common\modules\coupon\listeners\AfterMemberReceivedCouponListener;
use app\common\modules\payType\events\AfterOrderPayTypeChangedEvent;
use app\common\modules\payType\remittance\listeners\AfterOrderPayTypeChangedListener;
use app\common\modules\process\events\AfterProcessStateChangedEvent;
use app\common\modules\process\events\AfterProcessStatusChangedEvent;
use app\common\modules\process\StateContainer;
use app\common\modules\status\StatusContainer;
use app\frontend\modules\coupon\listeners\CouponExpired;
use app\frontend\modules\coupon\listeners\CouponExpireNotice;
use app\frontend\modules\coupon\listeners\CouponSend;
use app\frontend\modules\coupon\listeners\CouponSysMessage;
use app\frontend\modules\coupon\listeners\MonthCouponSend;
use app\frontend\modules\coupon\listeners\OrderCouponSend;
use app\frontend\modules\coupon\listeners\ShoppingShareCouponListener;
use app\frontend\modules\finance\listeners\BalanceRechargeCompletedListener;
use app\frontend\modules\finance\listeners\IncomeWithdraw;
use app\frontend\modules\goods\listeners\GoodsStock;
use app\frontend\modules\member\listeners\MemberLevelValidity;
use app\frontend\modules\order\listeners\orderListener;
use app\frontend\modules\withdraw\listeners\WithdrawApplyListener;
use app\frontend\modules\withdraw\listeners\WithdrawBalanceApplyListener;
use app\platform\modules\user\listeners\DisableUserAccount;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use app\common\events\WechatMessage;
use app\common\listeners\WechatMessageListener;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        \app\common\events\dispatch\OrderDispatchWasCalculated::class => [
            //订单邮费计算
            \app\frontend\modules\dispatch\listeners\prices\UnifyOrderDispatchPrice::class, //统一运费
            \app\frontend\modules\dispatch\listeners\prices\TemplateOrderDispatchPrice::class, //模板运费

        ],

        PayLog::class => [ //支付日志请求
            PayLogListener::class //保存支付参数
        ],
        \app\common\events\member\BecomeAgent::class => [ //会员成为下线
            \app\common\listeners\member\BecomeAgentListener::class
        ],
        AfterOrderCreatedEvent::class => [ //下单成功后调用会员成为下线事件
            \app\common\listeners\member\AfterOrderCreatedListener::class,
            \app\common\listeners\order\OrderCreateCertified::class, //关联实名认证表订单id
        ],

        AfterOrderCreatedImmediatelyEvent::class => [
            \app\frontend\modules\member\listeners\Order::class, //清空购物车

        ],
        /*AfterOrderReceivedEvent::class => [ //确认收货
            \app\common\listeners\member\AfterOrderReceivedListener::class
        ],*/
//        AfterOrderPaidEvent::class => [ //支付完成
//            \app\common\listeners\member\AfterOrderPaidListener::class,
//        ],
        //微信接口回调触发事件进程
        WechatProcessor::class => [
            WechatProcessorListener::class,//示例监听类
        ],

        WechatMessage::class => [
            WechatMessageListener::class,//示例监听类
            \app\common\listeners\WechatMinPayNotifyListener::class, //微信小程序支付管理事件通知
        ],

        AfterProcessStatusChangedEvent::class => [
            StatusContainer::class,
        ],
        AfterProcessStateChangedEvent::class => [
            StateContainer::class,
        ],
        AfterOrderPayTypeChangedEvent::class=>[
            AfterOrderPayTypeChangedListener::class
        ],
        MemberCreateRelationEvent::class=>[
            MemberCreateRelationEventListener::class
        ],
        AfterMemberReceivedCoupon::class=>[
            AfterMemberReceivedCouponListener::class
        ],
        UserActionEvent::class => [
            UserActionListener::class,
        ],
        MemberChangeRelationEvent::class=>[
            MemberChangeRelationEventListener::class
        ],
        \app\common\events\ProfitEvent::class => [
            \app\common\listeners\ProfitEventListener::class
        ],
        MemberNewOfflineEvent::class => [
            MemberNewOfflineEventListener::class
        ],

    ];
    /**
     * 注册监听者类
     * @var array
     */
    protected $subscribe = [

        BalanceRechargeCompletedListener::class,
        /**
         * 收入提现监听者类
         */
        WithdrawApplyListener::class,
        WithdrawAuditListener::class,
        WithdrawPayListener::class,
        WithdrawSuccessListener::class,
        /**
         * 收入提现奖励余额监听者
         */
        WithdrawPayedListener::class,

        /**
         * 余额提现监听者类
         */
        WithdrawBalanceApplyListener::class,

        \app\common\listeners\MessageListener::class,
        MemberCartListener::class,
        //会员等级升级
        \app\common\listeners\member\level\LevelListener::class,
        \app\common\listeners\balance\BalanceListener::class,

        //订单支付后，获取分享优惠卷资格
        ShoppingShareCouponListener::class,

        //订单赠送优惠卷监听
        \app\frontend\modules\coupon\listeners\CouponDiscount::class,


        //订单抵扣返还
        PointListener::class,
        \app\frontend\modules\finance\listeners\OrderDeductionRollback::class,
        \app\common\listeners\point\PointChangeCreatingListener::class, // 监听会员等级赠送积分是否超限

        //商品预扣库存
        GoodsStock::class,
		

        orderListener::class,
        IncomeWithdraw::class,
        CouponExpireNotice::class,
        CouponSend::class,
        CouponSysMessage::class,
        CouponExpired::class,
        MemberLevelValidity::class,
        LimitBuy::class,
        OrderStatistics::class,
        PhoneAttribution::class,
        UpdateCache::class, //每月初定时更新缓存
        OrderBonusListeners::class,
        MemberLowerListener::class,
        DisableUserAccount::class,
//        PluginCollectListener::class,
        CollectHostListener::class,
        WechatWithdrawV3Listener::class,
        MonthCouponSend::class,//购买商品按月发放优惠券
        OrderCouponSend::class,//购买商品订单完成发放优惠券
        //商品定时上下架
        \app\backend\modules\goods\listeners\GoodsServiceListener::class,

        //定时任务、队列情况记录
        \app\backend\modules\survey\listeners\HeartbeatStatusLogListener::class,


        //余额短信提醒定时任务
        \app\common\listeners\SmsBalanceListener::class,

        // 订单关闭后返还优惠券
        \app\backend\modules\coupon\listeners\OrderClosedListener::class,

        //商品下架、减库存发系统消息通知
        \app\common\listeners\goods\GoodsChangeListener::class,

        //余额充值赠送积分
        \app\common\listeners\balance\PointsRewardListener::class,

        //商品默认好评
        \app\backend\modules\goods\listeners\CommentServiceListener::class,

        //每月发放上级购物赠送积分
        TimeParentReward::class,

        //定时删除系统消息
        SystemMsgDestroyService::class,

    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        if (strpos(request()->path(), 'install')) {
            return;
        }

        parent::boot();

        //
    }
}
