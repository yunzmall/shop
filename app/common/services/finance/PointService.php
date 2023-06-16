<?php
/**
 * Created by PhpStorm.
 * Author:
 * Date: 2017/4/10
 * Time: 下午5:49
 */

namespace app\common\services\finance;


use app\backend\modules\member\models\Member;
use app\common\events\finance\PointChangeCreatingEvent;
use app\common\events\member\MemberPointChangeEvent;
use app\common\events\MessageEvent;
use app\common\exceptions\ShopException;
use app\common\models\finance\PointLog;
use app\common\models\notice\MessageTemp;
use app\common\services\notice\official\PointChangeNotice;
use app\common\services\notice\official\PointDeficiencyNotice;
use Setting;

class PointService
{
    const POINT_INCOME_GET = 1; //获得

    const POINT_INCOME_LOSE = -1; //失去

    const POINT_MODE_GOODS = 1; //商品赠送
    const POINT_MODE_GOODS_ATTACHED = '商品赠送';

    const POINT_MODE_ORDER = 2; //订单赠送
    const POINT_MODE_ORDER_ATTACHED = '订单赠送';

    const POINT_MODE_POSTER = 3; //超级海报
    const POINT_MODE_POSTER_ATTACHED = '超级海报';

    const POINT_MODE_ARTICLE = 4; //文章营销
    const POINT_MODE_ARTICLE_ATTACHED = '文章营销';

    const POINT_MODE_ADMIN = 5; //后台充值
    const POINT_MODE_ADMIN_ATTACHED = '后台充值';

    const POINT_MODE_BY = 6; //购物抵扣
    const POINT_MODE_BY_ATTACHED = '购物抵扣';

    const POINT_MODE_TEAM = 7; //团队奖励
    const POINT_MODE_TEAM_ATTACHED = '团队奖励';

    const POINT_MODE_LIVE = 8; //生活缴费奖励
    const POINT_MODE_LIVE_ATTACHED = '生活缴费奖励';

    const POINT_MODE_CASHIER = 9; //收银台奖励
    const POINT_MODE_CASHIER_ATTACHED = '收银台奖励';

    const POINT_MODE_AIR = 10; //飞机票
    const POINT_MODE_AIR_ATTACHED = '飞机票奖励';

    const POINT_MODE_RECHARGE = 11; //话费充值奖励
    const POINT_MODE_RECHARGE_ATTACHED = '话费充值奖励';

    const POINT_MODE_FLOW = 12; //流量充值奖励
    const POINT_MODE_FlOW_ATTACHED = '流量充值奖励';

    const POINT_MODE_TRANSFER = 13; //转让
    const POINT_MODE_TRANSFER_ATTACHED = '转让-转出';

    const POINT_MODE_RECIPIENT = 14; //转让
    const POINT_MODE_RECIPIENT_ATTACHED = '转让-转入';

    const POINT_MODE_ROLLBACK = 15; //回滚
    const POINT_MODE_ROLLBACK_ATTACHED = '返还';

    const POINT_MODE_COUPON_DEDUCTION_AWARD = 16;
    const POINT_MODE_COUPON_DEDUCTION_AWARD_ATTACHED = '优惠券抵扣奖励';

    const POINT_MODE_TASK_REWARD = 17;
    const POINT_MODE_TASK_REWARD_ATTACHED = '任务奖励';

    const POINT_MODE_TRANSFER_LOVE = 18;
    const POINT_MODE_TRANSFER_LOVE_ATTACHED = '自动转出';

    const POINT_MODE_SIGN_REWARD = 19;
    const POINT_MODE_SIGN_REWARD_ATTACHED = '签到奖励';

    const POINT_MODE_COURIER_REWARD = 20;
    const POINT_MODE_COURIER_REWARD_ATTACHED = '快递单奖励';

    const POINT_MODE_FROZE_AWARD = 21;
    const POINT_MODE_FROZE_AWARD_ATTACHED = '冻结币奖励';

    const POINT_MODE_COMMUNITY_REWARD = 22;
    const POINT_MODE_COMMUNITY_REWARD_ATTACHED = '圈子签到奖励';

    const POINT_MODE_CREATE_ACTIVITY = 23;
    const POINT_MODE_CREATE_ACTIVITY_ATTACHED = '创建活动';

    const POINT_MODE_ACTIVITY_OVERDUE = 24;
    const POINT_MODE_ACTIVITY_OVERDUE_ATTACHED = '活动失效';

    const POINT_MODE_RECEIVE_ACTIVITY = 25;
    const POINT_MODE_RECEIVE_ACTIVITY_ATTACHED = '领取活动';

    const POINT_MODE_RECEIVE_OVERDUE = 26;
    const POINT_MODE_RECEIVE_OVERDUE_ATTACHED = '领取失效';

    const POINT_MODE_COMMISSION_TRANSFER = 27;
    const POINT_MODE_COMMISSION_TRANSFER_ATTACHED = '分销佣金转入';

    const POINT_MODE_HOTEL_CASHIER = 28; //酒店收银台奖励
    const POINT_MODE_HOTEL_CASHIER_ATTACHED = '酒店收银台奖励';

    const POINT_MODE_EXCEL_RECHARGE = 29;
    const POINT_MODE_EXCEL_RECHARGE_ATTACHED = 'EXCEL充值';

    const POINT_MODE_CARD_VISIT_REWARD = 30;
    const POINT_MODE_CARD_VISIT_REWARD_ATTACHED = '名片访问奖励';

    const POINT_MODE_CARD_REGISTER_REWARD = 31;
    const POINT_MODE_CARD_REGISTER_REWARD_ATTACHED = '名片新增会员奖励';

    const POINT_MODE_PRESENTATION = 32;
    const POINT_MODE_PRESENTATION_ATTACHED = '推荐客户奖励';

    const POINT_MODE_LOVE_WITHDRAWAL_DEDUCTION = 33;
    const POINT_MODE_LOVE_WITHDRAWAL_DEDUCTION_ATTACHED = '爱心值提现扣除';

    const POINT_MODE_FIGHT_GROUPS_TEAM_SUCCESS = 34;
    const POINT_MODE_FIGHT_GROUPS_TEAM_SUCCESS_ATTACHED = '拼团活动团长奖励';

