<?php
/**
 * Created by PhpStorm.
 * User: Merlin
 * Date: 2020/9/18
 * Time: 14:33
 */

namespace app\common\services\income;
use app\common\models\Income;
class IncomeService
{
    //分红类型
    const COMMISSION_ORDER                = 1;
    const TEAM_DIVIDEND                   = 2;
    const AGENT_DIVIDEND                  = 3;
    const APPOINTMENT_ORDER               = 4;
    const AREA_DIVIDEND                   = 5;
    const MEMBER_BONUS_RECORDS            = 6;
    const SELL_RECORDS                    = 7;
    const AUCTION_BID_REWARD              = 8;
    const AUCTION_PREPAYMENT              = 9;
    const CHANNEL_AWARD                   = 10;
    const CLOCK_REWARD_LOG                = 11;
    const COMMISSION_ACTIVITY_REWARD      = 12;
    const COMMISSION_MANAGE_LOG           = 13;
    const CONSUME_RETURN                  = 14;
    const DELIVERY_STATION_DIVIDEND       = 15;
    const DISTRIBUTOR_MANAGE              = 16;
    const DIY_QUEUE_LOG                   = 17;
    const TEAM_PERFORMANCE_STATISTICS_LOG = 18;
    const ENERGY_CABIN                    = 19;
    const FIXED_REWARD_LOG                = 20;
    const FROZE_WITH_DRAW                 = 21;
    const FULL_RETURN                     = 22;
    const GLOBAL_DIVIDEND                 = 23;
    const HOTEL_CASHIER_ORDER             = 24;
    const HOTEL_ORDER                     = 25;
    const NOMINATE_BONUS                  = 26;
    const INTEGRAL_WITHDRAW               = 27;
    const INTERESTS_DIVIDEND              = 28;
    const CONSUMPTION_RECORDS             = 29;
    const LEVEL_RETURN                    = 30;
    const LOVE_WITHDRAW_RECORDS           = 31;
    const LOVE_RETURN_LOG                 = 32;
    const LOVE_TEAM_AWARD                 = 33;
    const MANAGE_AWARD_RECORDS            = 34;
    const MANAGEMENT_DIVIDEND             = 35;
    const MANUAL_LOG                      = 36;
    const MEMBER_RETURN_LOG               = 37;
    const MERCHANT_BONUS_LOG              = 38;
    const MICRO_SHOP_BONUS_LOG            = 39;
    const MICRO_COMMUNITIES_STICK_REWARD  = 40;
    const MEMBER_REFERRAL_AWARD           = 41;
    const MEMBER_TEAM_AWARD               = 42;
    const ORDER_PARENTING_AWARD           = 43;
    const ORDER_TEAM_AWARD                = 44;
    const TIER_AWARD                      = 45;
    const NET_CAR_DIVIDEND                = 46;
    const TEAM_PRIZE                      = 47;
    const ORDINARY_DIVIDEND               = 48;
    const OZY_AWARD_RECORD                = 49;
    const PACKAGE_DELIVER_BONUS           = 50;
    const PARTNER_REWARD_LOG              = 51;
    const PENDING_ORDER_DIVIDEND          = 52;
    const PERFORMANCE_BONUS               = 53;
    const PERIOD_RETURN_LOG               = 54;
    const POINT_ACTIVITY_AWARD_LOG        = 55;
    const RED_PACKET_RECEIVE_LOGS         = 56;
    const REVENUE_AWARD_BONUS             = 57;
    const ROOM_BONUS_LOG                  = 58;
    const SALES_COMMISSION                = 59;
    const SCORING_DIVIDEND                = 60;
    const SCORING_REWARD                  = 61;
    const SERVICE_STATION_DIVIDEND        = 62;
    const SHARE_CHAIN_AWARD_LOG           = 63;
    const SHAREHOLDER_DIVIDEND            = 64;
    const RETURN_SINGLE_LOG               = 65;
    const STORE_CASHIER_ORDER             = 66;
    const STORE_CASHIER_STORE_ORDER       = 67;
    const STORE_CASHIER_BOSS_ORDER        = 68;
    const TEAM_MANAGE_BONUS               = 69;
    const TEAM_MANAGEMENT_LOG             = 70;
    const TEAM_RETURN_LOG                 = 71;
    const TEAM_REWARDS_ORDER              = 72;
    const TEAM_MEMBER_TASKS               = 73;
    const TEAM_SALES_BONUS                = 74;
    const LECTURER_REWARD_LOG             = 75;
    const VIDEO_SHARE_BONUS               = 76;
    const WEIGHTED_DIVIDEND               = 77;
    const AUCTION_INCOME                  = 78;
    const AUCTION_ENDORSEMENT             = 79;
    const AUCTION_AREA                    = 80;
    const TEAM_SZTT                       = 81;
    const TEAM_SIDEWAYS_WITHDRAW          = 82;
    const COLLAGE_BONUS                   = 83;
    const COLLAGE_AREA_DIVIDEND           = 84;
    const CONSUME_RED_PACKET              = 85;
    const SNATCH_REWARD                   = 86;
    const REGIONAL_REWARD                 = 87;
    const CLOUD_WAREHOUSE                 = 88;
    const STORE_SHAREHOLDER               = 89;
    const ASSEMBLE                        = 90;
    const ASSEMBLE_WAGES                  = 91;
    const PERIOD_RETURN                   = 92;
    const TEAM_FJYX                       = 93;
    const RECOMMENDER                     = 94;
    const SUPERIOR_REWARD                 = 95;
    const SELL_AWARD                      = 96;
    const EQUITY_REWARD                   = 97;
    const STORE_CARD_INCOME               = 98;
    const CONSUME_REWARD                  = 99;
    const AGENCY_REWARD                      = 100;
	const RANKING_AWARD                      = 102;
    const RESERVE_FUND                       = 101;
    const CIRCLE_VIDEO_BOUNS                 = 103;
    const CIRCLE_INVITATION_REWARD           = 104;
    const CIRCLE_ADD                         = 105;
    const SCHOOL_COMPANY                     = 106;
	const CONSUMER_REWARD                    = 109;
    const INVEST_PEOPLE                      = 107;
    const LIVE_INSTALL                       = 108;
    const ROOM_CODE                          = 109;
    const STORE_BUSINESS_ALLIANCE_RECOMMEND  = 110;
    const STORE_BUSINESS_ALLIANCE_BUSINESS   = 111;
    const STORE_BUSINESS_ALLIANCE_SERVICE    = 112;
    const STORE_BUSINESS_ALLIANCE_OPERATION  = 113;
    const STORE_BUSINESS_ALLIANCE_PRICE      = 114;
    const STORE_BUSINESS_ALLIANCE_STORE      = 115;
    const STORE_BUSINESS_ALLIANCE_SUPPORT    = 116;
    const STORE_BUSINESS_ALLIANCE_TASK       = 117;
    const STORE_BUSINESS_ALLIANCE_TEAM       = 118;

