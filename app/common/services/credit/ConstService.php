<?php
/**
 * Created by PhpStorm.
 * Author: 芸众商城 www.yunzshop.com
 * Date: 2017/5/7
 * Time: 下午3:00
 */

namespace app\common\services\credit;

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
    
    protected static $title = '余额';


    public function __construct($title = '')
    {
        $shop = Setting::get('shop.shop');

        static::$title = $shop['credit'] ?: static::$title;
        static::$title = $title ?: static::$title;
    }


    public function sourceComment()
    {
        return [
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
            self::SOURCE_CONVERT                             => static::$title . '转化' . (defined('LOVE_NAME') ? LOVE_NAME : '爱心值'),
            self::SOURCE_CONVERT_CANCEL                      => static::$title . '转化' . (defined('LOVE_NAME') ? LOVE_NAME : '爱心值') . '失败回滚',
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
            self::CPS_AWARD                                  => '聚合CPS奖励',
            self::LUCK_DRAW_AWARD                            => "抽奖奖励",
            self::CIRCLE_ADD                                 => "加入付费圈子",
            self::DEPOSIT_LADDER                             => "定金阶梯团定金奖励",
            self::ZHUZHER_CREDIT                             => '酒店积分对接',
            self::KART_GIVE_REWARD                           => "门店打赏",
            self::FIGHT_GROUPS_LOTTERY_LOSER_PARENT          => "拼团抽奖失败上级奖励",
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
            self::PLATFORM_PURCHASE                           => "云仓平台采购",
            self::ZHP_QUIT_GROUP_REFUND                      => "珍惠拼退团",
            self::GROUP_WORK_AWARD                           => "0.1元拼-未拼中奖励",
            self::GROUP_WORK_HEAD_AWARD                      => "0.1元拼-团长奖励",
            self::GROUP_WORK_PARENT_AWARD                    => "0.1元拼-未拼中上级奖励",
        ];
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