    const POINT_MODE_DRAW_CHARGE_GET = 35;
    const POINT_MODE_DRAW_CHARGE_GRT_ATTACHED = '抽奖获得';

    const POINT_MODE_DRAW_CHARGE_DEDUCTION = 36;
    const POINT_MODE_DRAW_CHARGE_DEDUCTION_ATTACHED = '抽奖使用扣除';

    const POINT_MODE_DRAW_REWARD_GET = 37;
    const POINT_MODE_DRAW_REWARD_GRT_ATTACHED = '抽奖奖励';

    const POINT_MODE_CONVERT = 38;
    const POINT_MODE_CONVERT_ATTACHED = '兑换';

    const POINT_MODE_THIRD = 39;
    const POINT_MODE_THIRD_ATTACHED = '第三方变动';

    const POINT_MODE_CONSUMPTION_POINTS = 40;
    const POINT_MODE_CONSUMPTION_POINTS_ATTACHED = '消费充值奖励';

    const POINT_MODE_ROOM_MEMBER_ACTIVITY_POINTS = 41;
    const POINT_MODE_ROOM_MEMBER_ACTIVITY_POINTS_ATTACHED = '直播会员观看奖励';

    const POINT_MODE_ROOM_ACTIVITY_POINTS = 42;
    const POINT_MODE_ROOM_ACTIVITY_POINTS_ATTACHED = '直播间会员奖励';

    const POINT_MODE_ROOM_ANCHOR_ACTIVITY_POINTS = 43;
    const POINT_MODE_ROOM_ANCHOR_ACTIVITY_POINTS_ATTACHED = '直播主播奖励';

    const POINT_MODE_ROOM_REWARD_TRANSFER_POINTS = 44;
    const POINT_MODE_ROOM_REWARD_TRANSFER_POINTS_ATTACHED = '直播打赏-支出';

    const POINT_MODE_ROOM_REWARD_RECIPIENT_POINTS = 45;
    const POINT_MODE_ROOM_REWARD_RECIPIENT_POINTS_ATTACHED = '直播打赏-收入';
    const POINT_AUCTION_REWARD_RECIPIENT_POINTS = 46;
    const POINT_AUCTION_REWARD_RECIPIENT_POINTS_ATTACHED = '拍卖奖励';

    const POINT_INCOME_WITHDRAW_AWARD = 47;
    const POINT_INCOME_WITHDRAW_AWARD_ATTACHED = '收入提现奖励';

    const POINT_MODE_TRANSFER_BALANCE = 48;
    const POINT_MODE_TRANSFER_BALANCE_ATTACHED = "自动转入余额";

    const POINT_MODE_BIND_MOBILE = 49;
    const POINT_MODE_BIND_MOBILE_ATTACHED = "绑定手机号奖励";

    const POINT_MODE_LAYER_CHAIN = 50;
    const POINT_MODE_LAYER_CHAIN_ATTACHED = "关系链等级奖励";

    const POINT_MODE_LAYER_CHAIN_RECHARGE = 51;
    const POINT_MODE_LAYER_CHAIN_RECHARGE_ATTACHED = "层链充值";

    const POINT_MODE_DRAW_NEW_MEMBER_PRIZE = 52;
    const POINT_MODE_DRAW_NEW_MEMBER_PRIZE_ATTACHED = '新人奖奖励';

    const POINT_MODE_LAYER_CHAIN_QUESTIONNAIRE = 53;
    const POINT_MODE_LAYER_CHAIN_QUESTIONNAIRE_ATTACHED = "问卷奖励";

    const POINT_MODE_HEALTH_ASSESSMENT = 54;
    const POINT_MODE_HEALTH_ASSESSMENT_ATTACHED = "健康测评奖励";

    const POINT_INCOME_WITHDRAW_AWARD_SCALE = 55;
    const POINT_INCOME_WITHDRAW_AWARD_ATTACHED_SCALE = "收入提现奖励比例";

    const POINT_MODE_MICRO_COMMUNITIES = 56;
    const POINT_MODE_MICRO_COMMUNITIES_REWARD = "微社区发帖奖励";

    const POINT_MODE_CONFERENCE = 57;
    const POINT_MODE_CONFERENCE_REWARD = "会务活动签到奖励";

    const POINT_MODE_STORE_SHAREHOLDER = 58;
    const POINT_MODE_STORE_SHAREHOLDER_ATTACHED = "门店股东升级奖励";

    const POINT_MODE_ANSWER_REWARD = 59;
    const POINT_MODE_ANSWER_REWARD_ATTACHED = "短视频答题奖励";

    const POINT_MODE_ANSWER_REWARD_PARENT = 60;
    const POINT_MODE_ANSWER_REWARD_PARENT_ATTACHED = "短视频答题上级奖励";

    const POINT_MODE_POINT_EXCHANGE = 61;
    const POINT_MODE_POINT_EXCHANGE_ATTACHED = "蓝牛积分兑换";

    const POINT_MODE_SNATCH_REGIMENT = 62;
    const POINT_MODE_SNATCH_REGIMENT_ATTACHED = "抢团奖励";

    const POINT_MODE_FIGHT_GROUPS_LOTTERY_WIN = 63;
    const POINT_MODE_FIGHT_GROUPS_LOTTERY_WIN_ATTACHED = "拼团抽奖成功奖励";

    const POINT_MODE_FIGHT_GROUPS_LOTTERY_LOSER = 64;
    const POINT_MODE_FIGHT_GROUPS_LOTTERY_LOSER_ATTACHED = "拼团抽奖失败奖励";

    const POINT_MODE_COMMUNITY_RELAY = 65;
    const POINT_MODE_COMMUNITY_RELAY_ATTACHED = "社群接龙奖励";

    const POINT_MODE_REGISTRATION_REWARDS_PARENT = 66;
    const POINT_MODE_REGISTRATION_REWARDS_PARENT_ATTACHED = "分享会员注册奖励上级";

    const POINT_MODE_REGISTRATION_AWARD = 67;
    const POINT_MODE_REGISTRATION_AWARD_ATTACHED = "会员注册奖励";

    const POINT_MODE_OPEN_GROUP_DEDUCTION = 68;
    const POINT_MODE_OPEN_GROUP_DEDUCTION_ATTACHED = "拼团开团扣除";

    const POINT_MODE_EXCHANGE_REDPACK_CHALLENGE = 69;
    const POINT_MODE_EXCHANGE_REDPACK_CHALLENGE_ATTACHED = "兑换口令红包挑战次数";

