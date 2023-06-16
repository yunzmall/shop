<?php
/**
 * Created by PhpStorm.
 * Author:
 * Date: 2017/5/7
 * Time: 下午3:00
 */

namespace app\common\services\credit;

use app\common\exceptions\ShopException;
use app\common\facades\Setting;

class ConstService
{
    const OPERATOR_SHOP = 0;  //操作者 商城

    const OPERATOR_ORDER = -1; //操作者 订单

    const OPERATOR_MEMBER = -2; //操作者 会员

    //类型：收入
    const TYPE_INCOME = 1;

    //类型：支出
    const TYPE_EXPENDITURE = 2;

    //充值状态 ：成功
    const STATUS_SUCCESS = 1;

    //充值状态 ：失败
    const STATUS_FAILURE = -1;

    const SOURCE_RECHARGE = 1; //充值

    const SOURCE_CONSUME = 2; //消费

    const SOURCE_TRANSFER = 3; //转让


    const SOURCE_DEDUCTION = 4; //抵扣

    const SOURCE_AWARD = 5; //奖励

    const SOURCE_WITHDRAWAL = 6; //提现

    const SOURCE_INCOME = 7; //提现至～～

    const SOURCE_CANCEL_DEDUCTION = 8; //抵扣取消回滚

    const SOURCE_CANCEL_AWARD = 9; //奖励取消回滚

    const SOURCE_CANCEL_CONSUME = 10; //消费取消回滚

    const SOURCE_RECHARGE_MINUS = 11; //后台扣除

    const SOURCE_RECHARGE_CODE = 92; //充值码充值

    const SOURCE_REJECTED = 12; //提现驳回

    const SOURCE_EXCEL_RECHARGE = 13; //EXCEL充值

    const SOURCE_CONVERT = 14; //余额转化爱心值

    const SOURCE_CONVERT_CANCEL = 15; //余额转化爱心值回滚

    const SOURCE_DRAW_CHARGE = 16; //抽奖获得

    const SOURCE_DRAW_REWARD = 17; //抽奖奖励

    const SOURCE_THIRD_SYN = 18;//第三方同步

    const ROOM_MEMBER_ACTIVITY = 19;//直播会员观看奖励

    const ROOM_ACTIVITY = 20;//直播间观看奖励

    const ROOM_ANCHOR_ACTIVITY = 21;//直播间观看奖励

    const ROOM_REWARD_TRANSFER = 22;//直播间观看奖励

    const ROOM_REWARD_RECIPIENT = 23;//直播间观看奖励

    const SOURCE_POINT_TRANSFER = 24;//积分转入

    const LAYER_CHAIN_RECHARGE = 25;//层链充值

    const SOURCE_NEW_MEMBER_PRIZE = 26;//新人奖奖励

    const FIGHT_GROUPS_SUCCESS_REWARD = 27;//开团团长奖励

    const STORE_SHAREHOLDER_UPGRATE_AWARD = 28;//门店股东升级奖励

    const CLOUD_WAREHOUSE_DIVIDEND = 29;//云仓转入

    const SNATCH_REGIMENT_SUCCESS_AWARD = 30;//抢团奖励

    const COMMUNITY_RELAY_AWARD = 31;//社群接龙奖励

    const FIGHT_GROUPS_LOTTERY_WIN = 32;//拼团抽奖成功奖励

    const FIGHT_GROUPS_LOTTERY_LOSER = 33;//拼团抽奖失败奖励

    const CPS_AWARD = 34;//聚合CPS奖励

    const STAR_SPELL_SUCCESS_AWARD = 35;//星拼乐奖励

    const LUCK_DRAW_AWARD = 36; //抽奖奖励

    const CIRCLE_ADD = 37; //加入圈子

    const ZHUZHER_CREDIT = 38; //酒店积分对接

    const FIGHT_GROUPS_LOTTERY_LOSER_PARENT = 39; //拼团抽奖失败上级奖励

    const DEPOSIT_LADDER = 40; //定金阶梯团

    const KART_GIVE_REWARD = 41; //车场门店打赏

