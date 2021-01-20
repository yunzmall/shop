<?php
/**
 * Created by PhpStorm.
 * Author: 芸众商城 www.yunzshop.com
 * Date: 2017/4/10
 * Time: 下午5:47
 */

namespace app\common\models\finance;


use app\common\models\BaseModel;
use app\common\models\Member;
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
            $query->whereBetween('created_at', [strtotime($search['time']['start']), strtotime($search['time']['end'])]);
        }
        $query->searchMember($search);
    }

    /**
     * @param static $query
     * @param array $search
     */
    public function scopeSearchMember($query, $search = [])
    {
        if ($search['member'] || $search['level_id'] || $search['group_id']) {
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
            PointService::POINT_MODE_GOODS                        => '商品赠送',
            PointService::POINT_MODE_ORDER                        => '订单赠送',
            PointService::POINT_MODE_POSTER                       => '超级海报',
            PointService::POINT_MODE_ARTICLE                      => '文章营销',
            PointService::POINT_MODE_ADMIN                        => '后台充值',
            PointService::POINT_MODE_BY                           => '购物抵扣',
            PointService::POINT_MODE_TEAM                         => '团队奖励',
            PointService::POINT_MODE_LIVE                         => '生活缴费奖励',
            PointService::POINT_MODE_AIR                          => '飞机票奖励',
            PointService::POINT_MODE_CASHIER                      => '收银台奖励',
            PointService::POINT_MODE_STORE                        => '门店奖励',
            PointService::POINT_MODE_HOTEL_CASHIER                => '酒店收银台奖励',
            PointService::POINT_MODE_HOTEL                        => '酒店奖励',
            PointService::POINT_MODE_RECHARGE                     => '话费充值奖励',
            PointService::POINT_MODE_FLOW                         => '流量充值奖励',
            PointService::POINT_MODE_TRANSFER                     => '转让-转出',
            PointService::POINT_MODE_RECIPIENT                    => '转让-转入',
            PointService::POINT_MODE_ROLLBACK                     => '返还',
            PointService::POINT_MODE_COUPON_DEDUCTION_AWARD       => '优惠券抵扣奖励',
            PointService::POINT_MODE_TRANSFER_LOVE                => $this->transferLoveName(),
            PointService::POINT_MODE_RECHARGE_CODE                => '充值码充值',
            PointService::POINT_MODE_TASK_REWARD                  => '任务奖励',
            PointService::POINT_MODE_SIGN_REWARD                  => $this->signAwardName(),
            PointService::POINT_MODE_COURIER_REWARD               => '快递单奖励',
            PointService::POINT_MODE_FROZE_AWARD                  => $this->frozeAwardName(),
            PointService::POINT_MODE_COMMUNITY_REWARD             => '圈子签到奖励',
            PointService::POINT_MODE_CREATE_ACTIVITY              => '创建活动',
            PointService::POINT_MODE_ACTIVITY_OVERDUE             => '活动失效',
            PointService::POINT_MODE_RECEIVE_ACTIVITY             => '领取活动',
            PointService::POINT_MODE_RECEIVE_OVERDUE              => '领取失效',
            PointService::POINT_MODE_COMMISSION_TRANSFER          => '分销佣金转入',
            PointService::POINT_MODE_EXCEL_RECHARGE               => 'EXCEL充值',
            PointService::POINT_MODE_CARD_VISIT_REWARD            => '名片访问奖励',
            PointService::POINT_MODE_CARD_REGISTER_REWARD         => '名片新增会员奖励',
            PointService::POINT_MODE_PRESENTATION                 => '推荐客户奖励',
            PointService::POINT_MODE_LOVE_WITHDRAWAL_DEDUCTION    => $this->loveDeductionName(),
            PointService::POINT_MODE_FIGHT_GROUPS_TEAM_SUCCESS    => '拼团活动团长奖励',
            PointService::POINT_MODE_DRAW_CHARGE_GET              => '抽奖获得',
            PointService::POINT_MODE_DRAW_CHARGE_DEDUCTION        => '抽奖使用扣除',
            PointService::POINT_MODE_DRAW_REWARD_GET              => '抽奖奖励',
            PointService::POINT_MODE_CONVERT                      => '兑换',
            PointService::POINT_MODE_THIRD                        => '第三方变动',
            PointService::POINT_MODE_CONSUMPTION_POINTS           => '消费充值奖励',
            PointService::POINT_MODE_ROOM_MEMBER_ACTIVITY_POINTS  => '直播会员观看奖励',
            PointService::POINT_MODE_ROOM_ACTIVITY_POINTS         => '直播间会员奖励',
            PointService::POINT_MODE_ROOM_ANCHOR_ACTIVITY_POINTS  => '直播主播奖励',
            PointService::POINT_MODE_ROOM_REWARD_TRANSFER_POINTS  => '直播打赏-支出',
            PointService::POINT_MODE_ROOM_REWARD_RECIPIENT_POINTS => '直播打赏-收入',
            PointService::POINT_AUCTION_REWARD_RECIPIENT_POINTS   => '拍卖奖励',
            PointService::POINT_INCOME_WITHDRAW_AWARD             => '收入提现奖励',
            PointService::POINT_MODE_TRANSFER_BALANCE             => "自动转入余额",
            PointService::POINT_MODE_BIND_MOBILE                  => "绑定手机号奖励",
            PointService::POINT_MODE_LAYER_CHAIN                  => "关系链等级奖励",
            PointService::POINT_MODE_LAYER_CHAIN_RECHARGE         => "层链充值",
            PointService::POINT_MODE_DRAW_NEW_MEMBER_PRIZE        => '新人奖奖励',
            PointService::POINT_MODE_LAYER_CHAIN_QUESTIONNAIRE    => "问卷奖励",
            PointService::POINT_MODE_HEALTH_ASSESSMENT            => "健康测评奖励",
            PointService::POINT_MODE_MICRO_COMMUNITIES            => "微社区发帖奖励",
            PointService::POINT_MODE_CONFERENCE                   => "会务活动签到奖励",
            PointService::POINT_INCOME_WITHDRAW_AWARD_SCALE       => "收入提现奖励比例",
            PointService::POINT_MODE_STORE_SHAREHOLDER            => "门店股东升级奖励",
            PointService::POINT_MODE_ANSWER_REWARD                => "短视频答题奖励",
            PointService::POINT_MODE_ANSWER_REWARD_PARENT         => "短视频答题上级奖励",
            PointService::POINT_MODE_POINT_EXCHANGE               => "蓝牛积分兑换",
            PointService::POINT_MODE_SNATCH_REGIMENT              => "抢团奖励",
            PointService::POINT_MODE_FIGHT_GROUPS_LOTTERY_WIN     => "拼团抽奖成功奖励",
            PointService::POINT_MODE_FIGHT_GROUPS_LOTTERY_LOSER   => "拼团抽奖失败奖励",
            PointService::POINT_MODE_COMMUNITY_RELAY              => "社群接龙奖励",
            PointService::POINT_MODE_REGISTRATION_REWARDS_PARENT  => "分享会员注册奖励上级",
            PointService::POINT_MODE_REGISTRATION_AWARD           => "会员注册奖励",
            PointService::POINT_MODE_OPEN_GROUP_DEDUCTION         => "拼团开团扣除",
        ];
    }

    private function transferLoveName()
    {
        return app('plugins')->isEnabled('love') ? '转入' . LOVE_NAME : '转入爱心值';
    }

    private function loveDeductionName()
    {
        return app('plugins')->isEnabled('love') ? LOVE_NAME . '提现扣除' : '爱心值提现扣除';
    }

    private function signAwardName()
    {
        return app('plugins')->isEnabled('sign') ? trans('Yunshop\Sign::sign.plugin_name') . '奖励' : '签到奖励';
    }

    private function frozeAwardName()
    {
        return app('plugins')->isEnabled('sign') ? trans('Yunshop\Froze::froze.name') . '奖励' : '冻结币奖励';
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
            'hasOneMember'
        ]);
        return $builder;
    }
}