    const POINT_MODE_CPS = 70;
    const POINT_MODE_CPS_ATTACHED = '聚合CPS奖励';

    const POINT_MODE_STAR_SPELL = 71;
    const POINT_MODE_STAR_SPELL_ATTACHED = "星拼乐奖励";

    const POINT_MODE_STAR_LOST_SPELL = 72;
    const POINT_MODE_STAR_SPELL_LOST_ATTACHED = "星拼乐参团抵扣";

    const TEAM_POINTS_REWARD = 73;
    const TEAM_POINTS_REWARD_ATTACHED = "经销商积分奖励";

    const POINT_MODE_LOCK_DRAW_REWARD = 74;
    const POINT_MODE_LOCK_DRAW_ATTACHED = "抽奖奖励";

    const POINT_MODE_BLIND_BOX_LOST = 75;
    const POINT_MODE_BLIND_BOX_LOST_ATTACHED = "盲盒提示抵扣";

    const POINT_MODE_CIRCLE_ADD_REWARD = 76;
    const POINT_MODE_CIRCLE_ADD_ATTACHED = "加入圈子奖励";

    const POINT_MODE_LINK_SERVICE_REWARD = 77;
    const POINT_MODE_LINK_SERVICE_ATTACHED = "积分对接奖励";

    const POINT_MODE_CONSUMER_REWARD = 78;
    const POINT_MODE_CONSUMER_REWARD_ATTACHED = "消费奖励";

    const POINT_MODE_STORE_RESERVE = 79;
    const POINT_MODE_STORE_RESERVE_ATTACHED = "门店预约商品";

    const POINT_MODE__ZHUZHER_CREDIT_REWARD = 80;
    const POINT_MODE_ZHUZHER_CREDIT_LOST_ATTACHED = "酒店积分对接";

    const POINT_MODE_DEPOSIT_LADDER_REWARD = 81;
    const POINT_MODE_DEPOSIT_LADDER_ATTACHED = "定金阶梯团定金奖励";

    const POINT_MODE_FIGHT_GROUP_LOTTERY_COMFORT_REWARD = 82;
    const POINT_MODE_FIGHT_GROUP_LOTTERY_COMFORT_ATTACHED = "安慰奖奖励";

    const POINT_MODE_LOVE_REDPACK = 83;
    const POINT_MODE_LOVE_REDPACK_ATTACHED = "爱心值转入";

    const POINT_MODE_ZHP_LOST = 84;
    const POINT_MODE_ZHP_LOST_ATTACHED = "珍惠拼";

    const POINT_MODE_TEAM_DIVIDEND = 85;
    const POINT_MODE_TEAM_DIVIDEND_ATTACHED = "经销商提成";

    const CPS_SUB_PLATFORM = 86;
    const CPS_SUB_PLATFORM_ATTACHED = "芸CPS奖励";

    const CPS_CANCAL = 87;
    const CPS_CANCAL_ATTACHED = "CPS退款扣除积分";

    const POINT_MODE_COUPON_STORE_REWARD = 88;
    const POINT_MODE_COUPON_STORE_REWARD_ATTACHED = '消费券联盟核销奖励';

    const POINT_MODE_NEW_MEDIA_LIKE = 90;
    const POINT_MODE_NEW_MEDIA_LIKE_ATTACHED = '新媒体-点赞奖励';

    const POINT_MODE_NEW_MEDIA_ATTENTION = 91;
    const POINT_MODE_NEW_MEDIA_ATTENTION_ATTACHED = '新媒体-关注奖励';

    const POINT_MODE_RECHARGE_CODE = 92;
    const POINT_MODE_RECHARGE_CODE_ATTACHED = '充值码充值';

    const POINT_MODE_STORE = 93; //收银台奖励
    const POINT_MODE_STORE_ATTACHED = '门店奖励';

    const POINT_MODE_HOTEL = 94; //酒店奖励
    const POINT_MODE_HOTEL_ATTACHED = '酒店奖励';

    const POINT_MODE_NEW_MEDIA_COMMENT = 95;
    const POINT_MODE_NEW_MEDIA_COMMENT_ATTACHED = '新媒体-评论奖励';

    const POINT_MODE_NEW_MEDIA_REWARD = 96;
    const POINT_MODE_NEW_MEDIA_REWARD_ATTACHED = '新媒体-打赏奖励';

    const POINT_MODE_NEW_MEDIA_SUPERIOR = 97;
    const POINT_MODE_NEW_MEDIA_SUPERIOR_ATTACHED = '新媒体-上级奖励';

    const POINT_MODE_NEW_MEDIA_EXCHANGE = 98;
    const POINT_MODE_NEW_MEDIA_EXCHANGE_ATTACHED = '新媒体-兑换流量值';

    const POINT_MODE_NEW_MEDIA_READ = 99;
    const POINT_MODE_NEW_MEDIA_READ_ATTACHED = '新媒体-阅读奖励';

    const POINT_MODE_NEW_MEDIA_FORWARD = 100;
    const POINT_MODE_NEW_MEDIA_FORWARD_ATTACHED = '新媒体-转发奖励';

    const POINT_MODE_NEW_MEDIA_FAVORITES = 101;
    const POINT_MODE_NEW_MEDIA_FAVORITES_ATTACHED = '新媒体-收藏奖励';

    const GROUP_WORK_AWARD = 102;
    const GROUP_WORK_AWARD_ATTACHED = '0.1元拼-未拼中奖励';

    const GROUP_WORK_HEAD_AWARD = 103;
    const GROUP_WORK_HEAD_AWARD_ATTACHED = '0.1元拼-团长奖励';

    const GROUP_WORK_PARENT_AWARD = 104;
    const GROUP_WORK_PARENT_AWARD_ATTACHED = '0.1元拼-未拼中上级奖励';

    const POINT_MODE_VIDEO_WATCH_REWARD = 105;
    const POINT_MODE_VIDEO_WATCH_REWARD_ATTACHED = '视频奖励-观看视频';

    const POINT_MODE_VIDEO_TEAM_REWARD = 106;
    const POINT_MODE_VIDEO_TEAM_REWARD_ATTACHED = '视频奖励-团队上级奖励';