    const NEW_RETAIL_REWARD                  = 119;
    const NEW_RETAIL_RIGHT_REWARD            = 120;
    const PLUGIN_PARENT_PAYMENT_COMMISSION   = 121;
    const STORE_REWARDS                      = 122;
    const ZHP_REWARD                         = 123;
    const STORE_PROJECTS_ORDER               = 124;
    const COMMISSION_EXTRA_BONUS             = 125;
    const STORE_BALANCE_AWARD                = 126;


    /**
     * @param $data 会员id：member_id，分红类型：dividend_code，分红表id：dividend_table_id，金额：amount，订单号：order_sn，详情：detail
     * @return bool
     * @author: Merlin
     * @Time: 2020/10/9   11:27
     */
    public static function insertIncome(array $income_data)
    {
        if (is_array(current($income_data))) {
            $all_data = $income_data;
        } else {
            $all_data[] = $income_data;
        }
        foreach ($all_data as $data) {
            $income = Income::where('member_id',$data['member_id'])
                ->where('dividend_code',$data['dividend_code'])
                ->where('incometable_id',$data['dividend_table_id'])
                ->first();
            if (!empty($income)) {
                \Log::debug('该笔分红已转成收入',$data);
                continue;
            }
            $uniacid = isset($data['uniacid'])?$data['uniacid']:\YunShop::app()->uniacid;
            $type_name = isset($data['type_name'])?$data['type_name']:self::getDividendCodeName($data['dividend_code']);
            $all_income_data = [
                'uniacid' => $uniacid,
                'member_id' => $data['member_id'],
                'dividend_code' => $data['dividend_code'],
                'incometable_id' => $data['dividend_table_id'],
                'type_name' => $type_name,
                'incometable_type' => self::getDividendClass($data['dividend_code']),
                'amount' => $data['amount'],
                'status' => 0,
                'pay_status' => 0,
                'order_sn' => $data['order_sn'],
                'detail'   =>   $data['detail'],
                'create_month' => date('Ym', time()),
            ];
            $income_model = new Income();
            $income_model->fill($all_income_data);
            $result = $income_model->save();
            if (!$result) {
                \Log::debug('收入插入失败',$all_income_data);
            }
        }
        return true;
    }

    /**
     * 获取自定义名称//推广中心01模板，收入占比中收入类型名称跟随插件自定义名称变化
     * @param $code
     * @return string
     */
    public static function dividendCodeCustomPluginName($code)
    {
        switch ($code) {
            case self::COMMISSION_ORDER:
                $set = \Setting::get('shop.lang', ['lang' => 'zh_cn']);
                return $set[$set['lang']]['commission']['title'] ? $set[$set['lang']]['commission']['title'].'佣金' : self::getDividendCodeName($code);
            case self::TEAM_DIVIDEND:
                $set = \Setting::get('shop.lang', ['lang' => 'zh_cn']);
                return $set[$set['lang']]['team_dividend']['title'] ? $set[$set['lang']]['team_dividend']['title'].'分红' : self::getDividendCodeName($code);
            case self::AREA_DIVIDEND:
                $set = \Setting::get('shop.lang', ['lang' => 'zh_cn']);
                return $set[$set['lang']]['area_dividend']['title'] ? $set[$set['lang']]['area_dividend']['title'].'分红' : self::getDividendCodeName($code);
            case self::MERCHANT_BONUS_LOG:
                $set = \Setting::get('shop.lang', ['lang' => 'zh_cn']);
                return $set[$set['lang']]['merchant']['title'] ? $set[$set['lang']]['merchant']['title'].'分红' : self::getDividendCodeName($code);
            case self::CHANNEL_AWARD:
                if (app('plugins')->isEnabled('channel')) {
                    $set = \Setting::get('plugin.channel');
                    return $set['plugin_name'] ? : self::getDividendCodeName($code);
                }
                break;
            case self::CLOCK_REWARD_LOG:
                if (app('plugins')->isEnabled('clock-in')) {
                    $set = \Setting::get('plugin.clock_in');
                    return $set['plugin_name'] ? : self::getDividendCodeName($code);
                }
                break;
            case self::DIY_QUEUE_LOG:
                if (app('plugins')->isEnabled('diy-queue')) {
                    $set = \Setting::get('plugin.diy-queue');
                    return $set['plugin_name'] ? : self::getDividendCodeName($code);
                }
                break;
            case self::FROZE_WITH_DRAW:
                if (app('plugins')->isEnabled('froze')) {
                    $set = $set = array_pluck(\Setting::getAllByGroup('froze')->toArray(), 'value', 'key');
                    return $set['name'] ? $set['name'].'提现' : self::getDividendCodeName($code);
                }
                break;
            case self::FULL_RETURN:
                if (app('plugins')->isEnabled('full-return')) {
                    $set = \Setting::get('plugin.full-return');
                    return $set['full_return_name'] ? : self::getDividendCodeName($code);
                }
                break;
            case self::HOTEL_CASHIER_ORDER:
            case self::HOTEL_ORDER:
                if (app('plugins')->isEnabled('hotel')) {
                    $set = \Setting::get('plugin.hotel');
                    if ($code == self::HOTEL_CASHIER_ORDER) {
                        $str = '收银台';
                    } else {
                        $str = '提现';
                    }
                    return $set['hotels'] ? $set['hotels'].$str : self::getDividendCodeName($code);
                }
                break;
            case self::INTEGRAL_WITHDRAW:
                if (app('plugins')->isEnabled('hotel')) {
                    $set = array_pluck(\Setting::getAllByGroup('integral')->toArray(), 'value', 'key');
                    return $set['plugin_name'] ? $set['plugin_name'].'提现' : self::getDividendCodeName($code);
                }
                break;
            case self::LOVE_WITHDRAW_RECORDS:
            case self::LOVE_RETURN_LOG:
            case self::LOVE_TEAM_AWARD:
            case self::PERIOD_RETURN_LOG:
                if (app('plugins')->isEnabled('love')) {
                    $set = \Setting::get('love');
                    if ($code == self::LOVE_WITHDRAW_RECORDS) {
                        $str = '提现';
                    } elseif ($code == self::LOVE_RETURN_LOG) {
                        $str = '返现';
                    } elseif ($code == self::LOVE_RETURN_LOG) {
                        $str = '团队奖励';
                    } else {
                        $str = '周期奖励';
                    }
                    return $set['name'] ? $set['name'].$str : self::getDividendCodeName($code);
                }
                break;
            case self::MANUAL_LOG:
                if (app('plugins')->isEnabled('manual-bonus')) {
                    $set = \Setting::get('plugin.manual_bonus');
                    return $set['name'] ? $set['name'].'分红' : self::getDividendCodeName($code);
                }
                break;
            case self::PACKAGE_DELIVER_BONUS:
                if (app('plugins')->isEnabled('package-deliver')) {
                    $set = \Setting::get('plugin.package_deliver');
                    return $set['customize_name'] ? $set['customize_name'].'奖励' : self::getDividendCodeName($code);
                }
                break;
            case self::RED_PACKET_RECEIVE_LOGS:
                if (app('plugins')->isEnabled('red-packet')) {
                    $set = \Setting::get('plugin.red-packet');
                    return $set['red_packet_title'] ? $set['red_packet_title'] : self::getDividendCodeName($code);
                }
                break;
            case self::SHARE_CHAIN_AWARD_LOG:
                if (app('plugins')->isEnabled('share-chain')) {
                    $set = \Setting::get('plugin.shareChain');
                    return $set['plugin_name'] ? $set['plugin_name'] : self::getDividendCodeName($code);
                }
                break;
            case self::RETURN_SINGLE_LOG:
                if (app('plugins')->isEnabled('single-return')) {
                    $set = \Setting::get('shop.lang', ['lang' => 'zh_cn']);
                    return $set[$set['lang']]['single_return']['title'] ? $set[$set['lang']]['single_return']['title'] : self::getDividendCodeName($code);
                }
                break;
            case self::TEAM_RETURN_LOG:
                if (app('plugins')->isEnabled('team-return')) {
                    $set = \Setting::get('shop.lang', ['lang' => 'zh_cn']);
                    return $set[$set['lang']]['team_return']['title'] ? $set[$set['lang']]['team_return']['title'] : self::getDividendCodeName($code);
                }
                break;
            case self::ASSEMBLE://'安装服务分红',
            case self::ASSEMBLE_WAGES://'安装服务工资',
                if (app('plugins')->isEnabled('assemble')) {
                    $set = \Setting::get('plugin.assemble');
                    if ($code == self::ASSEMBLE) {
                        $str = $set['assemble_name'] ? $set['assemble_name'].'分红' : self::getDividendCodeName($code);
                    } else {
                        $str = $set['assemble_worker_name'] ? $set['assemble_worker_name'].'工资' : self::getDividendCodeName($code);
                    }
                    return $str;
                }
                break;
            case self::LIVE_INSTALL:
                if (app('plugins')->isEnabled('live-install')) {
                    $set = \Setting::get('plugin.live_install');
                    return $set['plugin_name'] ? : self::getDividendCodeName($code);
                }
                break;
        }
        return self::getDividendCodeName($code);
    }