    const FIGHT_GROUP_LOTTERY_COMFORT_REWARD_BALANCE = 42;  // 安慰奖奖励

    const CREDIT_ZNB_TRANSFER = 43;//信用值中南呗转入

    const PARENT_PAYMENT_REWARD = 44; //上级代付奖励

    const FREE_LOTTERY_DIVIDEND = 45;//商品免单抽奖

    const ZHP_LOTTERY = 46; //珍惠拼

    const AD_SERVING_REDPACK_REWARD = 47;//投放广告-广告获得红包奖励

    const AD_SERVING_PUT_IN_ADVERTISING_DEDUCT = 48;//投放广告-投放广告扣除余额

    const AD_SERVING_REFUND = 49;//投放广告-退款

    const OWNER_ORDER_SETTLE = 50; //采购成本结算（店主订单导入结算）

    const OWNER_ORDER_WITHHOLD = 51; //采购成本扣除（店主订单导入扣除）


    const FIGHT_GROUP_STATISTICS_AWARD = 55; //拼团统计奖励

    const FIGHT_GROUP_STATISTICS_AMOUNT_AWARD = 56; //拼团统计金额奖励


    const REDPACK_TOOL_AWARD_PARENT = 52; // 红包奖励-上级奖励
    const REDPACK_USER_SEND = 53; // 个人红包发放
    const REDPACK_USER_RECEIVE = 54; // 个人红包领取
    const REDPACK_USER_INVALID = 57; // 个人红包退回
    const CPS_SUB_PLATFORM = 58; // 芸CPS奖励
    const ZHP_QUIT_GROUP_REFUND = 59; //珍惠拼退团
    const GROUP_WORK_AWARD = 60; // 拼团等级未中奖励
    const GROUP_WORK_HEAD_AWARD = 61; // 拼团等级限制团长奖励
    const GROUP_WORK_PARENT_AWARD = 62; // 拼团等级限制未中上级奖励

    const PLATFORM_PURCHASE = 63; //云仓平台采购
    const FIGHT_GROUPS_OPERATORS_SETTLE_REWARD = 64;//拼团成团奖-结算奖励
    const LOVE_TO_BALANCE = 67; //爱心值转余额,插件point-to-balance

    const VIDEO_SHARE_POINTS = 68; //短视频积分奖励-个人
    const VIDEO_SHARE_POINTS_TEAM = 69; //短视频积分奖励-团队

    const ZHP_BARTER = 70; //珍惠拼-易货

    const CPS_CANCEL = 73; //cps订单退款返还积分

    const GROUP_CHAT_ACTIVITY = 74;// 群拓客奖励

    const CUSTOMER_INCREASE_REWARD = 76;//企业微信好友裂变活动奖励

    const RED_PACKET_REWARD = 77;//每日红包-红包奖励

    const SOURCE_INCOME_WITHDRAW_AWARD = 78;//收入提现奖励

    const STORE_RESERVE_SERVICE_AWARD = 79;//服务费奖励

    const SOURCE_OWNER_ORDER_BONUS_SETTLE = 80;//收入提现奖励

    const YS_SYSTEM_BALANCE_SYNC = 81;//益生同步余额

    const STORE_ATTENDANCE = 101;    //门店打卡提现

    const ZHP_UNIFY_REWARD = 102;//珍惠拼-统一奖励

    const YWM_FIGHT_GROUPS_SUCCESS_REWARD = 103;//(新拼团)拼团活动团长奖励

    const ROOM_REDPACK_SEND = 104;//直播拼手气红包发放

    const ROOM_REDPACK_RECEIVE = 105;//直播拼手气红包领取

    const ROOM_REDPACK_REFUND = 106;//直播拼手气红包退还

    const NEWCOMER_FISSION_REWARD = 107;//新客裂变

    const HAND_SIGN_PROTOCOL = 108;//手签协议奖励

    const ACTIVITY_RANKING_QUOTA_DISSATISFY = 109;//活动排行榜-额度扣除

    const ACTIVITY_REWARD = 110;//拓客活动奖励

    const MEMBER_MERGE = 111;//会员合并转入