    const POINT_MODE_FLYERS_ADVERTISE = 107;
    const POINT_MODE_FLYERS_ADVERTISE_ATTACHED = 'APP广告-广告奖励';

    const POINT_MODE_POINT_MIDDLE_SYNC = 108;
    const POINT_MODE_POINT_MIDDLE_SYNC_ATTACHED = '积分中台-积分同步';

    const POINT_MODE_GOODS_REFUND = 109; //商品赠送退回
    const POINT_MODE_GOODS_REFUND_ATTACHED = '商品赠送退回';

    const POINT_MODE_ORDER_REFUND = 110; //订单赠送退回
    const POINT_MODE_ORDER_REFUND_ATTACHED = '订单赠送退回';

    const POINT_MODE_LOVE_TRANSFER = 111; //订单赠送退回
    const POINT_MODE_LOVE_TRANSFER_ATTACHED = '转赠-转入';//爱心值转赠

    const POINT_MODE_QQ_ADVERTISE_POINT = 112;
    const POINT_MODE_QQ_ADVERTISE_POINT_ATTACHED = '优量汇-奖励扣除';

    const POINT_MODE_BALANCE_RECHARGE_REWARD = 113; //余额充值奖励
    const POINT_MODE_BALANCE_RECHARGE_REWARD_ATTACHED = '余额充值奖励';

    const POINT_MODE_ORDER_SHOPKEEPER_REWARD = 114; //门店收银台消费奖励店长积分
    const POINT_MODE_ORDER_SHOPKEEPER_REWARD_ATTACHED = '门店消费奖励店长';

    const POINT_MODE_HAND_SIGN_PROTOCOL = 115;
    const POINT_MODE_HAND_SIGN_PROTOCOL_ATTACHED = '手签协议奖励';
    const POINT_MODE_GROUP_CHAT_ACTIVITY_REWARD = 116;// 群拓客活动奖励
    const POINT_MODE_GROUP_CHAT_ACTIVITY_REWARD_ATTACHED = '群拓客活动奖励';

    const POINT_MODE_CUSTOMER_INCREASE_REWARD = 117; //企业微信好友裂变活动奖励
    const POINT_MODE_CUSTOMER_INCREASE_REWARD_ATTACHED = '企业微信好友裂变活动奖励';
    const INTEGRAL_POINT = 118;
    const INTEGRAL_POINT_ATTACHED = '消费积分转化积分';

    const YS_SYSTEM_POINT_SYNC = 119;
    const YS_SYSTEM_POINT_SYNC_NAME = '线下同步';

    const POINT_MODE_VIDEO_WATCH_TAKE = 120;
    const POINT_MODE_VIDEO_WATCH_TAKE_ATTACHED = '视频奖励-扣除积分';
    const POINT_MODE_PARKING_PAY_COUPON = 121;
    const POINT_MODE_PARKING_PAY_COUPON_ATTACHED = '停车缴费兑换优惠券';

    const POINT_MODE_LOVE_WITHDRAW_FINAL_REDUCE = 122;
    const POINT_MODE_LOVE_WITHDRAW_FINAL_REDUCE_ATTACHED = '爱心值提现扣除(实际到账)';

    const POINT_MODE_STORE_BALANCE_RECHARGE = 123;
    const POINT_MODE_STORE_BALANCE_RECHARGE_ATTACHED = '门店余额充值奖励';

    const POINT_MODE_YWM_FIGHT_GROUPS_TEAM_SUCCESS = 124;
    const POINT_MODE_YWM_FIGHT_GROUPS_TEAM_SUCCESS_ATTACHED = '(新拼团)拼团活动团长奖励';

    const POINT_MODE_LOVE_BUY_DEDUCTE_REDUCE = 125;
    const POINT_MODE_LOVE_BUY_DEDUCTE_REDUCE_ATTACHED = '爱心值购物抵扣扣除';

    const POINT_MODE_SUBSCRIPTION = 126;
    const POINT_MODE_SUBSCRIPTION_ATTACHED = '认购-认购活动';

    const POINT_MODE_ROOM_RED_PACK_RECEIVE = 127;
    const POINT_MODE_ROOM_RED_PACK_RECEIVE_ATTACHED = '直播领取拼手气红包';

    const POINT_MODE_ROOM_RED_PACK_REFUND = 128;
    const POINT_MODE_ROOM_RED_PACK_REFUND_ATTACHED = '直播拼手气红包返还';

    const POINT_MODE_ROOM_RED_PACK_SEND = 129;
    const POINT_MODE_ROOM_RED_PACK_SEND_ATTACHED = '直播发送拼手气红包扣除';

    const POINT_MODE_LOVE_FROZE_ACTIVE = 130;
    const POINT_MODE_LOVE_FROZE_ACTIVE_ATTACHED = '冻结爱心值激活';

    const POINT_MODE_NEWCOMER_FISSION_ACTIVE = 131;
    const POINT_MODE_NEWCOMER_FISSION_ACTIVE_ATTACHED = '新客裂变';

    const POINT_MODE_TRANSFER_INTEGRAL = 132;
    const POINT_MODE_TRANSFER_INTEGRAL_ATTACHED = '积分转化消费积分';

    const POINT_MODE_BLB_CASHIER = 133;
    const POINT_MODE_BLB_CASHIER_ATTACHED = '收银系统积分同步';

    const FACE_TO_FACE_BUY = 134;
    const FACE_TO_FACE_BUY_ATTACHED = '面对面服务购买';

    const FACE_TO_FACE_MEMBER_GIFT = 135;
    const FACE_TO_FACE_MEMBER_GIFT_ATTACHED = '面对面服务购买赠送';

    const FACE_TO_FACE_MERCHANT_GIFT = 136;
    const FACE_TO_FACE_MERCHANT_GIFT_ATTACHED = '面对面服务出售';

    const POINT_EXCHANGE_OUT = 139;
    const POINT_EXCHANGE_OUT_ATTACHED = '积分通转出';

    const POINT_EXCHANGE_IN = 140;
    const POINT_EXCHANGE_IN_ATTACHED = '积分通转入';

    const POINT_MODE_FIRST_PARENT_REWARD = 141;
    const POINT_MODE_FIRST_PARENT_REWARD_ATTACHED = '购物赠送上级(一级)';

    
    const POINT_MODE_SECOND_PARENT_REWARD = 142;
    const POINT_MODE_SECOND_PARENT_REWARD_ATTACHED = '购物赠送上级(二级)';