    private static function getDividendCodeName($code)
    {
        $income_config_desc = [
            self::COMMISSION_ORDER                      =>  '分销佣金',
            self::TEAM_DIVIDEND                         =>  '经销商分红',
            self::AGENT_DIVIDEND                        =>  '代理商分红',
            self::APPOINTMENT_ORDER                     =>  '门店预约',
            self::AREA_DIVIDEND                         =>  '区域分红',
            self::MEMBER_BONUS_RECORDS                  =>  '数字资产分红',
            self::SELL_RECORDS                          =>  '数字资产交易',
            self::AUCTION_BID_REWARD                    =>  '拍卖奖励',
            self::AUCTION_PREPAYMENT                    =>  '拍卖预付款提现',
            self::CHANNEL_AWARD                         =>  '代理商',
            self::CLOCK_REWARD_LOG                      =>  '早起打卡',
            self::COMMISSION_ACTIVITY_REWARD            =>  '分销活动',
            self::COMMISSION_MANAGE_LOG                 =>  '分销商管理奖',
            self::CONSUME_RETURN                        =>  '消费返现',
            self::DELIVERY_STATION_DIVIDEND             =>  '配送站提现',
            self::DISTRIBUTOR_MANAGE                    =>  '管理津贴',
            self::DIY_QUEUE_LOG                         =>  '自定义队列',
            self::TEAM_PERFORMANCE_STATISTICS_LOG       =>  '精英奖',
            self::ENERGY_CABIN                          =>  '能量舱奖励',
            self::FIXED_REWARD_LOG                      =>  '固定奖励',
            self::FROZE_WITH_DRAW                       =>  '冻结币提现',
            self::FULL_RETURN                           =>  '满额赠送',
            self::GLOBAL_DIVIDEND                       =>  '权益分红',
            self::HOTEL_CASHIER_ORDER                   =>  '酒店收银台',
            self::HOTEL_ORDER                           =>  '酒店提现',
            self::NOMINATE_BONUS                        =>  '推荐奖励',
            self::INTEGRAL_WITHDRAW                     =>  '消费积分提现',
            self::INTERESTS_DIVIDEND                    =>  '权益值分红',
            self::CONSUMPTION_RECORDS                   =>  '区间消费返点',
            self::LEVEL_RETURN                          =>  '等级返现',
            self::LOVE_WITHDRAW_RECORDS                 =>  '爱心值提现',
            self::LOVE_RETURN_LOG                       =>  '爱心值返现',
            self::LOVE_TEAM_AWARD                       =>  '爱心值团队奖励',
            self::MANAGE_AWARD_RECORDS                  =>  '管理奖',
            self::MANAGEMENT_DIVIDEND                   =>  '管理奖分红',
            self::MANUAL_LOG                            =>  '手动分红',
            self::MEMBER_RETURN_LOG                     =>  '排队奖励',
            self::MERCHANT_BONUS_LOG                    =>  '招商分红',
            self::MICRO_SHOP_BONUS_LOG                  =>  '微店分红',
            self::MICRO_COMMUNITIES_STICK_REWARD        =>  '微社区打赏',
            self::MEMBER_REFERRAL_AWARD                 =>  '直推奖',
            self::MEMBER_TEAM_AWARD                     =>  '团队奖/感恩奖',
            self::ORDER_PARENTING_AWARD                 =>  '育人奖',
            self::ORDER_TEAM_AWARD                      =>  '团队管理奖',
            self::TIER_AWARD                            =>  '平级奖',
            self::NET_CAR_DIVIDEND                      =>  '网约车分红',
            self::TEAM_PRIZE                            =>  '团队业绩奖',
            self::ORDINARY_DIVIDEND                     =>  '平级奖提现',
            self::OZY_AWARD_RECORD                      =>  'OZY',
            self::PACKAGE_DELIVER_BONUS                 =>  '自提点奖励',
            self::PARTNER_REWARD_LOG                    =>  '股东奖励',
            self::PENDING_ORDER_DIVIDEND                =>  '商品挂单提现',
            self::PERFORMANCE_BONUS                     =>  '业绩奖励',
            self::PERIOD_RETURN_LOG                     =>  '爱心值周期奖励',
            self::POINT_ACTIVITY_AWARD_LOG              =>  '积分活动奖励',
            self::RED_PACKET_RECEIVE_LOGS               =>  '每日红包',
            self::REVENUE_AWARD_BONUS                   =>  '收益奖',
            self::ROOM_BONUS_LOG                        =>  '主播分红',
            self::SALES_COMMISSION                      =>  '销售佣金',
            self::SCORING_DIVIDEND                      =>  '会员分红',
            self::SCORING_REWARD                        =>  '会员分红(消费奖励)',
            self::SERVICE_STATION_DIVIDEND              =>  '服务站提现',
            self::SHARE_CHAIN_AWARD_LOG                 =>  '共享链',
            self::SHAREHOLDER_DIVIDEND                  =>  '股东分红',
            self::RETURN_SINGLE_LOG                     =>  '消费赠送',
            self::STORE_CASHIER_ORDER                   =>  '门店收银台',
            self::STORE_CASHIER_STORE_ORDER             =>  '门店提现',
            self::STORE_CASHIER_BOSS_ORDER              =>  '连锁店提现',
            self::TEAM_MANAGE_BONUS                     =>  '区域代理管理',
            self::TEAM_MANAGEMENT_LOG                   =>  '经销商管理奖',
            self::TEAM_RETURN_LOG                       =>  '经销商奖励',
            self::TEAM_REWARDS_ORDER                    =>  '团队奖励订单奖励',
            self::TEAM_MEMBER_TASKS                     =>  '团队奖励任务奖励',
            self::TEAM_SALES_BONUS                      =>  '团队销售佣金',
            self::LECTURER_REWARD_LOG                   =>  '讲师分红',
            self::VIDEO_SHARE_BONUS                     =>  '发现视频',
            self::WEIGHTED_DIVIDEND                     =>  '加权分红',
            self::AUCTION_INCOME                        =>  '拍卖官收入',
            self::AUCTION_ENDORSEMENT                   =>  '拍卖代言费',
            self::AUCTION_AREA                          =>  '拍卖区域分红',
            self::TEAM_SZTT                             =>  '新团队分红',
            self::TEAM_SIDEWAYS_WITHDRAW                =>  '团队平级奖',
            self::COLLAGE_BONUS                         =>  '拼单',
            self::COLLAGE_AREA_DIVIDEND                 =>  '拼单区域分红',
            self::CONSUME_RED_PACKET                    =>  '消费红包',
            self::SNATCH_REWARD                         =>  '抢团',
            self::REGIONAL_REWARD                       =>  '区域业绩奖励',
            self::CLOUD_WAREHOUSE                       =>  '云仓释放',
            self::STORE_SHAREHOLDER                     =>  '门店股东',
            self::ASSEMBLE                              =>  '安装服务分红',
            self::ASSEMBLE_WAGES                        =>  '安装服务工资',
            self::PERIOD_RETURN                         =>  '爱心值周期奖励',
            self::TEAM_FJYX                             =>  '经销商(fjyx)',
            self::RECOMMENDER                           =>  '推荐官',
            self::SUPERIOR_REWARD                       =>  '上级奖',
            self::SELL_AWARD                            =>  '销售奖励',
            self::EQUITY_REWARD                         =>  '权益奖励',
            self::STORE_CARD_INCOME                     =>  '门店消费卡',
            self::CIRCLE_VIDEO_BOUNS                    =>  '圈子视频分红',
            self::CIRCLE_INVITATION_REWARD              =>  '圈子帖子打赏',
            self::CIRCLE_ADD                            =>  '圈子加入圈子',
            self::CONSUME_REWARD                        =>  '消费报销',
            self::AGENCY_REWARD                         =>  '云库存代理商奖励',
	        self::RANKING_AWARD                         =>  '推客排行榜奖励',
            self::RESERVE_FUND                          =>  '储备金',
            self::ROOM_CODE                             => '直播授权码',
            self::SCHOOL_COMPANY                        =>  '一校一企',
	        self::CONSUMER_REWARD                       =>  '消费奖励',
            self::LIVE_INSTALL                          =>  '上门安装分红',
            self::INVEST_PEOPLE                         =>  '招商专员',
            self::NEW_RETAIL_REWARD                     =>  '新零售奖励',
            self::NEW_RETAIL_RIGHT_REWARD               =>  '股权分红',
            self::STORE_BUSINESS_ALLIANCE_RECOMMEND     =>  '店商联盟推荐奖',
            self::STORE_BUSINESS_ALLIANCE_BUSINESS      =>  '店商联盟商家补贴',
            self::STORE_BUSINESS_ALLIANCE_SERVICE       =>  '店商联盟服务奖',
            self::STORE_BUSINESS_ALLIANCE_OPERATION     =>  '店商联盟运营奖',
            self::STORE_BUSINESS_ALLIANCE_PRICE         =>  '店商联盟差价收益',
            self::STORE_BUSINESS_ALLIANCE_STORE         =>  '店商联盟门店奖',
            self::STORE_BUSINESS_ALLIANCE_SUPPORT       =>  '店商联盟帮扶奖',
            self::STORE_BUSINESS_ALLIANCE_TASK          =>  '店商联盟任务奖',
            self::STORE_BUSINESS_ALLIANCE_TEAM          =>  '店商联盟团队提成',
            self::PLUGIN_PARENT_PAYMENT_COMMISSION      =>  '上级代付佣金',
	        self::STORE_REWARDS                         =>  '门店奖励',
            self::STORE_PROJECTS_ORDER                  =>  '多门店核销',
	        self::ZHP_REWARD                            =>  '珍惠拼奖励',
	        self::STORE_BALANCE_AWARD                   =>  '门店余额充值奖励',
	        self::COMMISSION_EXTRA_BONUS                =>  '分销额外分红',
        ];
        return $income_config_desc[$code];
    }