    const SIGN_BUY_SIGN_PROFIT = 115;//签到认购签到收益

    const SIGN_BUY_RECOMMEND_AWARD = 116;//签到认购推荐奖

    const STOCK_SERVICE_BACK = 117;//存货回购

    const TASK_PACKAGE_RECOVERY = 118;//任务包复活

    const LINK_MOVE_AWARD = 119; //链动2+1 奖励

    const BE_WITHIN_CALL_PACKAGE = 120; //随叫随到服务 企业套餐：点击浏览扣费

    const ALIPAY_PERIOD_DEDUCT_SETTLE = 121; //支付宝周期扣款押金结算

    const LOVE_LOCK_RECHARGE_GIVE = 122;

    const FIGHT_GROUPS_LOTTERY_LOSER_PARENT_AGENT = 123; //拼团抽奖失败上级经销商奖励

    const WISE_YUAN_TRADE_QUALITY_GIVE = 124;//元商慧-质积分活动到期释放

    const EQUITY_REWARD = 125;//权益奖励

    const SHAREHOLDER_DIVIDEND = 126;//股东分红

    const PARTNER_REWARD = 127;//股东奖励

    const TEAM_DIVIDEND = 128;//经销商管理
    const COMMISSION = 129;//分销商提成
    const AREA_DIVIDEND = 130;//区域代理提成

    protected static $title = '余额';

    private static $otherSource = [];

    public function __construct($title = '')
    {
        $shop = Setting::get('shop.shop');

        static::$title = $shop['credit'] ?: static::$title;
        static::$title = $title ?: static::$title;
    }

    public static function addSource($key, $value)
    {
        $source = self::sourceComment();
        if (in_array($key, array_keys($source)) || in_array($key, array_keys(self::$otherSource))) {
            throw new ShopException('余额常量重复【' . $key . '--' . $value . '】');
        }
        self::$otherSource[$key] = $value;
    }