    const POINT_MODE_FIRST_PARENT_REFUND = 143;
    const POINT_MODE_FIRST_PARENT_REFUND_ATTACHED = '商品退款上级(一级)赠送回退';

    const POINT_MODE_SECOND_PARENT_REFUND = 144;
    const POINT_MODE_SECOND_PARENT_REFUND_ATTACHED = '商品退款上级(二级)赠送回退';

    const POINT_MODE_POINT_EXCHANGE_LOVE = 145;
    const POINT_MODE_POINT_EXCHANGE_LOVE_ATTACHED = '手动转入' . (LOVE_NAME ?: '爱心值');

    const POINT_MODE_POOL_RESET = 146;
    const POINT_MODE_POOL_RESET_ATTACHED = '清零设置-积分清零';

    const ACTIVITY_REWARD_INTEGRAL = 147;
    const ACTIVITY_REWARD_INTEGRAL_ATTACHED = '拓客活动积分奖励';

    const POINT_MODE_AREA_DIVIDEND = 148;
    const POINT_MODE_AREA_DIVIDEND_ATTACHED = '区域分红转入';

    const SIGN_BUY_ALLOWANCE = 156;
    const SIGN_BUY_ALLOWANCE_NAME = '签到购物津贴';

    const POINT_MODE_LOVE_SPEED_POOL_CLEAR = 149;
    const POINT_MODE_LOVE_SPEED_POOL_CLEAR_ATTACHED = '加速池扣除(积分消耗)';

    const POINT_MODE_MEMBER_MERGE = 150;
    const POINT_MODE_MEMBER_MERGE_ATTACHED = '会员合并转入';

    const POINT_MODE_NEW_BLIND_BOX_EXCHANGE = 151;
    const POINT_MODE_NEW_BLIND_BOX_EXCHANGE_ATTACHED = '新盲盒兑换';

    const POINT_MODE_AREA_DIVIDEND_AWARD = 153;
    const POINT_MODE_AREA_DIVIDEND_AWARD_ATTACHED = '区域分红奖励';

    const POINT_MODE_AREA_MERCHANT_AWARD = 154;
    const POINT_MODE_AREA_MERCHANT_AWARD_ATTACHED = '区代招商奖励';

    const POINT_MODE_FACE_TO_FACE_AWARD = 155;
    const POINT_MODE_FACE_TO_FACE_AWARD_ATTACHED = '面对面服务奖励';

    const STAFF_AUDIT_REWARD = 157;
    const STAFF_AUDIT_REWARD_ATTACHED = '员工审批奖励';

    const STATIC_POINT_DIVIDEND = 158;
    const STATIC_POINT_DIVIDEND_ATTACHED = STATIC_POINT_DIVIDEND_NAME ?: '静态积分分红';


    const WISE_YUAN_TRADE_ACTIVITY_GIVE = 159;
    const WISE_YUAN_TRADE_ACTIVITY_GIVE_ATTACHED = '共识活动赠送';


    const WISE_YUAN_TRADE_REOPEN_GIVE = 160;
    const WISE_YUAN_TRADE_REOPEN_GIVE_ATTACHED = '重开值转入';


    /**
     * todo 不需要而外添加已统一，只需要在 getAllSourceName 方法添加即可
     * 上面的常量写死在这里，所以导致控制器获取这些业务类型的时候也要把这边的常量拿过去，麻烦新增一个业务类型的常量在这里的时候往
     * app\backend\modules\finance\controllers\PointLogController这个控制器中也加入这些常量，否则后台前端会缺失业务类型
     **/


    const POINT = 0;

    public $point_data = array();

    public $member_point;

    protected $member;

    private $is_change = 1;

    public static $otherSource = [];

    /*
     * $data = [
     *      'point_income_type' //失去还是获得 POINT_INCOME_GET OR POINT_INCOME_LOSE
     *      'point_mode' // 1、2、3、4、5 收入方式
     *      'member_id' //会员id
     *      'point' //获得or支出多少积分
     *      //'before_point' //获取or支出 之前 x积分
     *      //'after_point' //获得or支出 之后 x积分
     *      'remark'   //备注
     * ]
     * */

    public function __construct(array $point_data)
    {
        if (!isset($point_data['point'])) {
            return;
        }
        event($event = new PointChangeCreatingEvent($point_data));
        $this->is_change = $event->is_change;
        $point_data = $event->changeData;

        $this->point_data = $point_data;
        $this->point_data['point'] = round($this->point_data['point'], 2);
        //$member = Member::getMemberById($point_data['member_id']);

        $this->member = $this->getMemberModel();
        $this->member_point = $this->member->credit1 ? $this->member->credit1 : 0;    //会员信息有可能找不到，默认给个0
    }

    public static function addSource($key, $value)
    {
        if (self::getAllSourceName()[$key]) {
            throw new ShopException('积分常量重复【' . $key . '--' . $value . '】');

        }
        self::$otherSource[$key] = $value;
    }


    private function getMemberModel()
    {
        $member_id = $this->point_data['member_id'];
        $memberModel = Member::uniacid()->where('uid', $member_id)->lockForUpdate()->first();

        return $memberModel;
    }

    /**
     * Update member credit1.
     *
     * @return PointLog|bool
     * @throws ShopException
     */

    public function changePoint($relation_id = '')
    {
        if ($relation_id) {
            $this->point_data['relation_id'] = $relation_id;
        }
        $point = floor($this->point_data['point'] * 100) / 100;
        if ($this->point_data['point_income_type'] == self::POINT_INCOME_LOSE) {
            $point = floor(abs($this->point_data['point']) * 100) / 100;
        }
        if ($point < 0.01) {
            return false;
        }
        if (!$this->is_change) {
            \Log::debug('会员等级积分限制不修改', $this->point_data);
            return true;
        }
        $this->getAfterPoint();
        Member::updateMemberInfoById(['credit1' => $this->member_point], $this->point_data['member_id']);
        return $this->addLog();
    }