    private static function getDividendClass($code)
    {
        $income_config_class = [
            self::COMMISSION_ORDER                  =>  'Yunshop\Commission\models\CommissionOrder',
            self::TEAM_DIVIDEND                     =>  'Yunshop\TeamDividend\models\TeamDividendModel',
            self::AGENT_DIVIDEND                    =>  'Yunshop\AgentDividend\common\models\AgentDividendModel',
            self::APPOINTMENT_ORDER                 =>  'Yunshop\Appointment\common\models\AppointmentOrderService',
            self::AREA_DIVIDEND                     =>  'Yunshop\AreaDividend\models\AreaDividend',
            self::MEMBER_BONUS_RECORDS              =>  'Yunshop\Asset\Common\Models\MemberBonusRecordsModel',
            self::SELL_RECORDS                      =>  'Yunshop\Asset\Common\Models\Digitization\SellRecordsModel',
            self::AUCTION_BID_REWARD                =>  'Yunshop\Auction\models\AuctionBidReward',
            self::AUCTION_PREPAYMENT                =>  'auction_prepayment',
            self::CHANNEL_AWARD                     =>  'Yunshop\Channel\model\ChannelAward',
            self::CLOCK_REWARD_LOG                  =>  'Yunshop\ClockIn\models\ClockRewardLogModel',
            self::COMMISSION_ACTIVITY_REWARD        =>  'Yunshop\CommissionActivity\common\models\CommissionActivityReward',
            self::COMMISSION_MANAGE_LOG             =>  'Yunshop\CommissionManage\common\models\CommissionManageLogModel',
            self::CONSUME_RETURN                    =>  'Yunshop\ConsumeReturn\common\models\Log',
            self::DELIVERY_STATION_DIVIDEND         =>  'Yunshop\DeliveryStation\models\DeliveryStationDividend',
            self::DISTRIBUTOR_MANAGE                =>  'Yunshop\DistributorManage\models\DistributorManage',
            self::DIY_QUEUE_LOG                     =>  'Yunshop\DiyQueue\common\models\DiyQueueLog',
            self::TEAM_PERFORMANCE_STATISTICS_LOG   =>  'Yunshop\EliteAward\models\TeamPerformanceStatisticsLogModel',
            self::ENERGY_CABIN                      =>  'Yunshop\EnergyCabin\models\Dividend',
            self::FIXED_REWARD_LOG                  =>  'Yunshop\FixedReward\models\FixedRewardLog',
            self::FROZE_WITH_DRAW                   =>  'Yunshop\Froze\Common\Models\FrozeWithdraw',
            self::FULL_RETURN                       =>  'Yunshop\FullReturn\common\models\Log',
            self::GLOBAL_DIVIDEND                   =>  'Yunshop\GlobalDividend\models\GlobalDividendModel',
            self::HOTEL_CASHIER_ORDER               =>  'Yunshop\Hotel\common\models\CashierOrder',
            self::HOTEL_ORDER                       =>  'Yunshop\Hotel\common\models\HotelOrder',
            self::NOMINATE_BONUS                    =>  'Yunshop\Nominate\models\NominateBonus',
            self::INTEGRAL_WITHDRAW                 =>  'Yunshop\Integral\Common\Models\IntegralWithdrawModel',
            self::INTERESTS_DIVIDEND                =>  'Yunshop\InterestsDividend\models\InterestsDividendModel',
            self::CONSUMPTION_RECORDS               =>  'Yunshop\IntervalConsumption\Common\models\ConsumptionRecords',
            self::LEVEL_RETURN                      =>  'Yunshop\LevelReturn\models\LevelReturnModel',
            self::LOVE_WITHDRAW_RECORDS             =>  'Yunshop\Love\Common\Models\LoveWithdrawRecords',
            self::LOVE_RETURN_LOG                   =>  'Yunshop\Love\Common\Models\LoveReturnLogModel',
            self::LOVE_TEAM_AWARD                   =>  'Yunshop\LoveTeam\model\LoveTeamAward',
            self::MANAGE_AWARD_RECORDS              =>  'Yunshop\ManageAward\Common\Models\AwardRecordsModel',
            self::MANAGEMENT_DIVIDEND               =>  'Yunshop\ManagementDividend\models\ManagementDividend',
            self::MANUAL_LOG                        =>  'Yunshop\ManualBonus\models\ManualLog',
            self::MEMBER_RETURN_LOG                 =>  'Yunshop\MemberReturn\common\models\Log',
            self::MERCHANT_BONUS_LOG                =>  'Yunshop\Merchant\common\models\MerchantBonusLog',
            self::MICRO_SHOP_BONUS_LOG              =>  'Yunshop\Micro\common\models\MicroShopBonusLog',
            self::MICRO_COMMUNITIES_STICK_REWARD    =>  'Yunshop\MicroCommunities\models\MicroCommunitiesStickReward',
            self::MEMBER_REFERRAL_AWARD             =>  'Yunshop\Mryt\common\models\MemberReferralAward',
            self::MEMBER_TEAM_AWARD                 =>  'Yunshop\Mryt\common\models\MemberTeamAward',
            self::ORDER_PARENTING_AWARD             =>  'Yunshop\Mryt\common\models\OrderParentingAward',
            self::ORDER_TEAM_AWARD                  =>  'Yunshop\Mryt\common\models\OrderTeamAward',
            self::TIER_AWARD                        =>  'Yunshop\Mryt\common\models\TierAward',
            self::NET_CAR_DIVIDEND                  =>  'Yunshop\NetCar\models\NetCarDividend',
            self::TEAM_PRIZE                        =>  'Yunshop\Nominate\models\TeamPrize',
            self::ORDINARY_DIVIDEND                 =>  'Yunshop\OrdinaryDividend\models\RewardModel',
            self::OZY_AWARD_RECORD                  =>  'Yunshop\Ozy\models\AwardRecordModel',
            self::PACKAGE_DELIVER_BONUS             =>  'Yunshop\PackageDeliver\model\DeliverBonus',
            self::PARTNER_REWARD_LOG                =>  'Yunshop\PartnerReward\common\models\PartnerRewardLogModel',
            self::PENDING_ORDER_DIVIDEND            =>  'Yunshop\PendingOrder\models\PendingOrderDividend',
            self::PERFORMANCE_BONUS                 =>  'Yunshop\Performance\common\model\PerformanceBonus',
            self::PERIOD_RETURN_LOG                 =>  'Yunshop\PeriodReturn\model\PeriodLog',
            self::POINT_ACTIVITY_AWARD_LOG          =>  'Yunshop\PointActivity\Common\Models\PointActivityAwardLog',
            self::RED_PACKET_RECEIVE_LOGS           =>  'Yunshop\RedPacket\models\ReceiveLogsModel',
            self::REVENUE_AWARD_BONUS               =>  'Yunshop\RevenueAward\model\IncomeBonusLogModel',
            self::ROOM_BONUS_LOG                    =>  'Yunshop\Room\models\BonusLog',
            self::SALES_COMMISSION                  =>  'Yunshop\SalesCommission\models\SalesCommission',
            self::SCORING_DIVIDEND                  =>  'Yunshop\ScoringDividend\models\ScoringDividendModel',
            self::SCORING_REWARD                    =>  'Yunshop\ScoringDividend\models\ScoringRewardModel',
            self::SERVICE_STATION_DIVIDEND          =>  'Yunshop\ServiceStation\models\ServiceStationDividend',
            self::SHARE_CHAIN_AWARD_LOG             =>  'Yunshop\ShareChain\common\model\ShareChainAwardLog',
            self::SHAREHOLDER_DIVIDEND              =>  'Yunshop\ShareholderDividend\models\ShareholderDividendModel',
            self::RETURN_SINGLE_LOG                 =>  'Yunshop\SingleReturn\models\ReturnSingleLog',
            self::STORE_CASHIER_ORDER               =>  'Yunshop\StoreCashier\common\models\CashierOrder',
            self::STORE_CASHIER_STORE_ORDER         =>  'Yunshop\StoreCashier\common\models\StoreOrder',
            self::STORE_CASHIER_BOSS_ORDER          =>  'Yunshop\StoreCashier\common\models\BossOrder',
            self::TEAM_MANAGE_BONUS                 =>  'Yunshop\TeamManage\common\model\Bonus',
            self::TEAM_MANAGEMENT_LOG               =>  'Yunshop\TeamManagement\models\TeamManagementLogModel',
            self::TEAM_RETURN_LOG                   =>  'Yunshop\TeamReturn\models\TeamReturnLog',
            self::TEAM_REWARDS_ORDER                =>  'Yunshop\TeamRewards\common\models\TeamRewardsOrderModel',
            self::TEAM_MEMBER_TASKS                 =>  'Yunshop\TeamRewards\common\models\TeamMemberTasksModel',
            self::TEAM_SALES_BONUS                  =>  'Yunshop\TeamSales\common\models\TeamSalesModel',
            self::LECTURER_REWARD_LOG               =>  'Yunshop\VideoDemand\models\LecturerRewardLogModel',
            self::VIDEO_SHARE_BONUS                 =>  'Yunshop\VideoShare\common\model\Bonus',
            self::WEIGHTED_DIVIDEND                 =>  'Yunshop\WeightedDividend\models\RewardModel',
            self::TEAM_SZTT                         =>  'Yunshop\TeamSztt\models\TeamSzttModel',
            self::TEAM_SIDEWAYS_WITHDRAW            =>  'Yunshop\TeamSideways\model\SidewaysWithdrawLog',
            self::COLLAGE_BONUS                     =>  'Yunshop\Collage\models\BonusModel',
            self::COLLAGE_AREA_DIVIDEND             =>  'Yunshop\Collage\models\AreaDividendModel',
            self::CONSUME_RED_PACKET                =>  'Yunshop\ConsumeRedPacket\Common\Models\PondReceiveModel',
            self::SNATCH_REWARD                     =>  'Yunshop\SnatchRegiment\models\SnatchReward',
            self::REGIONAL_REWARD                   =>  'Yunshop\RegionalReward\Common\models\RecordModel',
            self::CLOUD_WAREHOUSE                   =>  'Yunshop\CloudWarehouse\models\CloudWarehouseDividend',
            self::STORE_SHAREHOLDER                 =>  'Yunshop\StoreShareholder\model\ShareholderBonusInfo',
            self::ASSEMBLE                          =>  'Yunshop\Assemble\Common\Models\OrderBonusModel',
            self::ASSEMBLE_WAGES                    =>  'Yunshop\Assemble\Common\Models\OrderWagesModel',
            self::PERIOD_RETURN                     =>  'Yunshop\PeriodReturn\model\PeriodLog',
            self::TEAM_FJYX                         =>  'Yunshop\TeamFjyx\models\TeamFjyxModel',
            self::RECOMMENDER                       =>  'Yunshop\Recommender\models\RewardModel',
            self::SUPERIOR_REWARD                   =>  'Yunshop\SuperiorReward\models\OrderBuyModel',
            self::SELL_AWARD                        =>  'Yunshop\SellAward\model\AwardLog',
            self::EQUITY_REWARD                     =>  'Yunshop\EquityReward\models\EquityReward',
            self::STORE_CARD_INCOME                 =>  'Yunshop\StoreCard\Common\Models\CardIncomeModel',
            self::CIRCLE_VIDEO_BOUNS                =>  'Yunshop\Circle\common\model\CircleVideoBonus',
            self::CIRCLE_INVITATION_REWARD          =>  'Yunshop\Circle\common\model\CircleReward',
            self::CIRCLE_ADD                        =>  'Yunshop\Circle\common\model\CirclePayLog',
            self::CONSUME_REWARD                    =>  'Yunshop\ConsumeReward\models\RewardLog',
            self::AGENCY_REWARD                     =>  'Yunshop\Agency\models\AgencyModel',
	        self::RANKING_AWARD                     =>  'Yunshop\CommissionRanking\models\CommissionOrder',
            self::RESERVE_FUND                      =>  'Yunshop\ReserveFund\models\ReserveFundBonusModel',
            self::ROOM_CODE                         =>  'Yunshop\Room\models\CodeUsed',
            self::SCHOOL_COMPANY                    =>  'Yunshop\SchoolCompany\models\IncomeModel',
	        self::CONSUMER_REWARD                   =>  'Yunshop\ConsumerReward\common\models\ConsumerRewardRecordModel',
            self::LIVE_INSTALL                      =>  'Yunshop\LiveInstall\models\WorkerReward',
            self::INVEST_PEOPLE                     =>  'Yunshop\InvestPeople\models\Dividend',
            self::NEW_RETAIL_REWARD                 =>  'Yunshop\NewRetail\models\RewardModel',
            self::NEW_RETAIL_RIGHT_REWARD           =>  'Yunshop\NewRetail\models\StockRightReward',
            self::STORE_BUSINESS_ALLIANCE_RECOMMEND =>  'Yunshop\StoreBusinessAlliance\models\StoreBusinessAllianceRecommendAwardModel',
            self::STORE_BUSINESS_ALLIANCE_BUSINESS  =>  'Yunshop\StoreBusinessAlliance\models\StoreBusinessAllianceBusinessAwardModel',
            self::STORE_BUSINESS_ALLIANCE_SERVICE   =>  'Yunshop\StoreBusinessAlliance\models\StoreBusinessAllianceServiceAwardModel',
            self::STORE_BUSINESS_ALLIANCE_OPERATION =>  'Yunshop\StoreBusinessAlliance\models\StoreBusinessAllianceOperationAwardModel',
            self::STORE_BUSINESS_ALLIANCE_PRICE     =>  'Yunshop\StoreBusinessAlliance\models\StoreBusinessAlliancePriceDifferenceAwardModel',
            self::STORE_BUSINESS_ALLIANCE_STORE     =>  'Yunshop\StoreBusinessAlliance\models\StoreBusinessAllianceStoreAwardModel',
            self::STORE_BUSINESS_ALLIANCE_SUPPORT   =>  'Yunshop\StoreBusinessAlliance\models\StoreBusinessAllianceSupportAwardModel',
            self::STORE_BUSINESS_ALLIANCE_TASK      =>  'Yunshop\StoreBusinessAlliance\models\StoreBusinessAllianceTaskAwardModel',
            self::STORE_BUSINESS_ALLIANCE_TEAM      =>  'Yunshop\StoreBusinessAlliance\models\StoreBusinessAllianceTeamAwardModel',
            self::PLUGIN_PARENT_PAYMENT_COMMISSION  =>  'Yunshop\ParentPayment\common\models\BehalfOrderModel',
	        self::STORE_REWARDS                     =>  'Yunshop\StoreRewards\common\models\StoreRewardsRecord',
            self::STORE_PROJECTS_ORDER              =>  'Yunshop\StoreProjects\common\models\ProjectsOrderService',
	        self::ZHP_REWARD                        =>  'Yunshop\ZhpGroupLottery\models\ZhpRewardLogModel',
	        self::STORE_BALANCE_AWARD               =>  'Yunshop\StoreBalance\model\BalanceAward',
	        self::COMMISSION_EXTRA_BONUS            =>  'Yunshop\CommissionExtra\models\CommissionExtraBonusModel',
            ];
        return $income_config_class[$code];
    }