    public function sourceComment()
    {
        $result = [
                self::SOURCE_RECHARGE                            => static::$title . '充值',
                self::SOURCE_CONSUME                             => static::$title . '消费',
                self::SOURCE_TRANSFER                            => static::$title . '转让',
                self::SOURCE_DEDUCTION                           => static::$title . '抵扣',
                self::SOURCE_AWARD                               => static::$title . '奖励',
                self::SOURCE_WITHDRAWAL                          => static::$title . '提现',
                self::SOURCE_INCOME                              => '提现至' . static::$title,
                self::SOURCE_CANCEL_DEDUCTION                    => '抵扣取消',
                self::SOURCE_CANCEL_AWARD                        => '奖励取消',
                self::SOURCE_CANCEL_CONSUME                      => '消费取消',
                self::SOURCE_RECHARGE_MINUS                      => '后台扣除',
                self::SOURCE_RECHARGE_CODE                       => '充值码充值',
                self::SOURCE_EXCEL_RECHARGE                      => 'EXCEL充值',
                self::SOURCE_REJECTED                            => static::$title . '提现驳回',
                self::SOURCE_CONVERT                             => static::$title . '转化' . (defined(
                        'LOVE_NAME'
                    ) ? LOVE_NAME : '爱心值'),
                self::SOURCE_CONVERT_CANCEL                      => static::$title . '转化' . (defined(
                        'LOVE_NAME'
                    ) ? LOVE_NAME : '爱心值') . '失败回滚',
                self::SOURCE_DRAW_CHARGE                         => '抽奖获得',
                self::SOURCE_DRAW_REWARD                         => '抽奖奖励',
                self::SOURCE_NEW_MEMBER_PRIZE                    => '新人奖奖励',
                self::SOURCE_THIRD_SYN                           => '第三方同步',
                self::ROOM_MEMBER_ACTIVITY                       => '直播会员观看奖励',
                self::ROOM_ACTIVITY                              => '直播间会员奖励',
                self::ROOM_ANCHOR_ACTIVITY                       => '直播主播奖励',
                self::ROOM_REWARD_TRANSFER                       => '直播打赏支出',
                self::ROOM_REWARD_RECIPIENT                      => '直播打赏收入',
                self::SOURCE_POINT_TRANSFER                      => '积分自动转入',
                self::LAYER_CHAIN_RECHARGE                       => '层链余额充值',
                self::FIGHT_GROUPS_SUCCESS_REWARD                => '开团团长奖励',
                self::STORE_SHAREHOLDER_UPGRATE_AWARD            => '门店股东升级奖励',
                self::SNATCH_REGIMENT_SUCCESS_AWARD              => '抢团奖励',
                self::CLOUD_WAREHOUSE_DIVIDEND                   => '云仓转入',
                self::COMMUNITY_RELAY_AWARD                      => '社群接龙奖励',
                self::FIGHT_GROUPS_LOTTERY_WIN                   => '拼团抽奖成功奖励',
                self::FIGHT_GROUPS_LOTTERY_LOSER                 => '拼团抽奖失败奖励',
                self::STAR_SPELL_SUCCESS_AWARD                   => '星拼乐奖励',
                self::CPS_AWARD                                  => (defined(
                        'CPS_PLUGIN_NAME'
                    ) ? CPS_PLUGIN_NAME : '聚合CPS') . '奖励',
                self::LUCK_DRAW_AWARD                            => "抽奖奖励",
                self::CIRCLE_ADD                                 => "加入付费圈子",
                self::DEPOSIT_LADDER                             => "定金阶梯团定金奖励",
                self::ZHUZHER_CREDIT                             => '酒店积分对接',
                self::KART_GIVE_REWARD                           => "门店打赏",
                self::FIGHT_GROUPS_LOTTERY_LOSER_PARENT          => "未抽中会员上级奖励",
                self::PARENT_PAYMENT_REWARD                      => '上级代付奖励',
                self::FIGHT_GROUP_LOTTERY_COMFORT_REWARD_BALANCE => "安慰奖奖励",
                self::CREDIT_ZNB_TRANSFER                        => "中南呗转入",
                self::ZHP_LOTTERY                                => "珍惠拼",
                self::FREE_LOTTERY_DIVIDEND                      => "免单抽奖",
                self::AD_SERVING_REDPACK_REWARD                  => '观看广告获得红包奖励',
                self::AD_SERVING_PUT_IN_ADVERTISING_DEDUCT       => '投放广告费',
                self::AD_SERVING_REFUND                          => '投放广告费退回',
                self::OWNER_ORDER_SETTLE                         => "采购成本结算",
                self::OWNER_ORDER_WITHHOLD                       => "采购成本扣除",
                self::FIGHT_GROUP_STATISTICS_AWARD               => "开团成功累计次数团长奖励",
                self::FIGHT_GROUP_STATISTICS_AMOUNT_AWARD        => "开团成功累计金额团长奖励",
                self::REDPACK_TOOL_AWARD_PARENT                  => "红包奖励-上级奖励",
                self::REDPACK_USER_SEND                          => "个人红包发放",
                self::REDPACK_USER_RECEIVE                       => "个人红包领取",
                self::REDPACK_USER_INVALID                       => "个人红包退回",
                self::CPS_SUB_PLATFORM                           => "芸CPS奖励",
                self::PLATFORM_PURCHASE                          => "云仓平台采购",
                self::ZHP_QUIT_GROUP_REFUND                      => "珍惠拼退团",
                self::GROUP_WORK_AWARD                           => "0.1元拼-未拼中奖励",
                self::GROUP_WORK_HEAD_AWARD                      => "0.1元拼-团长奖励",
                self::GROUP_WORK_PARENT_AWARD                    => "0.1元拼-未拼中上级奖励",
                self::FIGHT_GROUPS_OPERATORS_SETTLE_REWARD       => "拼团活动团长上级奖励",
                self::LOVE_TO_BALANCE                            => (defined(
                        'LOVE_NAME'
                    ) ? LOVE_NAME : '爱心值') . "转" . static::$title,
                self::VIDEO_SHARE_POINTS                         => "视频奖励--观看视频",
                self::VIDEO_SHARE_POINTS_TEAM                    => "视频奖励--团队上级奖励",
                self::ZHP_BARTER                                 => "珍惠拼-易货",
                self::GROUP_CHAT_ACTIVITY                        => "群拓客活动奖励",
                self::CUSTOMER_INCREASE_REWARD                   => "企业微信好友裂变活动奖励",
                self::RED_PACKET_REWARD                          => "每日红包转入",
                self::SOURCE_INCOME_WITHDRAW_AWARD               => "收入提现奖励",
                self::STORE_RESERVE_SERVICE_AWARD                => (\Setting::get(
                        'plugins.service-fee.service.name'
                    ) ?: '服务费') . "奖励",
                self::SOURCE_OWNER_ORDER_BONUS_SETTLE            => "区域代理奖金",
                self::YS_SYSTEM_BALANCE_SYNC                     => "线下同步",
                self::STORE_ATTENDANCE                           => "门店打卡提现",
                self::ZHP_UNIFY_REWARD                           => '珍惠拼-拼中统一时间奖励',
                self::YWM_FIGHT_GROUPS_SUCCESS_REWARD            => '(新拼团)拼团活动团长奖励',
                self::ROOM_REDPACK_SEND                          => "直播拼手气红包发放",
                self::ROOM_REDPACK_RECEIVE                       => '直播拼手气红包领取',
                self::ROOM_REDPACK_REFUND                        => '直播拼手气红包退还',
                self::NEWCOMER_FISSION_REWARD                    => '新客裂变奖励',
                self::HAND_SIGN_PROTOCOL                         => '手签协议奖励',
                self::ACTIVITY_RANKING_QUOTA_DISSATISFY          => '个人额度不足扣除余额',
                self::ACTIVITY_REWARD                            => '拓客活动奖励',
                self::SIGN_BUY_SIGN_PROFIT                       => '签到收益',
                self::SIGN_BUY_RECOMMEND_AWARD                   => '签到推荐奖',
                self::MEMBER_MERGE                               => '会员合并转入',
                self::STOCK_SERVICE_BACK                         => '存货服务-平台回购',
                self::TASK_PACKAGE_RECOVERY                      => '任务包复活',
                self::LINK_MOVE_AWARD                            => \Setting::get('plugin.link_move')['plugin_name'] ?: "链动2+1",
                self::BE_WITHIN_CALL_PACKAGE                     => '随叫随到点击收费',
                self::ALIPAY_PERIOD_DEDUCT_SETTLE                => "支付宝周期扣款押金结算",
                self::FIGHT_GROUPS_LOTTERY_LOSER_PARENT_AGENT    => "未抽中会员上级经销商奖励",
                self::WISE_YUAN_TRADE_QUALITY_GIVE               => app('plugins')->isEnabled('wise-yuan-trade') ?
                    \Yunshop\WiseYuanTrade\common\WiseYuanTradeSet::instance()->customName('quality_name').'活动到期释放' : '质积分活动到期释放',
                self::EQUITY_REWARD                              => '权益奖励',
                self::SHAREHOLDER_DIVIDEND                       => '股东分红',
                self::PARTNER_REWARD                             => '股东奖励',
                self::TEAM_DIVIDEND                              => '经销商管理',
                self::COMMISSION                                 => '分销商提成',
                self::AREA_DIVIDEND                              => '区域分红',
            ] + self::$otherSource;

        if (app('plugins')->isEnabled('love-lock')) {
            $result[self::LOVE_LOCK_RECHARGE_GIVE] = LOVE_NAME . '赠送' . static::$title;
        }

        return $result;

    }

    public function typeComment()
    {
        return [
            self::TYPE_INCOME      => '收入',
            self::TYPE_EXPENDITURE => '支出'
        ];
    }

    public function operatorComment()
    {
        return [
            self::OPERATOR_SHOP   => '商城操作',
            self::OPERATOR_ORDER  => '订单操作',
            self::OPERATOR_MEMBER => '会员操作',
            // self::OPERATOR_ORDER  => '会员操作',
            // self::OPERATOR_MEMBER => '订单操作'
        ];
    }
}