    /**
     * 该方法处理扣除积分大于剩余积分按积分归零算。不抛出异常需要积分明细记录
     */
    public function deductPoint()
    {
        $point = floor(abs($this->point_data['point']) * 100) / 100;
        if (bccomp($point, 0.01, 2) === -1) {
            return false;
        }
        $this->point_data['before_point'] = $this->member_point;
        $this->member_point = max(bcadd($this->member_point, $this->point_data['point'], 2), 0);
        $this->point_data['after_point'] = round($this->member_point, 2);
        Member::updateMemberInfoById(['credit1' => $this->member_point], $this->point_data['member_id']);
        return $this->addLog();
    }

    public function addLog()
    {
        //$this->point_data['uniacid'] = \YunShop::app()->uniacid;
        $uniacid = \YunShop::app()->uniacid;
        $this->point_data['thirdStatus'] = empty($this->point_data['thirdStatus']) ? 1 : $this->point_data['thirdStatus'];
        $this->point_data['uniacid'] = !empty($uniacid) ? $uniacid : $this->point_data['uniacid'];
        $point_model = PointLog::create($this->point_data);
        if (!isset($point_model)) {
            return false;
        }
        event(
            new MemberPointChangeEvent(
                $this->member,
                $this->point_data,
                $this->getModeAttribute($this->point_data['point_mode'])
            )
        );
        $this->messageNotice();
        $this->checkFloorNotice();
        return $point_model;
    }

    public function messageNotice()
    {
        if ($this->point_data['point'] == 0) {
            return;
        }
        $template_id = \Setting::get('shop.notice')['point_change'];
        $point_status = $this->getModeAttribute($this->point_data['point_mode']);
        $pointNotice = new PointChangeNotice($this->member, $this->point_data, $point_status);
        $pointNotice->sendMessage();
        return;
        if (!$template_id) {
            return;
        }
        $params = [
            ['name' => '商城名称', 'value' => \Setting::get('shop.shop')['name']],
            ['name' => '昵称', 'value' => $this->member['nickname']],
            ['name' => '时间', 'value' => date('Y-m-d H:i', time())],
            ['name' => '积分变动金额', 'value' => $this->point_data['point']],
            ['name' => '积分变动类型', 'value' => $this->getModeAttribute($this->point_data['point_mode'])],
            ['name' => '变动后积分数值', 'value' => $this->point_data['after_point']]
        ];
        $news_link = MessageTemp::find($template_id)->news_link;
        $news_link = $news_link ?: '';
        event(new MessageEvent($this->member->uid, $template_id, $params, $url = $news_link));
    }

    /**
     * 检测是否超过设置的下限并发送消息通知
     * @return bool
     */
    public function checkFloorNotice()
    {
        try {
            if ($this->point_data['point'] == 0) {
                return true;
            }


            $template_id = \Setting::get('shop.notice')['point_deficiency'];

            if (!$template_id) {
                return true;
            }

            $set = Setting::get('point.set');
            if (!$set['point_floor']) {
                return true;
            }

            if ($set['point_floor_on'] == 0 || empty($set['point_message_type']) == true || in_array(
                    $set['point_message_type'],
                    [1, 2, 3]
                ) != true) {
                return true;
            }


            //指定会员分组
            if ($set['point_message_type'] == 3) {
                if ($this->member->yzMember->group_id != $set['group_type']) {
                    return true;
                }
            }

            //指定会员等级
            if ($set['point_message_type'] == 2) {
                //这个会员属于当前的这个等级
                if ($this->member->yzMember->level_id != $set['level_limit']) {
                    return true;
                }
            }

            //指定会员
            if ($set['point_message_type'] == 1) {
                if (in_array($this->member->uid, explode(',', $set['uids'])) != true) {
                    return true;
                }
            }

            $pointNotice = new PointDeficiencyNotice($this->member, $this->point_data);
            $pointNotice->sendMessage();
            return;
            if ($this->point_data['after_point'] > $set['point_floor']) {
                $params = [
                    ['name' => '商城名称', 'value' => \Setting::get('shop.shop')['name']],
                    ['name' => '昵称', 'value' => $this->member['nickname']],
                    ['name' => '时间', 'value' => date('Y-m-d H:i', time())],
                    ['name' => '通知额度', 'value' => $set['point_floor']],
                    ['name' => '当前积分', 'value' => $this->point_data['after_point']],
                ];
                $news_link = MessageTemp::find($template_id)->news_link;
                $news_link = $news_link ?: '';
                event(new MessageEvent($this->member->uid, $template_id, $params, $url = $news_link));
            } else {
                return true;
            }
        } catch (\Exception $e) {
            \Log::debug('抛异常了');
            return true;
        }
    }

    /**
     * 获取变化之后的积分
     *
     * @throws ShopException
     */
    public function getAfterPoint()
    {
        $this->point_data['before_point'] = $this->member_point;
        $this->member_point += $this->point_data['point'];
        if ($this->member_point < PointService::POINT) {
            throw new ShopException('积分不足!!!');
            //$this->member_point = PointService::POINT;
        }
        $this->point_data['after_point'] = round($this->member_point, 2);
    }

    public function getModeAttribute($mode)
    {
        $allSource = self::getAllSourceName();

        $mode_attribute = $allSource[$mode];

        if (!$mode_attribute) {
            $mode_attribute = self::$otherSource[$mode];
        }


        return $mode_attribute?:'类型：'.$mode;
    }