    public static function getClass()
    {
        return [
            self::COMMISSION_ORDER                  =>  'Yunshop\Commission\models\CommissionOrder',
            self::TEAM_DIVIDEND                     =>  'Yunshop\TeamDividend\models\TeamDividendModel',
            self::AGENT_DIVIDEND                    =>  'Yunshop\AgentDividend\common\models\AgentDividendModel',
            self::APPOINTMENT_ORDER                 =>  'Yunshop\Appointment\common\models\AppointmentOrderService',
            self::AREA_DIVIDEND                     =>  'Yunshop\AreaDividend\models\AreaDividend',
            self::MEMBER_BONUS_RECORDS              =>  'Yunshop\Asset\Common\Models\MemberBonusRecordsModel',
            self::SELL_RECORDS                      =>  'Yunshop\Asset\Common\Models\Digitization\SellRecordsModel',
            self::AUCTION_BID_REWARD                =>  'Yunshop\Auction\models\AuctionBidReward',
            self::AUCTION_PREPAYMENT                =>  'auction_prepayment',
            self::CHANNEL_AWARD                     =>  'Yunshop\Channel\model\ChannelAward',
            self::CLOCK_REWARD_LOG                  =>  'Yunshop\ClockIn\models\ClockRewardLogModel',
            self::COMMISSION_ACTIVITY_REWARD        =>  'Yunshop\CommissionActivity\common\models\CommissionActivityReward',
            self::COMMISSION_MANAGE_LOG             =>  'Yunshop\CommissionManage\common\models\CommissionManageLogModel',
            self::CONSUME_RETURN                    =>  'Yunshop\ConsumeReturn\common\models\Log',
            self::DELIVERY_STATION_DIVIDEND         =>  'Yunshop\DeliveryStation\models\DeliveryStationDividend',
            self::DISTRIBUTOR_MANAGE                =>  'Yunshop\DistributorManage\models\DistributorManage',
            self::DIY_QUEUE_LOG                     =>  'Yunshop\DiyQueue\common\models\DiyQueueLog',
            self::TEAM_PERFORMANCE_STATISTICS_LOG   =>  'Yunshop\EliteAward\models\TeamPerformanceStatisticsLogModel',
            self::ENERGY_CABIN                      =>  'Yunshop\EnergyCabin\models\Dividend',
            self::FIXED_REWARD_LOG                  =>  'Yunshop\FixedReward\models\FixedRewardLog',
            self::FROZE_WITH_DRAW                   =>  'Yunshop\Froze\Common\Models\FrozeWithdraw',
            self::FULL_RETURN                       =>  'Yunshop\FullReturn\common\models\Log',
            self::GLOBAL_DIVIDEND                   =>  'Yunshop\GlobalDividend\models\GlobalDividendModel',
            self::HOTEL_CASHIER_ORDER               =>  'Yunshop\Hotel\common\models\CashierOrder',
            self::HOTEL_ORDER                       =>  'Yunshop\Hotel\common\models\HotelOrder',
            self::NOMINATE_BONUS                    =>  'Yunshop\Nominate\models\NominateBonus',
            self::INTEGRAL_WITHDRAW                 =>  'Yunshop\Integral\Common\Models\IntegralWithdrawModel',
            self::INTERESTS_DIVIDEND                =>  'Yunshop\InterestsDividend\models\InterestsDividendModel',
            self::CONSUMPTION_RECORDS               =>  'Yunshop\IntervalConsumption\Common\models\ConsumptionRecords',
            self::LEVEL_RETURN                      =>  'Yunshop\LevelReturn\models\LevelReturnModel',
            self::LOVE_WITHDRAW_RECORDS             =>  'Yunshop\Love\Common\Models\LoveWithdrawRecords',
            self::LOVE_RETURN_LOG                   =>  'Yunshop\Love\Common\Models\LoveReturnLogModel',
            self::LOVE_TEAM_AWARD                   =>  'Yunshop\LoveTeam\model\LoveTeamAward',
            self::MANAGE_AWARD_RECORDS              =>  'Yunshop\ManageAward\Common\Models\AwardRecordsModel',
            self::MANAGEMENT_DIVIDEND               =>  'Yunshop\ManagementDividend\models\ManagementDividend',
            self::MANUAL_LOG                        =>  'Yunshop\ManualBonus\models\ManualLog',
            self::MEMBER_RETURN_LOG                 =>  'Yunshop\MemberReturn\common\models\Log',
            self::MERCHANT_BONUS_LOG                =>  'Yunshop\Merchant\common\models\MerchantBonusLog',
            self::MICRO_SHOP_BONUS_LOG              =>  'Yunshop\Micro\common\models\MicroShopBonusLog',
            self::MICRO_COMMUNITIES_STICK_REWARD    =>  'Yunshop\MicroCommunities\models\MicroCommunitiesStickReward',
            self::MEMBER_REFERRAL_AWARD             =>  'Yunshop\Mryt\common\models\MemberReferralAward',
            self::MEMBER_TEAM_AWARD                 =>  'Yunshop\Mryt\common\models\MemberTeamAward',
            self::ORDER_PARENTING_AWARD             =>  'Yunshop\Mryt\common\models\OrderParentingAward',
            self::ORDER_TEAM_AWARD                  =>  'Yunshop\Mryt\common\models\OrderTeamAward',
            self::TIER_AWARD                        =>  'Yunshop\Mryt\common\models\TierAward',
            self::NET_CAR_DIVIDEND                  =>  'Yunshop\NetCar\models\NetCarDividend',
            self::TEAM_PRIZE                        =>  'Yunshop\Nominate\models\TeamPrize',
            self::ORDINARY_DIVIDEND                 =>  'Yunshop\OrdinaryDividend\models\RewardModel',
            self::OZY_AWARD_RECORD                  =>  'Yunshop\Ozy\models\AwardRecordModel',
            self::PACKAGE_DELIVER_BONUS             =>  'Yunshop\PackageDeliver\model\DeliverBonus',
            self::PARTNER_REWARD_LOG                =>  'Yunshop\PartnerReward\common\models\PartnerRewardLogModel',
            self::PENDING_ORDER_DIVIDEND            =>  'Yunshop\PendingOrder\models\PendingOrderDividend',
            self::PERFORMANCE_BONUS                 =>  'Yunshop\Performance\common\model\PerformanceBonus',
            self::PERIOD_RETURN_LOG                 =>  'Yunshop\PeriodReturn\model\PeriodLog',
            self::POINT_ACTIVITY_AWARD_LOG          =>  'Yunshop\PointActivity\Common\Models\PointActivityAwardLog',
            self::RED_PACKET_RECEIVE_LOGS           =>  'Yunshop\RedPacket\models\ReceiveLogsModel',
            self::REVENUE_AWARD_BONUS               =>  'Yunshop\RevenueAward\model\IncomeBonusLogModel',
            self::ROOM_BONUS_LOG                    =>  'Yunshop\Room\models\BonusLog',
            self::SALES_COMMISSION                  =>  'Yunshop\SalesCommission\models\SalesCommission',
            self::SCORING_DIVIDEND                  =>  'Yunshop\ScoringDividend\models\ScoringDividendModel',
            self::SCORING_REWARD                    =>  'Yunshop\ScoringDividend\models\ScoringRewardModel',
            self::SERVICE_STATION_DIVIDEND          =>  'Yunshop\ServiceStation\models\ServiceStationDividend',
            self::SHARE_CHAIN_AWARD_LOG             =>  'Yunshop\ShareChain\common\model\ShareChainAwardLog',
            self::SHAREHOLDER_DIVIDEND              =>  'Yunshop\ShareholderDividend\models\ShareholderDividendModel',
            self::RETURN_SINGLE_LOG                 =>  'Yunshop\SingleReturn\models\ReturnSingleLog',
            self::STORE_CASHIER_ORDER               =>  'Yunshop\StoreCashier\common\models\CashierOrder',
            self::STORE_CASHIER_STORE_ORDER         =>  'Yunshop\StoreCashier\common\models\StoreOrder',
            self::STORE_CASHIER_BOSS_ORDER          =>  'Yunshop\StoreCashier\common\models\BossOrder',
            self::TEAM_MANAGE_BONUS                 =>  'Yunshop\TeamManage\common\model\Bonus',
            self::TEAM_MANAGEMENT_LOG               =>  'Yunshop\TeamManagement\models\TeamManagementLogModel',
            self::TEAM_RETURN_LOG                   =>  'Yunshop\TeamReturn\models\TeamReturnLog',
            self::TEAM_REWARDS_ORDER                =>  'Yunshop\TeamRewards\common\models\TeamRewardsOrderModel',
            self::TEAM_MEMBER_TASKS                 =>  'Yunshop\TeamRewards\common\models\TeamMemberTasksModel',
            self::TEAM_SALES_BONUS                  =>  'Yunshop\TeamSales\common\models\TeamSalesModel',
            self::LECTURER_REWARD_LOG               =>  'Yunshop\VideoDemand\models\LecturerRewardLogModel',
            self::VIDEO_SHARE_BONUS                 =>  'Yunshop\VideoShare\common\model\Bonus',
            self::WEIGHTED_DIVIDEND                 =>  'Yunshop\WeightedDividend\models\RewardModel',
            self::TEAM_SZTT                         =>  'Yunshop\TeamSztt\models\TeamSzttModel',
            self::TEAM_SIDEWAYS_WITHDRAW            =>  'Yunshop\TeamSideways\model\SidewaysWithdrawLog',
            self::COLLAGE_BONUS                     =>  'Yunshop\Collage\models\BonusModel',
            self::COLLAGE_AREA_DIVIDEND             =>  'Yunshop\Collage\models\AreaDividendModel',
            self::CONSUME_RED_PACKET                =>  'Yunshop\ConsumeRedPacket\Common\Models\PondReceiveModel',
            self::SNATCH_REWARD                     =>  'Yunshop\SnatchRegiment\models\SnatchReward',
            self::REGIONAL_REWARD                   =>  'Yunshop\RegionalReward\Common\models\RecordModel',
            self::CLOUD_WAREHOUSE                   =>  'Yunshop\CloudWarehouse\models\CloudWarehouseDividend',
            self::STORE_SHAREHOLDER                 =>  'Yunshop\StoreShareholder\model\ShareholderBonusInfo',
            self::ASSEMBLE                          =>  'Yunshop\Assemble\Common\Models\OrderBonusModel',
            self::ASSEMBLE_WAGES                    =>  'Yunshop\Assemble\Common\Models\OrderWagesModel',
            self::PERIOD_RETURN                     =>  'Yunshop\PeriodReturn\model\PeriodLog',
            self::TEAM_FJYX                         =>  'Yunshop\TeamFjyx\models\TeamFjyxModel',
            self::RECOMMENDER                       =>  'Yunshop\Recommender\models\RewardModel',
            self::SUPERIOR_REWARD                   =>  'Yunshop\SuperiorReward\models\OrderBuyModel',
            self::SELL_AWARD                        =>  'Yunshop\SellAward\model\AwardLog',
            self::EQUITY_REWARD                     =>  'Yunshop\EquityReward\models\EquityReward',
            self::STORE_CARD_INCOME                 =>  'Yunshop\StoreCard\Common\Models\CardIncomeModel',
            self::CIRCLE_VIDEO_BOUNS                =>  'Yunshop\Circle\common\model\CircleVideoBonus',
            self::CIRCLE_INVITATION_REWARD          =>  'Yunshop\Circle\common\model\CircleReward',
            self::CIRCLE_ADD                        =>  'Yunshop\Circle\common\model\CirclePayLog',
            self::CONSUME_REWARD                    =>  'Yunshop\ConsumeReward\models\RewardLog',
            self::AGENCY_REWARD                     =>  'Yunshop\Agency\models\AgencyModel',
            self::RANKING_AWARD                     => 'Yunshop\CommissionRanking\models\CommissionOrder',
            self::RESERVE_FUND                      =>  'Yunshop\ReserveFund\models\ReserveFundBonusModel',
            self::ROOM_CODE                         =>  'Yunshop\Room\models\CodeUsed',
            self::SCHOOL_COMPANY                    =>  'Yunshop\SchoolCompany\models\IncomeModel',
            self::LIVE_INSTALL                      =>  'Yunshop\LiveInstall\models\WorkerReward',
            self::INVEST_PEOPLE                     =>  'Yunshop\InvestPeople\models\Dividend',
            self::NEW_RETAIL_REWARD                 =>  'Yunshop\NewRetail\models\RewardModel',
            self::NEW_RETAIL_RIGHT_REWARD           =>  'Yunshop\NewRetail\models\StockRightReward',
            self::STORE_BUSINESS_ALLIANCE_RECOMMEND =>  'Yunshop\StoreBusinessAlliance\models\RecommendAward',
            self::STORE_BUSINESS_ALLIANCE_BUSINESS  =>  'Yunshop\StoreBusinessAlliance\models\BusinessAward',
            self::STORE_BUSINESS_ALLIANCE_SERVICE   =>  'Yunshop\StoreBusinessAlliance\models\ServiceAward',
            self::STORE_BUSINESS_ALLIANCE_OPERATION =>  'Yunshop\StoreBusinessAlliance\models\OperationAward',
            self::STORE_BUSINESS_ALLIANCE_PRICE     =>  'Yunshop\StoreBusinessAlliance\models\PriceDifferenceAward',
            self::STORE_BUSINESS_ALLIANCE_STORE     =>  'Yunshop\StoreBusinessAlliance\models\StoreAward',
            self::STORE_BUSINESS_ALLIANCE_SUPPORT   =>  'Yunshop\StoreBusinessAlliance\models\SupportAward',
            self::STORE_BUSINESS_ALLIANCE_TASK      =>  'Yunshop\StoreBusinessAlliance\models\TaskAward',
            self::STORE_BUSINESS_ALLIANCE_TEAM      =>  'Yunshop\StoreBusinessAlliance\models\TeamAward',
            self::PLUGIN_PARENT_PAYMENT_COMMISSION  =>  'Yunshop\ParentPayment\common\models\BehalfOrderModel',
	        self::STORE_REWARDS                     =>  'Yunshop\StoreRewards\common\models\StoreRewardsRecord',
            self::STORE_PROJECTS_ORDER              =>  'Yunshop\StoreProjects\common\models\ProjectsOrderService',
	        self::ZHP_REWARD                        =>  'Yunshop\ZhpGroupLottery\models\ZhpRewardLogModel',
	        self::STORE_BALANCE_AWARD               =>  'Yunshop\StoreBalance\model\BalanceAward',
            self::COMMISSION_EXTRA_BONUS            =>  'Yunshop\CommissionExtra\models\CommissionExtraBonusModel',
        ];
    }
}