    public static function getAllSourceName()
    {
        //业务类型
        $activity_mode = [
            1   => self::POINT_MODE_GOODS_ATTACHED,
            2   => self::POINT_MODE_ORDER_ATTACHED,
            3   => self::POINT_MODE_POSTER_ATTACHED,
            4   => self::POINT_MODE_ARTICLE_ATTACHED,
            5   => self::POINT_MODE_ADMIN_ATTACHED,
            6   => self::POINT_MODE_BY_ATTACHED,
            7   => self::POINT_MODE_TEAM_ATTACHED,
            8   => self::POINT_MODE_LIVE_ATTACHED,
            9   => self::POINT_MODE_CASHIER_ATTACHED,
            13  => self::POINT_MODE_TRANSFER_ATTACHED,
            14  => self::POINT_MODE_RECIPIENT_ATTACHED,
            15  => self::POINT_MODE_ROLLBACK_ATTACHED,
            16  => self::POINT_MODE_COUPON_DEDUCTION_AWARD_ATTACHED,
            17  => self::POINT_MODE_TASK_REWARD_ATTACHED,
            18  => (app('plugins')->isEnabled('love')) ? '转入' . \Yunshop\Love\Common\Services\SetService::getLoveName() : self::POINT_MODE_TRANSFER_LOVE_ATTACHED,
            19  => (app('plugins')->isEnabled('sign')) ? trans('Yunshop\Sign::sign.plugin_name') . '奖励' : self::POINT_MODE_SIGN_REWARD_ATTACHED,
            20  => self::POINT_MODE_COURIER_REWARD_ATTACHED,
            21  => (app('plugins')->isEnabled('froze')) ? \Yunshop\Froze\Common\Services\SetService::getFrozeName() . '奖励' : self::POINT_MODE_FROZE_AWARD_ATTACHED,
            23  => self::POINT_MODE_CREATE_ACTIVITY_ATTACHED,
            24  => self::POINT_MODE_ACTIVITY_OVERDUE_ATTACHED,
            25  => self::POINT_MODE_RECEIVE_ACTIVITY_ATTACHED,
            26  => self::POINT_MODE_RECEIVE_OVERDUE_ATTACHED,
            27  => self::POINT_MODE_COMMISSION_TRANSFER_ATTACHED,
            28  => self::POINT_MODE_HOTEL_CASHIER_ATTACHED,
            29  => self::POINT_MODE_EXCEL_RECHARGE_ATTACHED,
            92  => self::POINT_MODE_RECHARGE_CODE_ATTACHED,
            93  => self::POINT_MODE_STORE_ATTACHED,
            94  => self::POINT_MODE_HOTEL_ATTACHED,
            22  => self::POINT_MODE_COMMUNITY_REWARD_ATTACHED,
            30  => self::POINT_MODE_CARD_VISIT_REWARD_ATTACHED,
            31  => self::POINT_MODE_CARD_REGISTER_REWARD_ATTACHED,
            32  => self::POINT_MODE_PRESENTATION_ATTACHED,
            33  => (app('plugins')->isEnabled('love')) ? \Yunshop\Love\Common\Services\SetService::getLoveName() . '提现扣除' : self::POINT_MODE_LOVE_WITHDRAWAL_DEDUCTION_ATTACHED,
            34  => self::POINT_MODE_FIGHT_GROUPS_TEAM_SUCCESS_ATTACHED,
            35  => self::POINT_MODE_DRAW_CHARGE_GRT_ATTACHED,
            36  => self::POINT_MODE_DRAW_CHARGE_DEDUCTION_ATTACHED,
            37  => self::POINT_MODE_DRAW_REWARD_GRT_ATTACHED,
            38  => self::POINT_MODE_CONVERT_ATTACHED,
            40  => self::POINT_MODE_CONSUMPTION_POINTS_ATTACHED,
            41  => self::POINT_MODE_ROOM_MEMBER_ACTIVITY_POINTS_ATTACHED,
            42  => self::POINT_MODE_ROOM_ACTIVITY_POINTS_ATTACHED,
            43  => self::POINT_MODE_ROOM_ANCHOR_ACTIVITY_POINTS_ATTACHED,
            44  => self::POINT_MODE_ROOM_REWARD_TRANSFER_POINTS_ATTACHED,
            45  => self::POINT_MODE_ROOM_REWARD_RECIPIENT_POINTS_ATTACHED,
            46  => self::POINT_AUCTION_REWARD_RECIPIENT_POINTS_ATTACHED,
            47  => self::POINT_INCOME_WITHDRAW_AWARD_ATTACHED,
            48  => self::POINT_MODE_TRANSFER_BALANCE_ATTACHED,
            49  => self::POINT_MODE_BIND_MOBILE_ATTACHED,
            50  => self::POINT_MODE_LAYER_CHAIN_ATTACHED,
            51  => self::POINT_MODE_LAYER_CHAIN_RECHARGE_ATTACHED,
            52  => self::POINT_MODE_HEALTH_ASSESSMENT_ATTACHED,
            53  => self::POINT_MODE_LAYER_CHAIN_QUESTIONNAIRE_ATTACHED,
            54  => self::POINT_MODE_HEALTH_ASSESSMENT_ATTACHED,
            55  => self::POINT_INCOME_WITHDRAW_AWARD_ATTACHED_SCALE,
            56  => self::POINT_MODE_MICRO_COMMUNITIES_REWARD,
            57  => self::POINT_MODE_CONFERENCE_REWARD,
            58  => self::POINT_MODE_STORE_SHAREHOLDER_ATTACHED,
            59  => self::POINT_MODE_ANSWER_REWARD_ATTACHED,
            60  => self::POINT_MODE_ANSWER_REWARD_PARENT_ATTACHED,
            61  => self::POINT_MODE_POINT_EXCHANGE_ATTACHED,
            62  => self::POINT_MODE_SNATCH_REGIMENT_ATTACHED,
            63  => self::POINT_MODE_FIGHT_GROUPS_LOTTERY_WIN_ATTACHED,
            64  => self::POINT_MODE_FIGHT_GROUPS_LOTTERY_LOSER_ATTACHED,
            65  => self::POINT_MODE_COMMUNITY_RELAY_ATTACHED,
            66  => self::POINT_MODE_REGISTRATION_REWARDS_PARENT_ATTACHED,
            67  => self::POINT_MODE_REGISTRATION_AWARD_ATTACHED,
            68  => self::POINT_MODE_OPEN_GROUP_DEDUCTION_ATTACHED,
            69  => self::POINT_MODE_EXCHANGE_REDPACK_CHALLENGE_ATTACHED,
            71  => self::POINT_MODE_STAR_SPELL_ATTACHED,
            72  => self::POINT_MODE_STAR_SPELL_LOST_ATTACHED,
            73  => self::TEAM_POINTS_REWARD_ATTACHED,
            74  => self::POINT_MODE_LOCK_DRAW_ATTACHED,
            75  => self::POINT_MODE_BLIND_BOX_LOST_ATTACHED,
            76  => self::POINT_MODE_CIRCLE_ADD_ATTACHED,
            78  => self::POINT_MODE_CONSUMER_REWARD_ATTACHED,
            77  => self::POINT_MODE_LINK_SERVICE_ATTACHED,
            79  => self::POINT_MODE_STORE_RESERVE_ATTACHED,
            80  => self::POINT_MODE_ZHUZHER_CREDIT_LOST_ATTACHED,
            81  => self::POINT_MODE_DEPOSIT_LADDER_REWARD,
            82  => self::POINT_MODE_FIGHT_GROUP_LOTTERY_COMFORT_ATTACHED,
            83  => self::POINT_MODE_LOVE_REDPACK_ATTACHED,
            84  => self::POINT_MODE_ZHP_LOST_ATTACHED,
            85  => self::POINT_MODE_TEAM_DIVIDEND_ATTACHED,
            86  => self::CPS_SUB_PLATFORM_ATTACHED,
            88  => self::POINT_MODE_COUPON_STORE_REWARD_ATTACHED,
            90  => self::POINT_MODE_NEW_MEDIA_LIKE_ATTACHED,
            91  => self::POINT_MODE_NEW_MEDIA_ATTENTION_ATTACHED,
            95  => self::POINT_MODE_NEW_MEDIA_COMMENT_ATTACHED,
            96  => self::POINT_MODE_NEW_MEDIA_REWARD_ATTACHED,
            97  => self::POINT_MODE_NEW_MEDIA_SUPERIOR_ATTACHED,
            98  => self::POINT_MODE_NEW_MEDIA_EXCHANGE_ATTACHED,
            99  => self::POINT_MODE_NEW_MEDIA_READ_ATTACHED,
            100 => self::POINT_MODE_NEW_MEDIA_FORWARD_ATTACHED,
            101 => self::POINT_MODE_NEW_MEDIA_FAVORITES_ATTACHED,
            102 => self::GROUP_WORK_AWARD_ATTACHED,
            103 => self::GROUP_WORK_HEAD_AWARD_ATTACHED,
            104 => self::GROUP_WORK_PARENT_AWARD_ATTACHED,
            105 => self::POINT_MODE_VIDEO_WATCH_REWARD_ATTACHED,
            106 => self::POINT_MODE_VIDEO_TEAM_REWARD_ATTACHED,
            107 => self::POINT_MODE_FLYERS_ADVERTISE_ATTACHED,
            108 => self::POINT_MODE_POINT_MIDDLE_SYNC_ATTACHED,
            112 => self::POINT_MODE_QQ_ADVERTISE_POINT_ATTACHED,
            111 => self::POINT_MODE_LOVE_TRANSFER_ATTACHED,
            115 => self::POINT_MODE_GROUP_CHAT_ACTIVITY_REWARD_ATTACHED,
            117 => self::POINT_MODE_CUSTOMER_INCREASE_REWARD_ATTACHED,
            118 => self::INTEGRAL_POINT_ATTACHED,
            119 => self::YS_SYSTEM_POINT_SYNC_NAME,
            120 => self::POINT_MODE_VIDEO_WATCH_TAKE_ATTACHED,
            121 => self::POINT_MODE_PARKING_PAY_COUPON_ATTACHED,
            122 => self::POINT_MODE_LOVE_WITHDRAW_FINAL_REDUCE_ATTACHED,
            123 => self::POINT_MODE_STORE_BALANCE_RECHARGE_ATTACHED,
            124 => self::POINT_MODE_YWM_FIGHT_GROUPS_TEAM_SUCCESS_ATTACHED,
            125 => self::POINT_MODE_LOVE_BUY_DEDUCTE_REDUCE_ATTACHED,
            126 => self::POINT_MODE_SUBSCRIPTION_ATTACHED,
            127 => self::POINT_MODE_ROOM_RED_PACK_RECEIVE_ATTACHED,
            128 => self::POINT_MODE_ROOM_RED_PACK_REFUND_ATTACHED,
            129 => self::POINT_MODE_ROOM_RED_PACK_SEND_ATTACHED,
            130  => (app('plugins')->isEnabled('love')) ? '冻结'.\Yunshop\Love\Common\Services\SetService::getLoveName().'激活'  : self::POINT_MODE_LOVE_FROZE_ACTIVE_ATTACHED,
            131 => self::POINT_MODE_NEWCOMER_FISSION_ACTIVE_ATTACHED,
            132 => self::POINT_MODE_TRANSFER_INTEGRAL_ATTACHED,
            133 => self::POINT_MODE_BLB_CASHIER_ATTACHED,
            134 => self::FACE_TO_FACE_BUY_ATTACHED,
            135 => self::FACE_TO_FACE_MEMBER_GIFT_ATTACHED,
            136 => self::FACE_TO_FACE_MERCHANT_GIFT_ATTACHED,
            139 => self::POINT_EXCHANGE_OUT_ATTACHED,
            140 => self::POINT_EXCHANGE_IN_ATTACHED,
            141 => self::POINT_MODE_FIRST_PARENT_REWARD_ATTACHED,
            142 => self::POINT_MODE_SECOND_PARENT_REWARD_ATTACHED,
            143 => self::POINT_MODE_FIRST_PARENT_REFUND_ATTACHED,
            144 => self::POINT_MODE_SECOND_PARENT_REFUND_ATTACHED,
            145 => self::POINT_MODE_POINT_EXCHANGE_LOVE_ATTACHED,
            146 => self::POINT_MODE_POOL_RESET_ATTACHED,
            147 => self::ACTIVITY_REWARD_INTEGRAL_ATTACHED,
            148 => self::POINT_MODE_AREA_DIVIDEND_ATTACHED,
            149 => self::POINT_MODE_LOVE_SPEED_POOL_CLEAR_ATTACHED,
            153 => self::POINT_MODE_AREA_DIVIDEND_AWARD_ATTACHED,
            154 => self::POINT_MODE_AREA_MERCHANT_AWARD_ATTACHED,
            155 => self::POINT_MODE_FACE_TO_FACE_AWARD_ATTACHED,
            157 => self::STAFF_AUDIT_REWARD_ATTACHED,
            158 => self::STATIC_POINT_DIVIDEND_ATTACHED,
            159 => self::WISE_YUAN_TRADE_ACTIVITY_GIVE_ATTACHED,
            160 => app('plugins')->isEnabled('wise-yuan-trade') ?
                \Yunshop\WiseYuanTrade\common\WiseYuanTradeSet::instance()->customName('reopen_name').'转入' : self::WISE_YUAN_TRADE_REOPEN_GIVE_ATTACHED,
        ];

        foreach (static::$otherSource as $key => $value) {
            if (!isset($activity_mode[$key])) {
                $activity_mode[$key] = $value;
            }
        }

        return $activity_mode;
    }
}
