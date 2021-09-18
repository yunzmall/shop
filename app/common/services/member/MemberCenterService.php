<?php
/**
 * Created by PhpStorm.
 * User: win 10
 * Date: 2019/7/16
 * Time: 14:41
 */

namespace app\common\services\member;

use app\common\facades\Setting;
use app\common\models\MemberShopInfo;
use app\common\services\popularize\PortType;
use Yunshop\AggregationCps\models\EquityGoodsSkuModel;
use Yunshop\AlipayOnekeyLogin\models\MemberAlipay;
use Yunshop\AlipayOnekeyLogin\services\SynchronousUserInfo;
use Yunshop\Assemble\Common\Services\EntryService;
use Yunshop\Channel\model\StockLog;
use Yunshop\CommissionActivity\common\services\CommonService;
use Yunshop\Auction\models\AuctioneerModel;
use Yunshop\CommissionStatistics\common\model\Agents;
use Yunshop\Designer\models\ViewSet;
use Yunshop\Kingtimes\common\models\Distributor;
use Yunshop\Kingtimes\common\models\Provider;
use Yunshop\OwnerOrderImport\common\services\OwnerService;
use Yunshop\PackageDeliver\model\Deliver;


class MemberCenterService
{
    public function getMemberData($memberId)
    {
        $filter = [
            'conference',
            //'store-cashier',
            'recharge-code'
        ];

        $diyarr = [
            'tool' => [
                'separate', 'elive', 'member_code', 'member_pay_code', 'signIndex', 'converge_pay',
                'room-aide', 'contractListPlu', 'findpwd', 'instation-message', 'applicationMarket', 'marketSub', 'lower-footprint', 'kefu',
                'flight-collect', 'yee-pay', 'group-develop-user', 'signContractLock',
            ],

            'asset_equity' => ['integral', 'credit', 'asset', 'love', 'coin', 'froze', 'extension', 'dragon_deposit'],
            'merchant'     => [
                'supplier', 'kingtimes', 'hotel', 'store-cashier', 'cashier', 'micro', 'delivery_station',
                'service_station', 'voice-goods', 'staging_buy_car', 'package_deliver', 'appointment',
                'room-apply', 'subsidiary', 'auction', 'promotion-assistant', 'activity-apply', 'supply-demand', 'cloud_warehouse',
                'skin-check', 'StoreVerification', 'ad-serving'
            ],
            'market'       => [
                'ranking', 'article', 'clock_in', 'conference', 'video_demand', 'enter_goods',
                'universal_card', 'recharge_code', 'my-friend', 'business_card', 'net_car', 'pickUpCardOrderList',
                'fight_groups', 'material-center', 'help-center', 'sign', 'courier',
                'declaration', 'distribution-order', 'video-share', 'pending_order',
                'channel', 'commission-activity', 'elite-award', 'team-sales', 'coupon-qr',
                'micro-communities', 'bonus-pool', 'advert-market', 'room', 'easy-refuel',

                'energy_cabin', 'energy_cabin_dealer', 'questionnaire', 'group-code', 'health-assessment', 'group-code',
                'love-ranking', 'assemble', 'worker_apply', 'exchange', 'agent-enquiry', 'together-purchase', 'TeamDistributions', 'sales-report', 'snatch-regiment',
                'oil_station', 'school-company', 'store-community', 'journal', 'live_install', 'live_install_worker', 'kart',
                'case-library', 'star-spell', 'agent_qa', 'store-card', 'luck-draw', 'aggregation-cps', 'agency', 'upgrade-code', 'customer-development', 'commission-ranking', 'blind-box',
                'room-code', 'circle', 'new-retail', 'store-business-alliance', 'my-snatch-regiment', 'consumer-reward', 'credit-inventory', 'free-lottery', 'owner-order-import',
                'zhp-group-lottery', 'full-return', 'new-media-advertising', 'group-work',
            ]
        ];

        //默认
        $data = [
            [
                'name'     => 'findpwd',
                'title'    => '忘记密码',
                'class'    => 'icon-fontclass-wangjimima2',
                'url'      => 'findpwd',
                'image'    => 'member_a(124).png',
                'mini_url' => '/packageE/findpwd/findpwd',
            ]
        ];


        collect(app('plugins')->getPlugins())->filter(function ($item) use ($filter) {

            if (1 == $item->isEnabled()) {
                $info = $item->toArray();

                if (in_array($info['name'], $filter)) {
                    return $item;
                }
            }
        })->each(function ($item) use (&$data) {
            $info = $item->toArray();

            $name = $info['name'];
            //todo 门店暂时不传

            if ($info['name'] == "store-cashier") {
                $name = 'store_cashier';
            } elseif ($info['name'] == 'recharge-code') {
                $name = 'recharge_code';
                $class = 'icon-member-recharge1';
                $url = 'rechargeCode';
                $image = 'member_a(3).png';
                $mini_url = '/packageA/member/rechargeCode/rechargeCode';
            } elseif ($info['name'] == 'conference') {
                $name = 'conference';
                $class = 'icon-member-act-signup1';
                $url = 'conferenceList';
                $image = 'member_a(15).png';
                $mini_url = '/packageA/member/conferenceList/conferenceList';
            }

            $data[] = [
                'name'     => $name,
                'title'    => $info['title'],
                'class'    => $class,
                'url'      => $url,
                'image'    => $image,
                'mini_url' => $mini_url
            ];
        });
        if (app('plugins')->isEnabled('asset') && (new \Yunshop\Asset\Common\Services\IncomeDigitizationService)->memberPermission()) {
            $data[] = [
                'name'     => 'asset',
                'title'    => PLUGIN_ASSET_NAME,
                'class'    => 'icon-number_assets',
                'url'      => 'TransHome',
                'image'    => 'member_a(69).png',
                'mini_url' => '',
            ];
        }

        if (\Setting::getByGroup('coupon')['exchange_center'] == 1) {
            $data[] = [
                'name'     => 'exchange',
                'title'    => '兑换中心',
                'class'    => 'icon-member_changer_centre',
                'url'      => 'CouponExchange',
                'image'    => 'member_a(74).png',
                'mini_url' => '/packageC/CouponExchange/index',
            ];
        }

        if (app('plugins')->isEnabled('sales-report')) {
            $sales_report_set = array_pluck(Setting::getAllByGroup('sales-report')->toArray(), 'value', 'key');
            if ($sales_report_set['is_open'] == 1 && app('plugins')->isEnabled('team-dividend') && app('plugins')->isEnabled('shareholder-dividend')) {
                $data[] = [
                    'name'     => 'sales-report',
                    'title'    => $sales_report_set['plugin_name'] ?: "销售报表",
                    'class'    => 'icon-fontclass-xiaoshoubaobiao',
                    'url'      => 'salesTable',
                    'image'    => 'member_a(128).png',
                    'mini_url' => '',
                ];
            }
        }


        if (PortType::popularizeShow(\YunShop::request()->type)) {
            $data[] = [
                'name'     => 'extension',
                'title'    => '推广中心',
                'class'    => 'icon-member-extension1',
                'url'      => 'extension',
                'image'    => 'member_a(38).png',
                'mini_url' => '/pages/member/extension/extension',
            ];
        }

        if (app('plugins')->isEnabled('business-card')) {
            $is_open = Setting::get('business-card.is_open');
            if ($is_open == 1) {
                $data[] = [
                    'name'     => 'business_card',
                    'title'    => '名片',
                    'class'    => 'icon-member_card1',
                    'url'      => 'CardCenter',
                    'image'    => 'member_a(58).png',
                    'mini_url' => '/packageB/member/business_card/CardCenter/CardCenter',
                ];
            }
        }

        if (app('plugins')->isEnabled('customer-development')) {
            $is_open = Setting::get('customer-development.is_open');
            if ($is_open == 1) {
                $data[] = [
                    'name'     => 'customer-development',
                    'title'    => \Yunshop\CustomerDevelopment\Common\services\SetService::getPluginName(),
                    'class'    => 'icon-fontclass-tuoke',
                    'url'      => 'memberTokerCard',
                    'image'    => 'member_a(136).png',
                    'mini_url' => '/packageH/toker/memberTokerCard/memberTokerCard',
                ];
            }
        }

        if (app('plugins')->isEnabled('agent-enquiry')) {
            $is_open = Setting::get('agent-enquiry.is_open');
            if ($is_open == 1) {
                if (app('plugins')->isEnabled('area-dividend')) {
                    $data[] = [
                        'name'     => 'agent-enquiry',
                        'title'    => '区域代理查询',
                        'class'    => 'icon-member_area_search',
                        'url'      => 'regionalAgent',
                        'image'    => 'member_a(121).png',
                        'mini_url' => '',
                    ];
                }
            }
        }

        if (\YunShop::request()->type != 2 && app('plugins')->isEnabled('channel')) {
            $channelSetting = Setting::get('plugin.channel');
            if ($channelSetting['is_open_channel'] == 1) {
                $pluginName = $channelSetting['plugin_name'] ?: '代理商';
                // 订单未读数量
                $readCount = StockLog::select('id')
                    ->where('parent_uid', $memberId)
                    ->where('is_read', 1)
                    ->count();

                $data[] = [
                    'name'     => 'channel',
                    'title'    => $pluginName,
                    'class'    => 'icon-member_distributors',
                    'url'      => 'distributorIndex',
                    'image'    => 'member_a(94).png',
                    'mini_url' => '',
                    'total'    => $readCount
                ];
            }
        }

        if (app('plugins')->isEnabled('commission-activity')) {
            $name = \Yunshop\CommissionActivity\common\services\CommonService::getName();
            $data[] = [
                'name'     => 'commission-activity',
                'title'    => $name,
                'class'    => 'icon-member_active',
                'url'      => 'DistributionIndex',
                'image'    => 'member_a(100).png',
                'mini_url' => '/packageB/member/distribution_queue/DistributionIndex/DistributionIndex',
            ];
            $data[] = [
                'name'     => 'commission-activity',
                'title'    => $name . '查询',
                'class'    => 'icon-member_active_search',
                'url'      => 'DistributionStatistics',
                'image'    => 'member_a(99).png',
                'mini_url' => '/packageB/member/distribution_queue/DistributionStatistics/DistributionStatistics',
            ];
        }

        if (app('plugins')->isEnabled('team-sales')) {
            $name = \Yunshop\TeamSales\common\services\CommonService::getSet()['name'];
            $data[] = [
                'name'     => 'team-sales',
                'title'    => $name,
                'class'    => 'icon-member_team_salereturn',
                'url'      => 'teamSell',
                'image'    => 'member_a(87).png',
                'mini_url' => '',
            ];
        }

        if (app('plugins')->isEnabled('declaration')) {
            if (Setting::get('plugin.declaration.switch')) {
                $data[] = [
                    'name'     => 'declaration',
                    'title'    => DECLARATION_NAME,
                    'class'    => 'icon-declaration_system',
                    'url'      => 'DeclarationApply',
                    'image'    => 'member_a (66).png',
                    'mini_url' => '',
                ];
            }
        }


        //配送站
        if (app('plugins')->isEnabled('delivery-station')) {
            $delivery_station_setting = Setting::get('plugin.delivery_station');
            $delivery_station = \Yunshop\DeliveryStation\models\DeliveryStation::memberId($memberId)->first();
            if ($delivery_station && $delivery_station_setting['is_open']) {
                $data[] = [
                    'name'     => 'delivery_station',
                    'title'    => '配送站',
                    'class'    => 'icon-delivery_order',
                    'url'      => 'deliveryStation',
                    'mini_url' => '',
                ];
            }
        }
        //服务站
        if (app('plugins')->isEnabled('service-station')) {
            $service_station = \Yunshop\ServiceStation\models\ServiceStation::isBlack()->memberId($memberId)->first();
            if ($service_station) {
                $data[] = [
                    'name'     => 'service_station',
                    'title'    => '服务站',
                    'class'    => 'icon-service_station',
                    'url'      => 'serviceStation',
                    'mini_url' => '',
                ];
            }
        }

        if (app('plugins')->isEnabled('material-center')) {
            $data[] = [
                'name'     => 'material-center',
                'title'    => '素材中心',
                'class'    => 'icon-member_material',
                'url'      => 'materialCenter',
                'image'    => 'member_a(65).png',
                'mini_url' => '/packageA/others/materialCenter/materialCenter',
            ];
        }


        if (app('plugins')->isEnabled('distribution-order')) {
            $disorder_setting = Setting::get('plugins.distribution-order');
            if ($disorder_setting && 1 == $disorder_setting['is_open']) {
                $data[] = [
                    'name'     => 'distribution-order',
                    'title'    => $disorder_setting['title'] ?: '分销订单统计',
                    'class'    => 'icon-order_system',
                    'url'      => 'DistributionOrders',
                    'image'    => 'member_a(70).png',
                    'mini_url' => '/packageA/member/DistributionOrders/DistributionOrders',
                ];
            }
        }

        if (app('plugins')->isEnabled('credit')) {
            $credit_setting = Setting::get('plugin.credit');
            if ($credit_setting && 1 == $credit_setting['is_credit']) {
                $data[] = [
                    'name'     => 'credit',
                    'title'    => '信用值',
                    'class'    => 'icon-member-credit01',
                    'url'      => 'creditInfo',
                    'image'    => 'member_a(44).png',
                    'mini_url' => '/packageB/member/creditInfo/creditInfo',
                ];
            }
        }
        if (app('plugins')->isEnabled('ranking')) {
            $ranking_setting = Setting::get('plugin.ranking');
            if ($ranking_setting && 1 == $ranking_setting['is_ranking']) {
                $data[] = [
                    'name'     => 'ranking',
                    'title'    => '排行榜',
                    'class'    => 'icon-member-bank-list1',
                    'url'      => 'rankingIndex',
                    'image'    => 'member_a(29).png',
                    'mini_url' => '/packageE/Rankings/Rankings',
                ];
            }
        }

        if (app('plugins')->isEnabled('micro')) {
            $micro_set = \Setting::get('plugin.micro');
            if ($micro_set['is_open_miceo'] == 1) {
                $micro_shop = \Yunshop\Micro\common\models\MicroShop::getMicroShopByMemberId($memberId);
                if ($micro_shop) {
                    $data[] = [
                        'name'     => 'micro',
                        'title'    => MICRO_PLUGIN_NAME . '中心',
                        'class'    => 'icon-member-mendian1',
                        'url'      => 'microShop_home',
                        'image'    => 'member_a(40).png',
                        'mini_url' => '/packageC/microshop/microShop_home/microShop_home',
                    ];
                } else {
                    $data[] = [
                        'name'     => 'micro',
                        'title'    => '我要开店',
                        'class'    => 'icon-member-mendian1',
                        'url'      => 'microShop_apply',
                        'image'    => 'member_a(40).png',
                        'mini_url' => '/packageC/microshop/microShop_apply/microShop_apply',
                    ];
                }
            }
        }

        if (app('plugins')->isEnabled('help-center')) {
            $status = \Setting::get('help-center.status') ? true : false;
            if ($status) {
                $data[] = [
                    'name'     => 'help-center',
                    'title'    => '帮助中心',
                    'class'    => 'icon-member-help',
                    'url'      => 'helpcenter',
                    'image'    => 'member_a(2).png',
                    'mini_url' => '/packageB/member/helpcenter/helpcenter',
                ];
            }
        }

        if (app('plugins')->isEnabled('love')) {
            $data[] = [
                'name'     => 'love',
                'title'    => \Yunshop\Love\Common\Services\SetService::getLoveName() ?: '爱心值',
                'class'    => 'icon-member-exchange1',
                'url'      => 'love_index',
                'image'    => 'member_a(1).png',
                'mini_url' => '/packageB/member/love/Love_index/Love_index',
            ];
        }
        if (app('plugins')->isEnabled('store-card')) {
            $data[] = [
                'name'     => 'store-card',
                'title'    => '门店消费卡',
                'class'    => 'icon-fontclass-mendianxaiofeika1',
                'url'      => 'storeConsumeIndex',
                'image'    => 'member_a(137).png',
                'mini_url' => '/packageH/o2o/storeConsumeIncme/storeConsumeIncme',
            ];
        }
        if (app('plugins')->isEnabled('assemble')) {
            $data[] = [
                'name'     => 'assemble',
                'title'    => Setting::get('plugin.assemble.assemble_name') ?: '安装服务',
                'class'    => 'icon-member_installserver',
                'url'      => 'userOrderlist',
                'image'    => 'member_a(114).png',
                'mini_url' => '',
            ];
            //如果会员是安装师傅、或者区域代理显示安装师傅，否则显示安装师傅申请
            if ((new EntryService())->isShow()) {
                $data[] = [
                    'name'     => 'worker_apply',
                    'title'    => Setting::get('plugin.assemble.assemble_worker_name') ?: '安装师傅',
                    'class'    => 'icon-member_installserverapply',
                    'url'      => 'workerOrderlist',
                    'image'    => 'member_a(113).png',
                    'mini_url' => '',
                ];
            } else {
                $workerName = Setting::get('plugin.assemble.assemble_worker_name');
                $data[] = [
                    'name'     => 'worker_apply',
                    'title'    => $workerName ? $workerName . '申请' : '安装师傅申请',
                    'class'    => 'icon-member_installserverapply',
                    'url'      => 'workerApply',
                    'image'    => 'member_a(113).png',
                    'mini_url' => '',
                ];
            }
        }
        if (app('plugins')->isEnabled('owner-order-import')) {
            $ownerService = new OwnerService();
            if ($ownerService->isShow()) {
                if ($ownerService->isRegister()) {
                    $data[] = [
                        'name'     => 'owner-order-import',
                        'title'    => '跨境店主',
                        'class'    => 'icon-fontclass-kuajing',
                        'url'      => 'orderImportIndex',
                        'image'    => 'member_a(167).png',
                        'mini_url' => '/packageH/orderImport/orderImportIndex/orderImportIndex',
                    ];
                } else {
                    $data[] = [
                        'name'     => 'owner-order-import',
                        'title'    => '跨境店主申请',
                        'class'    => 'icon-fontclass-dianzhushenqing',
                        'url'      => 'orderImportApply',
                        'image'    => 'member_a(166).png',
                        'mini_url' => '/packageH/orderImport/orderImportApply/orderImportApply',
                    ];
                }
            }
        }

        if (app('plugins')->isEnabled('love-ranking')) {
            if (app('plugins')->isEnabled('love')) {
                $title = \Yunshop\Love\Common\Services\SetService::getLoveName() ?: '爱心值';
                $data[] = [
                    'name'     => 'love-ranking',
                    'title'    => $title . '排行榜',
                    'class'    => ' icon-member_reward_ranking',
                    'url'      => 'loveRanking',
                    'image'    => 'member_a(116).png  ',
                    'mini_url' => '/packageE/loveRanking/loveRanking',
                ];
            }

        }

        if (app('plugins')->isEnabled('froze')) {
            $data[] = [
                'name'     => 'froze',
                'title'    => \Yunshop\Froze\Common\Services\SetService::getFrozeName() ?: '冻结币',
                'class'    => 'icon-member-frozen1',
                'url'      => 'FrozenCoin',
                'image'    => 'member_a(7).png',
                'mini_url' => '/packageB/member/FrozenCoin/FrozenCoin',
            ];
        }

        if (app('plugins')->isEnabled('coin')) {
            $data[] = [
                'name'     => 'coin',
                'title'    => \Yunshop\Coin\Common\Services\SetService::getCoinName() ?: '华侨币',
                'class'    => 'icon-member-currency1',
                'url'      => 'overseas_index',
                'image'    => 'member_a(14).png',
                'mini_url' => '/packageB/member/overseas_index/overseas_index',
            ];
        }

        if (app('plugins')->isEnabled('elive')) {
            $data[] = [
                'name'     => 'elive',
                'title'    => '生活缴费',
                'class'    => 'icon-shenghuojiaofei',
                'url'      => 'lifeService',
                'image'    => 'member_a(49).png',
                'mini_url' => '',
            ];
        }


        if (app('plugins')->isEnabled('sign')) {
            $data[] = [
                'name'     => 'sign',
                'title'    => trans('Yunshop\Sign::sign.plugin_name') ?: '签到',
                'class'    => 'icon-member-clock1',
                'url'      => 'sign',
                'image'    => 'member_a(30).png',
                'mini_url' => '/packageA/member/sign/sign',
            ];
        }

        if (app('plugins')->isEnabled('courier')) {
            //快递单
            $status = \Setting::get('courier.courier.radio');
            if ($status) {
                $data[] = [
                    'name'     => 'courier',
                    'title'    => '快递',
                    'image'    => 'member_a(68).png',
                    'mini_url' => '/packageA/member/CourseManage/CourseManage',
                ];
            }
        }

        if (app('plugins')->isEnabled('voice-goods')) {
            $set = \Setting::get('plugin.voice-goods');
            if ($set['is_open_voice'] == 1) {
                $data[] = [
                    'name'     => 'voice-goods',
                    'title'    => $set['voice_name'],
                    'class'    => 'icon-member_voice_center',
                    'url'      => 'MyVoices',
                    'image'    => 'member_a(75).png',
                    'mini_url' => '/packageC/voice_shop/my_voices/my_voices',
                ];
            }
        }

        if (app('plugins')->isEnabled('my-friend')) {
            $data[] = [
                'name'     => 'my-friend',
                'title'    => MY_FRIEND_NAME,
                'class'    => 'icon-member_my-friend',
                'url'      => 'MyFriendApply',
                'image'    => 'member_a(63).png',
                'mini_url' => '/packageD/MyFriendApply/myfriends',
            ];
        }

        if (app('plugins')->isEnabled('article')) {
            $article_setting = Setting::get('plugin.article');

            if ($article_setting) {
                $data[] = [
                    'name'     => 'article',
                    'title'    => $article_setting['center'] ?: '文章中心',
                    'class'    => 'icon-member-collect1',
                    'url'      => 'notice',
                    'param'    => 0,
                    'image'    => 'member_a(41).png',
                    'mini_url' => '/packageA/member/notice/notice',
                ];
            }
        }

        if (app('plugins')->isEnabled('clock-in')) {
            $clockInService = new \Yunshop\ClockIn\services\ClockInService();
            $pluginName = $clockInService->get('plugin_name');

            $clock_in_setting = Setting::get('plugin.clock_in');

            if ($clock_in_setting && 1 == $clock_in_setting['is_clock_in']) {
                $data[] = [
                    'name'     => 'clock_in',
                    'title'    => $pluginName,
                    'class'    => 'icon-member-get-up',
                    'url'      => 'ClockPunch',
                    'image'    => 'member_a(47).png',
                    'mini_url' => '/packageA/member/ClockPunch/ClockPunch',
                ];
            }
        }

        if (app('plugins')->isEnabled('video-demand')) {

            $video_demand_setting = Setting::get('plugin.video_demand');

            if ($video_demand_setting && $video_demand_setting['is_video_demand']) {
                $data[] = [
                    'name'     => 'video_demand',
                    'title'    => '课程中心',
                    'class'    => 'icon-member-course3',
                    'url'      => 'CourseManage',
                    'image'    => 'member_a(22).png',
                    'mini_url' => '/packageA/member/CourseManage/CourseManage',
                ];
            }
        }

        if (app('plugins')->isEnabled('help-center')) {

            $help_center_setting = Setting::get('plugin.help_center');

            if ($help_center_setting && 1 == $help_center_setting['status']) {
                $data[] = [
                    'name'     => 'help_center',
                    'title'    => '帮助中心',
                    'class'    => 'icon-member-help',
                    'url'      => 'helpcenter',
                    'mini_url' => '/packageB/member/helpcenter/helpcenter',
                ];
            }
        }

        if (app('plugins')->isEnabled('store-cashier')) {
            //商米D2使用的
            $data[] = [
                'name'     => 'member_code',
                'title'    => '会员卡号 ',
                'class'    => 'icon-member_posvip_cardnum',
                'url'      => 'uidCode',
                'image'    => 'member_a(83).png',
                'mini_url' => '',
            ];
            $data[] = [
                'name'     => 'member_pay_code',
                'title'    => '动态验证码',
                'class'    => 'icon-member_pospay_validation',
                'url'      => 'codePage',
                'image'    => 'member_a(82).png',
                'mini_url' => '',
            ];
            $store = \Yunshop\StoreCashier\common\models\Store::getStoreByUid($memberId)->first();

            if (!$store) {
                $data[] = [
                    'name'     => 'store-cashier',
                    'title'    => '门店申请',
                    'class'    => 'icon-member-store-apply1',
                    'url'      => 'storeApply',
                    'image'    => 'member_a(26).png',
                    'mini_url' => '/packageA/member/storeApply/storeApply',
                ];
            }

            if ($store && $store->is_black != 1) {
                $data[] = [
                    'name'     => 'store-cashier',
                    'title'    => '门店管理',
                    'class'    => 'icon-member_store',
                    'url'      => 'storeManage',
                    'image'    => 'member_a(26).png',
                    'mini_url' => '/packageC/o2o/storeManage/storeManage',
                ];

                if ($store->hasOneCashier->hasOneCashierGoods->is_open == 1) {
                    $data[] = [
                        'name'     => 'cashier',
                        'title'    => '收银台',
                        'class'    => 'icon-member-cashier',
                        'url'      => 'cashier',
                        'api'      => 'plugin.store-cashier.frontend.cashier.center.index',
                        'image'    => 'member_a(34).png',
                        'mini_url' => '/packageC/cashier/cashier/cashier',
                    ];
                }
            }


        }
        if (app('plugins')->isEnabled('supplier')) {
            $supplier_setting = Setting::get('plugin.supplier');
            $supplier = \Yunshop\Supplier\common\models\Supplier::getSupplierByMemberId($memberId, 1);

            if (!$supplier) {
                $data[] = [
                    'name'     => 'supplier',
                    'title'    => '供应商申请',
                    'class'    => 'icon-member-apply1',
                    'url'      => 'supplier',
                    'api'      => 'plugin.supplier.supplier.controllers.apply.supplier-apply.apply',
                    'image'    => 'member_a(53).png',
                    'mini_url' => '/packageA/member/supplier/supplier',
                ];
            } elseif ($supplier_setting && 1 == $supplier_setting['status']) {
                $data[] = [
                    'name'     => 'supplier',
                    'title'    => $supplier_setting['name'] ?: '供应商管理',
                    'class'    => 'icon-member-supplier',
                    'url'      => 'SupplierCenter',
                    'image'    => 'member_a(53).png',
                    'mini_url' => '/packageA/member/SupplierCenter/SupplierCenter',
                ];
            }
        }
        if (app('plugins')->isEnabled('kingtimes')) {
            $provider = Provider::select(['id', 'uid', 'status'])->where('uid',
                $memberId)->first();
            $distributor = Distributor::select(['id', 'uid', 'status'])->where('uid',
                $memberId)->first();

            if ($provider) {

                if ($provider->status == 1) {
                    $data[] = [
                        'name'     => 'kingtimes',
                        'title'    => '补货商中心',
                        'class'    => 'icon-member-replenishment',
                        'url'      => 'ReplenishmentApply',
                        'image'    => 'member_a(67).png',
                        'mini_url' => '',
                    ];
                }
            } else {
                $data[] = [
                    'name'     => 'kingtimes',
                    'title'    => '补货商申请',
                    'class'    => 'icon-member-replenishment',
                    'url'      => 'ReplenishmentApply',
                    'image'    => 'member_a(67).png',
                    'mini_url' => '',
                ];
            }
            if ($distributor) {
                if ($distributor->status == 1) {
                    $data[] = [
                        'name'     => 'kingtimes',
                        'title'    => '配送站中心',
                        'class'    => 'icon-member-express-list',
                        'url'      => 'DeliveryTerminalApply',
                        'image'    => 'member_a(54).png',
                        'mini_url' => '',
                    ];
                }
            } else {
                $data[] = [
                    'name'     => 'kingtimes',
                    'title'    => '配送站申请',
                    'class'    => 'icon-member-express-list',
                    'url'      => 'DeliveryTerminalApply',
                    'image'    => 'member_a(54).png',
                    'mini_url' => '',
                ];
            }
            // dd($data);

        }
        if (app('plugins')->isEnabled('enter-goods')) {

            $data[] = [
                'name'     => 'enter_goods',
                'title'    => '用户入驻',
                'class'    => 'icon-member_goods',
                'url'      => 'EnterShop',
                'image'    => 'member_a(52).png',
                'mini_url' => '',
            ];
        }

        if (app('plugins')->isEnabled('integral')) {
            $status = \Yunshop\Integral\Common\Services\SetService::getIntegralSet();

            if ($status['member_show']) {
                $data[] = [
                    'name'     => 'integral',
                    'title'    => $status['plugin_name'] ?: '消费积分',
                    'class'    => 'icon-member_integral',
                    'url'      => 'Integral_love',
                    'image'    => 'member_a(55).png',
                    'mini_url' => '/packageB/member/Integral_love/Integral_love',
                ];
            }
        }

        //直播
        if (app('plugins')->isEnabled('room')) {
            $set = \Setting::get('plugin.room_set_basic');

            if ($set && $set['is_open_room'] == 1) {
                $data[] = [
                    'name'     => 'room',
                    'title'    => $set['plugin_name'] ?: '直播',
                    'class'    => 'icon-member_live_normal',
                    'url'      => 'liveList',
                    'image'    => 'member_a(92).png',
                    'mini_url' => '/packageD/directSeeding/liveList/liveList',
                ];
            }
            $anchor = \Yunshop\Room\models\Anchor::getAnchorByMemberId($memberId)->first();
            if (!$anchor) {
                $data[] = [
                    'name'     => 'room-apply',
                    'title'    => '主播申请',
                    'class'    => 'icon-member_host_application',
                    'url'      => 'anchorApply',
                    'image'    => 'member_a(91).png',
                    'mini_url' => '/packageD/directSeeding/anchorApply/anchorApply',
                ];
            }
            if ($anchor || empty($memberId)) {
                $data[] = [
                    'name'     => 'room-aide',
                    'title'    => '主播助手',
                    'class'    => 'icon-member_vj_asistant',
                    'url'      => 'helperLiveList',
                    'image'    => 'member_a(102).png',
                    'mini_url' => '/packageD/directSeeding/helperLiveList/helperLiveList',
                ];
            }

            $code_set = \Setting::get('plugin.room_code_basic');

            if ($code_set['open_code'] && $code_set['front_show']) {
                $data[] = [
                    'name'     => 'room-code',
                    'title'    => '直播授权码',
                    'class'    => 'icon-fontclass-shouquanma-01',
                    'url'      => 'liveAuthorization',
                    'image'    => 'member_a(148).png',
                    'mini_url' => '/packageH/liveAuthorization/liveAuthorization/liveAuthorization',
                ];
            }

        }

        if (app('plugins')->isEnabled('universal-card')) {
            $set = \Yunshop\UniversalCard\services\CommonService::getSet();
            //判断插件开关
            if ($set['switch']) {
                $shopSet = \Setting::get('shop.member');
                //判断商城升级条件是否为指定商品
                if ($shopSet['level_type'] == 2) {
                    $data[] = [
                        'name'     => 'universal_card',
                        'title'    => $set['name'],
                        'class'    => 'icon-card',
                        'url'      => 'CardIndex',
                        'image'    => 'member_a(57).png',
                        'mini_url' => '/packageB/member/CardIndex/CardIndex',
                    ];
                }
            }
        }

        if (app('plugins')->isEnabled('separate')) {
            $setting = \Setting::get('plugin.separate');
            if ($setting && 1 == $setting['separate_status']) {
                $data[] = [
                    'name'     => 'separate',
                    'title'    => '绑定银行卡',
                    'class'    => 'icon-yinhangqia',
                    'url'      => 'BankCard',
                    'image'    => 'tool_a(10).png',
                    'mini_url' => '',
                ];
            }
        }

        if (app('plugins')->isEnabled('upgrade-code') && app('plugins')->isEnabled('team-dividend')) {

            $code_open = Setting::get('upgrade-code.is_open');
            if ($code_open == 1) {
                $data[] = [
                    'name'     => 'upgrade-code',
                    'title'    => '升级码',
                    'class'    => 'icon-fontclass-shengjima',
                    'url'      => 'MemberUpgradeCode',
                    'image'    => 'member_a(141).png',
                    'mini_url' => '/packageH/member/upgrade_code/index/index',
                ];
            }
        }

        if (app('plugins')->isEnabled('credit-inventory')) {
            $credit_open = Setting::get('credit-inventory.is_open');
            if ($credit_open == 1) {
                $data[] = [
                    'name'     => 'credit-inventory',
                    'title'    => '信用值',
                    'class'    => 'icon-fontclass-xinyongzhi',
                    'url'      => 'creditValeuIndex',
                    'image'    => 'member_a(159).png',
                    'mini_url' => '/packageH/credit_value/created_value_index/created_value_index',
                ];
            }
        }

        if (app('plugins')->isEnabled('hotel')) {
            $hotel = \Yunshop\Hotel\common\models\Hotel::getHotelByUid($memberId)->first();
            if ($hotel) {
                $data[] = [
                    'name'     => 'hotel',
                    'title'    => HOTEL_NAME . '管理',
                    'class'    => 'icon-member_hotel',
                    'url'      => 'HotelManage',
                    'image'    => 'member_a(56).png',
                    'mini_url' => '/packageA/hotel/HotelManage/HotelManage',
                ];
            } else {
                $data[] = [
                    'name'     => 'hotel',
                    'title'    => HOTEL_NAME . '申请',
                    'class'    => 'icon-member-hotel-apply',
                    'url'      => 'hotelApply',
                    'image'    => 'member_a(56).png',
                    'mini_url' => '/packageA/member/storeApply/storeApply?name=hotelApply',
                ];
            }
            //酒店自定义字段
            $set = \Setting::get('plugin.hotel');
            $arr['hotel'] = [
                'hotel_home_page'    => $set['hotel_home_page'] ?: '酒店主页',
                'check_the_room'     => $set['check_the_room'] ?: '查看房型',
                'hotel_intro'        => $set['hotel_intro'] ?: '酒店简介',
                'goods_details'      => $set['goods_details'] ?: '商品详情',
                'goods_presentation' => $set['goods_presentation'] ?: '商品介绍',
                'goods_parameters'   => $set['goods_parameters'] ?: '商品参数',
                'user_evaluation'    => $set['user_evaluation'] ?: '用户评价',
                'hotels'             => $set['hotels'] ?: '酒店',
                'hotel_first_page'   => $set['hotel_first_page'] ?: '酒店首页',
                'hotel_find'         => $set['hotel_find'] ?: '查找酒店',
                'hotel_find_name'    => $set['hotel_find_name'] ?: '酒店名'
            ];
        }

        //网约车插件开启关闭
        if (app('plugins')->isEnabled('net-car')) {

            $video_demand_setting = Setting::get('plugin.net_car');

            if ($video_demand_setting && $video_demand_setting['net_car_open']) {
                $data[] = [
                    'name'     => 'net_car',
                    'title'    => '网约车',
                    'class'    => 'icon-member_my-card',
                    'url'      => 'online_car',
                    'image'    => 'member_a(64).png',
                    'mini_url' => '',
                ];
            }
        }

        //拼团插件开启关闭
        if (app('plugins')->isEnabled('fight-groups')) {
            $data[] = [
                'name'     => 'fight_groups',
                'title'    => '我的拼团',
                'class'    => 'icon-member_mygroup',
                'url'      => 'MyGroups',
                'image'    => 'member_a(72).png',
                'mini_url' => '/packageB/member/group/MyGroups/MyGroups',
            ];
        }


        //自提点
        if (app('plugins')->isEnabled('package-deliver')) {
            $is_package = \Setting::get('plugin.package_deliver.is_package');
            $community_group = \Setting::get('plugin.package_deliver.community_group');
            if ($is_package) {
                $deliver = Deliver::uniacid()->where('uid', $memberId)->first();
                if (!$deliver) {
                    $data[] = [
                        'name'     => 'package_deliver',
                        'title'    => \Yunshop\PackageDeliver\service\CustomizeNameService::getName() . '申请',
                        'class'    => 'icon-member_place_apply',
                        'url'      => 'SelfCarryApply',
                        'image'    => 'member_a(77).png',
                        'mini_url' => '/packageC/selfCarry/selfCarryApply/selfCarryApply',
                    ];
                } // else if ($community_group)
                else {
                    $data[] = [
                        'name'     => 'package_deliver',
                        'title'    => \Yunshop\PackageDeliver\service\CustomizeNameService::getName(),
                        'class'    => 'icon-fontclass-zitidian',
                        'url'      => 'communityBuyIndex',
                        'image'    => 'member_a(130).png',
                        'mini_url' => '/packageE/community_buying/index/index',
                    ];
                }

            }
        }

        //发现视频
        if (app('plugins')->isEnabled('video-share')) {
            $set = \Setting::get('plugin.video-share');
            if ($set['is_open']) {
                $data[] = [
                    'name'     => 'video-share',
                    'title'    => '发现视频',
                    'class'    => 'icon-extension_video',
                    'url'      => $set['list_style'] == 2 ? 'VideoDetail' : 'VideoList',
                    'image'    => 'member_a(73).png',
                    'mini_url' => $set['list_style'] == 2 ? '/packageC/video_goods/VideoDetail/VideoDetail' : '/packageC/video_goods/VideoList/VideoList',
                ];
            }
        }

        //微社区
        if (app('plugins')->isEnabled('micro-communities')) {
            $set = \Setting::get('plugin.micro-communities');
            if ($set && $set['is_open_micro'] == 1) {
                $data[] = [
                    'name'     => 'micro-communities',
                    'title'    => '微社区',
                    'class'    => 'icon-member_community',
                    'url'      => 'MicroHome',
                    'image'    => 'member_a(74).png',
                    'mini_url' => '/packageC/micro_communities/microIndex/microIndex',
                ];
            }
        }

        //商品挂单
        if (app('plugins')->isEnabled('pending-order')) {
            $set = \Setting::get('plugin.pending_order');
            if ($set['whether_open']) {
                $data[] = [
                    'name'     => 'pending_order',
                    'title'    => \Yunshop\PendingOrder\services\PendingOrderService::PendingOrderName(),
                    'class'    => 'icon-extension_goods_order',
                    'url'      => 'EntryVolume',
                    'image'    => 'member_a(59).png',
                    'mini_url' => '/packageC/entry_orders/entry_volume/entry_volume',
                ];
            }
        }


        //分期购车
        if (app('plugins')->isEnabled('staging-buy-car')) {
            $set = \Setting::get('plugin.staging_buy_car');
            if ($set['staging_buy_car_open'] != '1') {
                $data[] = ['name' => 'staging_buy_car', 'title' => '分期购车', 'class' => 'icon-member_installment_buycar', 'url' => 'hireCarManage', 'image' => 'member_a(80).png'];
            }
        }

        if (app('plugins')->isEnabled('advert-market')) {
            $data[] = [
                'name'     => 'advert-market',
                'title'    => '广告市场',
                'class'    => 'icon-member_advertise_market',
                'url'      => 'admarketHome',
                'image'    => 'member_a(84).png',
                'mini_url' => '',
            ];
        }

        //龙存管插件开启
        if (app('plugins')->isEnabled('dragon-deposit')) {

            $data[] = [
                'name'     => 'dragon_deposit',
                'title'    => '龙存管',
                'class'    => 'icon-member_construction_deposit',
                'url'      => 'myWallet',
                'image'    => 'member_a(76).png',
                'mini_url' => '',
            ];

        }

        if (app('plugins')->isEnabled('bonus-pool')) {

            $pluginName = Setting::get('plugin.bonus_pool')['bonus_pool_name'];

            $data[] = [
                'name'     => 'bonus-pool',
                'title'    => $pluginName ?: '奖金池',
                'class'    => 'icon-member_bonus_pools',
                'url'      => 'Bonus',
                'image'    => 'member_a(81).png',
                'mini_url' => '',
            ];

        }
        //预约
        if (app('plugins')->isEnabled('appointment')) {
            $data[] = [
                'name'     => 'appointment',
                'title'    => '预约',
                'class'    => 'icon-member_appointment',
                'url'      => 'Appointment',
                'image'    => 'member_a(85).png',
                'mini_url' => '/packageE/appointment/appointment_index',
            ];
        }

        //供需发布

        if (app('plugins')->isEnabled('supply-demand')) {
            $pluginName = Setting::get('plugin.supply-demand')['custom_name'];

            $data[] = [
                'name'     => 'supply-demand',
                'title'    => $pluginName ?: '供需信息发布',
                'class'    => 'icon-member_information_release',
                'url'      => 'publish_manage',
                'image'    => 'member_a(90).png',
                'mini_url' => '',
            ];
        }

        //汇聚支付插件
        if (app('plugins')->isEnabled('converge_pay')) {
            $data[] = [
                'name'     => 'converge_pay',
                'title'    => '汇聚支付',
                'class'    => 'icon-member_quickpay',
                'url'      => 'quickIndex',
                'image'    => 'member_a(95).png',
                'mini_url' => '/packageD/quickPay/quick_index/quick_index',
            ];
        }

        //电子合同
        if (app('plugins')->isEnabled('yun-sign')) {
            $data[] = [
                'name'     => 'signIndex',
                'title'    => '电子合同',
                'class'    => 'icon-member-cloud-object',
                'url'      => 'signIndex',
                'image'    => 'member_a(93).png',
                'mini_url' => '/packageH/signPage/signIndex/signIndex',
            ];
        }
        //商城电子合同
        if (app('plugins')->isEnabled('shop-esign')) {
            $data[] = [
                'name'     => 'contractListPlu',
                'title'    => '商城电子合同',
                'class'    => 'icon-member_mycontract',
                'url'      => 'signIndexPlu',
                'image'    => 'member_a(89).png',
                'mini_url' => '/packageH/signIndexPlu/signIndex/signIndex',
            ];
        }
        //电子签约
        if (app('plugins')->isEnabled('yun-contract-lock')) {
            $data[] = [
                'name'     => 'signContractLock',
                'title'    => '电子签约',
                'class'    => 'icon-member_mycontract',
                'url'      => 'signContractLock',
                'image'    => 'member_a(89).png',
                'mini_url' => '',
            ];
        }

        //拍卖
        if (app('plugins')->isEnabled('auction')) {
            if (\Setting::get('plugin.auction.is_open_auction') == 1) {

                $auction = AuctioneerModel::where('member_id', $memberId)->first();
                if (!$auction || $auction->type == 0 || $auction->type == 2) {
                    $data[] = [
                        'name'     => 'auction',
                        'title'    => '拍卖官申请',
                        'class'    => 'icon-member_auctionofficer_apply',
                        'url'      => 'auctionApply',
                        'image'    => 'member_a(96).png',
                        'mini_url' => '/packageD/auction/auction-apply/auction-apply/auction-apply',
                    ];
                }

                $data[] = [
                    'name'     => 'auction',
                    'title'    => '拍卖管理',
                    'class'    => 'icon-member_auctionofficer_admin',
                    'url'      => 'auctioneer',
                    'image'    => 'member_a(97).png',
                    'mini_url' => '/packageD/auction/auctioneer-manage/auctioneer/auctioneer',
                ];

                $data[] = [
                    'name'     => 'auction',
                    'title'    => '拍卖',
                    'class'    => 'icon-member_auctionofficer_index',
                    'url'      => 'auctionIndex',
                    'image'    => 'member_a(98).png',
                    'info'     => 'get',
                    'mini_url' => '/packageD/auction/auction/auction_index/auction_index',
                ];
            }
        }

        if (app('plugins')->isEnabled('together-purchase')) {
            $data[] = [
                'name'     => 'together-purchase',
                'title'    => '我的拼购',
                'class'    => 'icon-member_my_buy',
                'url'      => 'myGroupBuy',
                'image'    => 'member_a(119).png',
                'mini_url' => '/packageB/member/group_buy/my_group_buy/my_group_buy',
            ];

            $data[] = [
                'name'     => 'together-purchase',
                'title'    => '拼购列表',
                'class'    => 'icon-member_buygroup_orderlist',
                'url'      => 'GroupBuyList',
                'image'    => 'member_a(118).png',
                'mini_url' => '/packageB/member/group_buy/group_buy_list/group_buy_list',
                // 'info'  => 'get'
            ];
        }

        if (app('plugins')->isEnabled('snatch-regiment')) {
            $data[] = [
                'name'     => 'my-snatch-regiment',
                'title'    => '我的抢团',
                'class'    => 'icon-member_mygroupbuy',
                'url'      => 'grab_group_my',
                'image'    => 'member_a(122).png',
                'mini_url' => '/packageE/grab_group/grab_group_my/grab_group_my',
            ];

            $data[] = [
                'name'     => 'snatch-regiment',
                'title'    => '抢团',
                'class'    => 'icon-member_groupbuy',
                'url'      => 'grab_group_home',
                'image'    => 'member_a(123).png',
                'mini_url' => '/packageE/grab_group/grab_group_home/grab_group_home',
            ];
        }

        if (app('plugins')->isEnabled('star-spell')) {
            $data[] = [
                'name'     => 'star-spell',
                'title'    => '我的星拼乐',
                'class'    => 'icon-fontclass-xingpinle',
                'url'      => 'starMusicIndex',
                'image'    => 'member_a(133).png',
                'mini_url' => '/packageH/starMusic/starMusicIndex/starMusicIndex',
            ];
        }

        if (app('plugins')->isEnabled('luck-draw')) {
            $set = \Setting::get('plugin.luck-draw');
            if ($set['is_show'] == 1) {
                $data[] = [
                    'name'     => 'luck-draw',
                    'title'    => '抽奖活动',
                    'class'    => 'icon-fontclass-choujianghuodong',
                    'url'      => 'luckyDraw',
                    'image'    => 'member_a(138).png',
                    'mini_url' => '/packageH/newDraw/luckyDraw/luckyDraw',
                ];
            }
        }

        if (app('plugins')->isEnabled('new-retail')) {
            $set = \Setting::get('plugin.new-retail');
            $retail_name = empty($set['new_name']) ? "新零售" : $set['new_name'];
            if ($set['is_open_retail'] == 1) {
                $data[] = [
                    'name'     => 'new-retail',
                    'title'    => $retail_name,
                    'class'    => 'icon-fontclass-lingshou',
                    'url'      => 'newRetailIndex',
                    'image'    => 'member_a(152).png',
                    'mini_url' => '/packageH/newRetail/newRetailIndex/newRetailIndex',
                ];
            }
        }

        //分公司
        if (app('plugins')->isEnabled('subsidiary')) {
            $data[] = [
                'name'     => 'subsidiary',
                'title'    => '分公司管理',
                'class'    => 'icon-member_branch',
                'url'      => 'subsidiary',
                'image'    => 'member_a(101).png',
                'mini_url' => '/packageA/member/myOrder_v2/myOrder_v2?status=0&orderType=subsidiary',
            ];
        }
        //易加油
        if (app('plugins')->isEnabled('easy-refuel')) {
            $data[] = [
                'name'     => 'easy-refuel',
                'title'    => '易加油',
                'class'    => 'icon-member_ejiayou',
                'url'      => 'easyRefuel',
                'image'    => 'member_a(103).png',
                'mini_url' => '/packageD/easyRefuel/easyRefuel',
            ];
        }

        if (app('plugins')->isEnabled('activity-apply')) {
            $data[] = [
                'name'     => 'activity-apply',
                'title'    => '活动报名',
                'class'    => 'icon-member_meeting_active',
                'url'      => 'activityHome',
                'image'    => 'member_a(88).png',
                'mini_url' => '/packageC/Myshaky/activityHome/activityHome',
            ];
        }

        //推广小助手
        if (app('plugins')->isEnabled('promotion-assistant')) {
            $set = \Setting::get('promotion-assistant.data');
            if ($set['is_open'] == 1) {
                $title = empty($set['plugin_name']) ? '推广小助手' : $set['plugin_name'];
                $data[] = [
                    'name'     => 'promotion-assistant',
                    'title'    => $title,
                    'class'    => 'icon-member_promoteassitant',
                    'url'      => 'extendHelper',
                    'image'    => 'member_a(90).png',
                    'mini_url' => '',
                ];
            }
        }
        //问卷功能
        if (app('plugins')->isEnabled('questionnaire')) {

            $type = request()->get('type');
            if (empty($type) || $type == 1 || $type == 5 || $type == 2) {
                $questionnaire = Setting::get('plugin.questionnaire');
                if ($questionnaire['is_open'] == 1) {
                    $pluginName = $questionnaire['title'];
                    $data[] = [
                        'name'     => 'questionnaire',
                        'title'    => $pluginName ?: '问卷分析',
                        'class'    => 'icon-member_questionsurvey',
                        'url'      => 'questionnaireIndex',
                        'image'    => 'member_a(111).png',
                        'mini_url' => '/packageE/questionnaire/questionnaireIndex/questionnaireIndex',
                    ];
                }
            }
        }


        //优惠券二维码
        if (app('plugins')->isEnabled('coupon-qr')) {
            $data[] = [
                'name'     => 'coupon-qr',
                'title'    => '优惠券二维码',
                'class'    => 'icon-member_coupons_qrcode',
                'url'      => 'coupons_qrcode',
                'image'    => 'member_a(109).png',
                'mini_url' => '/packageC/couponList/couponList'
            ];
        }

        if (app('plugins')->isEnabled('elite-award')) {
            $is_open = \Setting::get('plugin.elite_award.is_show');
            if ($is_open == 1) {
                $data[] = [
                    'name'     => 'elite-award',
                    'title'    => '精英奖',
                    'class'    => 'icon-member_elitecenter',
                    'url'      => 'eliteAwardHome',
                    'image'    => 'member_a(105).png',
                    'mini_url' => '',
                ];
            }
        }

        if (app('plugins')->isEnabled('health-assessment')) {
            $type = request()->get("type");
            if (empty($type) || $type == 2) {
                $health_set = \Setting::get('plugin.health-assessment');
                if ($health_set['is_open_health'] == 1) {
                    $health_name = empty($health_set['health_name']) ? "健康测评" : $health_set['health_name'];
                    $data[] = [
                        'name'     => 'health-assessment',
                        'title'    => $health_name,
                        'class'    => 'icon-member_healthtest',
                        'url'      => 'healthyIndex',
                        'image'    => 'member_a(112).png',
                        'mini_url' => '/packageE/healthy/healthyIndex/healthyIndex',
                    ];
                }
            }
        }

        //能量舱
        if (app('plugins')->isEnabled('energy-cabin')) {
            $is_package = \Setting::get('plugin.energy_cabin.is_open');
            if ($is_package) {
                $data[] = [
                    'name'     => 'energy_cabin',
                    'title'    => \Yunshop\EnergyCabin\common\EnergyCabinSet::getName(),
                    'class'    => 'icon-member_chamber',
                    'url'      => 'memberCard',
                    'image'    => 'member_a(106).png',
                    'mini_url' => '/packageE/energy/userCard/userCard',
                ];
            }
        }

        //能量舱经销商
        if (app('plugins')->isEnabled('energy-cabin') && app('plugins')->isEnabled('team-dividend')) {

            $energy_cabin_dealer = \Yunshop\TeamDividend\models\TeamDividendAgencyModel::getAgentByUidId($memberId)->first();

            $is_package = \Setting::get('plugin.energy_cabin.is_open');
            if ($is_package && $energy_cabin_dealer) {
                $data[] = [
                    'name'     => 'energy_cabin_dealer',
                    'title'    => \Yunshop\EnergyCabin\common\EnergyCabinSet::getName() . '经销商',
                    'class'    => 'icon-member_chamber_dealer',
                    'url'      => 'powerSell',
                    'image'    => 'member_a(107).png',
                    'mini_url' => '/packageE/energy/powerSell/powerSell',
                ];
            }
        }

        //群活码
        if (app('plugins')->isEnabled('group-code')) {
            $is_open = \Setting::get('plugin.group-code.is_open');
            if ($is_open == 1) {
                $data[] = [
                    'name'     => 'group-code',
                    'title'    => '群活码',
                    'class'    => 'icon-member_group_livecode',
                    'url'      => 'groupCode',
                    'image'    => 'member_a(110).png',
                    'mini_url' => '/packageE/groupCode/groupCode',
                ];
            }
        }

        //站内消息
        if (app('plugins')->isEnabled('instation-message')) {
            $is_open = \Setting::get('plugin.instation-message.is_open');
            if ($is_open == 1) {
                $data[] = [
                    'name'     => 'instation-message',
                    'title'    => '消息',
                    'class'    => 'icon-member_massage_remind',
                    'url'      => 'stationNotice',
                    'image'    => 'member_a(115).png',
                    'mini_url' => '/packageE/stationNotice/stationNotice',
                ];
            }
        }

        //云仓
        if (app('plugins')->isEnabled('cloud-warehouse')) {
            $is_open = \Setting::get('plugin.cloud_warehouse.is_open');
            if ($is_open) {
                $data[] = [
                    'name'     => 'cloud_warehouse',
                    'title'    => '云仓',
                    'class'    => 'icon-fontclass-yuncang',
                    'url'      => 'cloudIndex',
                    'image'    => 'member_a(125).png',
                    'mini_url' => '',
                ];
            }
        }
        if (app('plugins')->isEnabled('commission-statistics')) {
            $isAgent = Agents::getAgentByUidId($memberId)->first();
            if ($isAgent || \YunShop::isWeb()) {
                $data[] = [
                    'name'     => 'TeamDistributions',
                    'title'    => '团队分销',
                    'class'    => 'icon-fontclass-tuanduifenxiaoshang',
                    'url'      => 'TeamDistribution',
                    'image'    => 'member_a(126).png',
                    'mini_url' => '/packageE/TeamDistribution/TeamDistribution',
                ];
            }
        }

        //AI智能测肤
        if (app('plugins')->isEnabled('skin-check')) {
            $is_open = \Yunshop\SkinCheck\service\SetService::getPluginStatus();
            if ($is_open) {
                $data[] = [
                    'name'     => 'skin-check',
                    'title'    => \Yunshop\SkinCheck\service\SetService::getCustomizeName(),
                    'class'    => 'icon-fontclass-cefu',
                    'url'      => 'skinHome',
                    'mini_url' => '/packageE/others/skin/skin',
                    'image'    => 'member_a(129).png'
                ];
            }
        }

        if (app('plugins')->isEnabled('product-market')) {
            $data[] = [
                'name'     => 'applicationMarket',
                'title'    => '应用市场',
                'class'    => 'icon-fontclass-yiingyongguanli',
                'url'      => 'applicationMarket',
                'mini_url' => '/packageE/others/application_market/application_market',
                'image'    => 'member_a(131).png'
            ];
        }
        if (app('plugins')->isEnabled('market-sub')) {
            $data[] = [
                'name'     => 'marketSub',
                'title'    => '应用市场',
                'class'    => 'icon-fontclass-yiingyongguanli',
                'url'      => 'marketSub',
                'mini_url' => '/packageE/others/application_market/application_market?marketSub=1',
                'image'    => 'member_a(131).png'
            ];
        }
        //提货卡
        if (app('plugins')->isEnabled('picking-card')) {

            $settings = Setting::get("card_setting");

            if ($settings["switch"] == 1) {
                $data[] = [
                    'name'  => 'pickUpCardOrderList',
                    'title' => '提货卡',
                    'class' => 'icon-fontclass-tihuoka',
                    'url'   => 'pickUpCardOrderList',
                    'image' => 'member_a(127).png'
                ];
            }

        }

        //下级足迹
        if (app('plugins')->isEnabled('lower-footprint')) {
            $is_open = \Yunshop\LowerFootprint\services\SetService::getPluginStatus();
            if ($is_open) {
                $data[] = [
                    'name'  => 'lower-footprint',
                    'title' => '下级足迹',
                    'class' => 'icon-fontclass-xiajizuji',
                    'url'   => 'footprintMember',
                    'image' => 'member_a(142).png'
                ];
            }
        }

        if (app('plugins')->isEnabled('case-library')) {
            $data[] = [
                'name'     => 'case-library',
                'title'    => \YunShop\CaseLibrary\service\SetService::getCustomizeName(),
                'class'    => 'icon-fontclass-anliku',
                'url'      => 'caseLibraryHome',
                'mini_url' => '/packageE/others/case_library/home/caseLibraryHome',
                'image'    => 'member_a(132).png'
            ];
        }

        //推广员视频答题
        if (app('plugins')->isEnabled('agent-qa')) {
            $set = \Setting::get('plugin.agent_qa');
            if ($set['is_open']) {
                $data[] = [
                    'name'     => 'agent_qa',
                    'title'    => $set['name'] != '' ? $set['name'] : "推广员视频&答题",
                    'class'    => 'icon-fontclass-shipindati',
                    'url'      => 'videoQuestion',
                    'image'    => 'member_a(133).png',
                    'mini_url' => '/packageE/video_question/video_question',
                ];
            }
        }

        //全国油站
        if (app('plugins')->isEnabled('oil-station')) {
            $oil_setting = \Setting::get('plugin.oil_station');
            if ($oil_setting['open_state']) {
                $data[] = [
                    'name'     => 'oil_station',
                    'title'    => '全国油站',
                    'class'    => 'icon-fontclass-quanguojiayou',
                    'url'      => 'oilStation',
                    'image'    => 'member_a(151).png',
                    'mini_url' => '',
                ];
            }
        }


        //圈子
        if (app('plugins')->isEnabled('circle')) {
            $circle_set = \Setting::get('circle.data');
            $data[] = [
                'name'     => 'circle',
                'title'    => empty($circle_set['plugin_name']) ? '圈子' : $circle_set['plugin_name'],
                'class'    => 'icon-fontclass-quanzi2',
                'url'      => 'circleIndex',
                'image'    => 'extension(81).png',
                'mini_url' => '/packageH/circleCommunity/circleIndex/circleIndex',
            ];
        }

        //上门安装
        if (app('plugins')->isEnabled('live-install')) {
            $another_name = \Yunshop\LiveInstall\services\SettingService::getAnotherName();
            $data[] = [
                'name'     => 'live_install',
                'title'    => $another_name['plugin_name'],
                'class'    => 'icon-fontclass-anzhuangfuwuhy',
                'url'      => 'installationSeverMemberHome',
                'image'    => 'member_a(144).png',
                'mini_url' => '/packageH/installationSever/installationSeverMemberHome/installationSeverMemberHome',
            ];

            $live_install_worker_data = [
                'name'     => 'live_install_worker',
                'title'    => $another_name['worker_name'] . '申请',
                'class'    => 'icon-fontclass-anzhuangshifu1',
                'url'      => 'installationSeverApply',
                'image'    => 'member_a(145).png',
                'mini_url' => '/packageH/installationSever/installationSeverApply/installationSeverApply',
            ];

            if (\Yunshop\LiveInstall\services\SettingService::checkIsWorker()) {
                $live_install_worker_data['class'] = 'icon-fontclass-anzhuangshifu1';
                $live_install_worker_data['title'] = $another_name['worker_name'];
                $live_install_worker_data['url'] = 'installationSeverHome';
                $live_install_worker_data['image'] = 'member_a(145).png';
                $live_install_worker_data['mini_url'] = '/packageH/installationSever/installationSeverHome/installationSeverHome';
            }

            $data[] = $live_install_worker_data;
        }

        // 云库存
        if (app('plugins')->isEnabled('agency')) {
            $set = \Setting::get('plugin.agency');
            if ($set['enabled']) {
                $data[] = [
                    'name'     => 'agency',
                    'title'    => "云库存",
                    'class'    => 'icon-fontclass-yunkucun',
                    'url'      => 'StockIndex',
                    'image'    => 'member_a(140).png',
                    'mini_url' => '/packageH/stock/index/index',
                ];
            }
        }

        //聚合CPS-卡券
        if (app('plugins')->isEnabled('aggregation-cps')) {
            if (!EquityGoodsSkuModel::get()->isEmpty()) {
                $data[] = [
                    'name'     => 'aggregation-cps',
                    'title'    => '卡券',
                    'class'    => 'icon-fontclass-huiyuankaquanrukou',
                    'url'      => 'cpsCard',
                    'image'    => 'member_a(143).png',
                    'mini_url' => '/packageH/cpsCard/cpsCard',
                ];
            }
        }

        //推客排行榜
        if (app('plugins')->isEnabled('commission-ranking')) {

            $data[] = [
                'name'     => 'commission-ranking',
                'title'    => \Setting::get('plugin.commission_ranking.plugin_name') ?: '推客排行榜',
                'class'    => 'icon-fontclass-paihang',
                'url'      => '',
                'image'    => 'member_a(144).png',
                'mini_url' => '/packageE/ranking_2/index/index',
            ];
        }

        //盲盒
        if (app('plugins')->isEnabled('blind-box')) {
            $is_open = \Yunshop\BlindBox\service\SetService::getPluginStatus();
            if ($is_open) {
                $data[] = [
                    'name'     => 'blind-box',
                    'title'    => '盲盒',
                    'class'    => 'icon-fontclass-manghe',
                    'url'      => 'blindBoxIndex',
                    'mini_url' => '/packageH/blindBox/blindBoxIndex/blindBoxIndex',
                    'image'    => 'member_a(139).png'
                ];
            }
        }
        //一校一企
        if (app('plugins')->isEnabled('school-company')) {
            $schoolSetting = \Yunshop\SchoolCompany\services\CommonService::getSet();
            if ($schoolSetting['switch']) {
                $data[] = [
                    'name'     => 'school-company',
                    'title'    => '一校一企',
                    'class'    => 'icon-fontclass-yixiaoyiqi',
                    'url'      => '',
                    'mini_url' => '/packageH/schoolCompany/schoolCompanyIndex/schoolCompanyIndex',
                    'image'    => 'member_a(150).png'
                ];
            }
        }

        //期刊杂志
        if (app('plugins')->isEnabled('journal')) {
            $is_open = \Yunshop\Journal\services\SetConfigService::getSetConfig('is_open');
            if ($is_open) {
                $data[] = [
                    'name'     => 'journal',
                    'title'    => '期刊杂志',
                    'class'    => 'icon-fontclass-waikan',
                    'url'      => 'periodicalIndex',
                    'mini_url' => '/packageH/article/periodicalIndex/periodicalIndex',
                    'image'    => 'member_a(153).png'
                ];
            }
        }


        //消费奖励
        if (app('plugins')->isEnabled('consumer-reward')) {
            $set = \Setting::get('plugin.consumer_reward');
            if ($set['is_display']) {
                $data[] = [
                    'name'     => 'consumer-reward',
                    'title'    => $set['name'] ?: '消费奖励',
                    'class'    => 'icon-fontclass-xiaofeijiangli',
                    'url'      => 'consumerRewardIndex',
                    'mini_url' => '/packageH/consumerReward/consumerRewardIndex/consumerRewardIndex',
                    'image'    => 'member_a(153).png'
                ];
            }
        }


        //车场
        if (app('plugins')->isEnabled('kart')) {
            $data[] = [
                'name'     => 'kart',
                'title'    => '车场计时',
                'class'    => 'icon-fontclass-cehchang',
                'url'      => '',
                'mini_url' => '/packageH/turmaroundTime/turmaroundMy/turmaroundMy',
                'image'    => 'image member_a(157).png'
            ];
        }


        //客服
        if (app('plugins')->isEnabled('yun-chat')) {
            $data[] = [
                'name'     => 'kefu',
                'title'    => '客服',
                'class'    => 'icon-member_kefu',
                'url'      => 'chatList',
                'mini_url' => '/packageH/chitchat/chatList/chatList',
                'image'    => 'tool_a(11).png'
            ];
        }
        //航班信息收集
        if (app('plugins')->isEnabled('flight-collect')) {
            $flight_switch = \Setting::get('plugin.flight-collect.switch');
            if ($flight_switch) {
                $data[] = [
                    'name'     => 'flight-collect',
                    'title'    => '航班信息',
                    'class'    => 'icon-fontclass-hangbanxinxi',
                    'url'      => 'flightInformation',
                    'mini_url' => '/packageH/flightInformation/flightInformation',
                    'image'    => 'member_a(156).png'
                ];
            }

        }

        //店商联盟
        if (app('plugins')->isEnabled('store-business-alliance')) {
            $is_open = \Setting::get('plugin.store-business-alliance.is_open');
            if ($is_open) {
                $data[] = [
                    'name'     => 'store-business-alliance',
                    'title'    => '店商联盟',
                    'class'    => 'icon-fontclass-dianshanglianmeng',
                    'url'      => 'leagueStoreIndex',
                    'mini_url' => '',
                    'image'    => 'member_a(154).png'
                ];
            }
        }

        //群拓客
        if (app('plugins')->isEnabled('group-develop-user')) {
            $data[] = [
                'name'     => 'group-develop-user',
                'title'    => '群拓客',
                'class'    => 'icon-fontclass-quntuoke',
                'url'      => 'gduIndex',
                'image'    => 'member_a(158).png',
                'mini_url' => '',
            ];
        }

        //珍惠拼
        if (app('plugins')->isEnabled('zhp-group-lottery')) {
            $setting = \Yunshop\ZhpGroupLottery\models\ZhpSettingModel::getAll();
            if ($setting['is_open'] == 1) {

                $data[] = [
                    'name'     => 'zhp-group-lottery',
                    'title'    => '珍惠拼',
                    'class'    => 'icon-fontclass-huizhenpin',
                    'url'      => 'zhpGroupList',
                    'image'    => 'member_a(160).png',
                    'mini_url' => '/packageB/member/group/GroupList/GroupList?types=zhpGroup',
                ];
            }
        }

        //新媒体
        if (app('plugins')->isEnabled('new-media-advertising')) {
            $is_open = \Setting::get('new_media_advertising.data.is_open');

            if ($is_open) {

                $data[] = [
                    'name'     => 'new-media-advertising',
                    'title'    => '新媒体',
                    'class'    => 'icon-member-newMedia',
                    'url'      => 'newMediaGuide',
                    'image'    => 'member_a(171).png',
                    'mini_url' => '/packageI/newMedia/newMediaGuide/newMediaGuide',
                ];
            }
        }

        if (app('plugins')->isEnabled('yee-pay')) {
            $is_open = \Yunshop\YeePay\services\SetService::getStatus();
            if ($is_open) {
                $data[] = [
                    'name'     => 'yee-pay',
                    'title'    => '易宝代付签约',
                    'class'    => 'icon-fontclass-yibaodaifu',
                    'url'      => 'YeePay',
                    'image'    => 'member_a(161).png',
                    'mini_url' => '',
                ];
            }
        }

        //投放广告视频
        if (app('plugins')->isEnabled('ad-serving')) {
            $advertiser = \Yunshop\AdServing\models\AdServingAdvertisersModel::getAdvertisersByMemberId($memberId);
            $is_open = \Yunshop\AdServing\services\SetConfigService::getSetConfig('is_open');

            if ($is_open) {
                if (!$advertiser) {
                    $data[] = [
                        'name'     => 'ad-serving',
                        'title'    => '广告主申请',
                        'class'    => 'icon-fontclass-guanggaozhushenqing',
                        'url'      => 'advertisingApply',
                        'image'    => 'member_a(163).png',
                        'mini_url' => '/packageH/Advertising/advertisingApply/advertisingApply',
                    ];
                } else {
                    $data[] = [
                        'name'     => 'ad-serving',
                        'title'    => '广告主',
                        'class'    => 'icon-fontclass-guanggaozhu',
                        'url'      => 'advertisingAdvertisers',
                        'image'    => 'member_a(164).png',
                        'mini_url' => '/packageH/Advertising/advertisingAdvertisers/advertisingAdvertisers',
                    ];
                }
            }

        }

        //商品免单
        if (app('plugins')->isEnabled('free-lottery')) {
            $is_open = \Yunshop\FreeLottery\services\FreeLotteryService::config('open');
            if ($is_open) {
                $data[] = [
                    'name'     => 'free-lottery',
                    'title'    => \Yunshop\FreeLottery\services\FreeLotteryService::name(),
                    'class'    => 'icon-fontclass-miandan',
                    'url'      => 'FreeRecord',
                    'image'    => 'member_a(165).png',
                    'mini_url' => '/packageH/free_of_charge/FreeRecord/FreeRecord',
                ];
            }
        }
        //多门店核销
        if (app('plugins')->isEnabled('store-projects')) {
            $data[] = [
                'name'     => 'StoreVerification',
                'title'    => '我的项目',
                'class'    => 'icon-fontclass-mendianhexiao',
                'url'      => 'StoreVerification',
                'image'    => 'member_a(162).png',
                'mini_url' => '/packageH/project_verification/StoreVerification/StoreVerification',
            ];
        }
        //满额赠送
        if (app('plugins')->isEnabled('full-return')) {
            $is_open = \Setting::get('plugin.full-return');
            if ($is_open['is_open_return'] && !empty($is_open['is_show_total'])) {
                $data[] = [
                    'name'     => 'full-return',
                    'title'    => \Yunshop\FullReturn\admin\SetController::getSet()['full_return_name'] ?: '满额赠送',
                    'class'    => 'icon-fontclass-miandan',
                    'url'      => 'interestsGive',
                    'image'    => 'member_a(170).png',
                    'mini_url' => '/packageH/interests_give/interests_give',
                ];
            }
        }

        //0.1元拼团
        if (app('plugins')->isEnabled('group-work')) {
            $is_open = \Setting::get('plugin.group_work');
            if ($is_open['plugin_switch'] == 1) {
                $data[] = [
                    'name'     => 'group-work',
                    'title'    => $is_open['plugin_name'] ? $is_open['plugin_name'] : '0.1元拼',
                    'class'    => 'icon-member_groupWork',
                    'url'      => 'groupWorkMy',
                    'image'    => 'member_a(172).png',
                    'mini_url' => '/packageI/groupWork/groupWorkMy/groupWorkMy',
                ];
            }
        }

        foreach ($data as $k => $v) {
            if (in_array($v['name'], $diyarr['tool'])) {
                $arr['tool'][] = $v;
            }
            if (in_array($v['name'], $diyarr['asset_equity'])) {
                $arr['asset_equity'][] = $v;
            }
            if (in_array($v['name'], $diyarr['merchant'])) {
                $arr['merchant'][] = $v;
            }
            if (in_array($v['name'], $diyarr['market'])) {
                $arr['market'][] = $v;
            }
        }
        $arr['ViewSet'] = [];
        if (app('plugins')->isEnabled('designer')) {
            //获取所有模板
            $sets = ViewSet::uniacid()->select('names', 'type')->get()->toArray();

            foreach ($sets as $k => $v) {
                $arr['ViewSet'][$v['type']]['name'] = $v['names'];
                $arr['ViewSet'][$v['type']]['name'] = $v['names'];
            }
        }

        $arr['is_open'] = [
            'is_open_hotel'               => app('plugins')->isEnabled('hotel') ? 1 : 0,
            'is_open_net_car'             => app('plugins')->isEnabled('net-car') ? 1 : 0,
            'is_open_fight_groups'        => app('plugins')->isEnabled('fight-groups') ? 1 : 0,
            'is_open_lease_toy'           => \app\common\services\plugin\leasetoy\LeaseToySet::whetherEnabled(), //租赁订单列表是否开启
            'is_open_converge_pay'        => app('plugins')->isEnabled('converge_pay') ? 1 : 0,
            'is_open_snatch_regiment'     => app('plugins')->isEnabled('snatch-regiment') ? 1 : 0,
            'is_open_live_install'        => app('plugins')->isEnabled('live-install') ? 1 : 0,
            'is_open_live_install_worker' => app('plugins')->isEnabled('live-install') ? 1 : 0,
            'is_open_aggregation_cps'     => app('plugins')->isEnabled('aggregation-cps') ? 1 : 0,
            'is_open_group_work'          => app('plugins')->isEnabled('group-work') ? 1 : 0,
            'is_store'                    => $store && $store->is_black != 1 ? 1 : 0,
        ];
        return $arr;
    }

    public function morePluginData()
    {
        $arr = [
            [
                'name'   => '必备工具',
                'plugin' => [
                    'm-collection', 'm-footprint', 'm-address', 'm-info', 'findpwd', 'member_code', 'member_pay_code', 'm-guanxi', 'ranking', 'material-center',
                    'm-coupon', 'instation-message', 'applicationMarket', 'marketSub', 'converge_pay', 'group-develop-user', 'lower-footprint', 'aggregation-cps',
                    'group-code', 'my-friend', 'commission-ranking',
                ]
            ],
            [
                'name'   => '便捷服务',
                'plugin' => [
                    'recharge_code', 'exchange', 'help-center', 'article', 'video_demand', 'easy-refuel', 'room-aide', 'promotion-assistant', 'oil_station',
                    'case-library'
                ]
            ],
            [
                'name'   => '互动参与',
                'plugin' => [
                    'm-erweima', 'm-pinglun', 'clock_in', 'conference', 'sign', 'fight_groups', 'video-share', 'micro-communities', 'luck-draw', 'my-snatch-regiment',
                    'snatch-regiment', 'star-spell', 'activity-apply', 'kefu', 'customer-development', 'circle', 'questionnaire', 'blind-box', 'agent_qa', 'new-media-advertising',
                    'group-work'
                ]
            ],
            [
                'name'   => '特色业务',
                'plugin' => [
                    'business_card', 'skin-check', 'appointment', 'auction', 'contractListPlu', 'room-apply', 'pending_order', 'room', 'room-code', 'micro', 'new-retail',
                    'pickUpCardOrderList', 'assemble', 'worker_apply', 'live_install', 'live_install_worker', 'StoreVerification',
                ]
            ],
            [
                'name'   => '商家合作',
                'plugin' => [
                    'store-cashier', 'cashier', 'supplier', 'hotel', 'package_deliver', 'subsidiary', 'channel', 'advert-market',
                    'school-company', 'ad-serving',
                ]
            ],
            [
                'name'   => '我的资产',
                'plugin' => [
                    'extension', 'credit', 'love', 'integral', 'froze', 'agency', 'consumer-reward', 'upgrade-code', 'credit-inventory',
                ]
            ],
        ];
        return $arr;
    }

    public function defaultPluginData($memberId)
    {
        $arr = [
            [
                'class'    => 'icon-fontclass-pinglun',
                'id'       => '111111116',
                'image'    => $this->handlePluginImage('tool_a(4).png'),
                'is_open'  => true,
                'mini_url' => "/packageD/member/myEvaluation/myEvaluation",
                'name'     => "m-pinglun",
                'title'    => "评论",
                'url'      => "myEvaluation"
            ],
            [
                'class'    => 'icon-fontclass-kehu',
                'id'       => '111111114',
                'image'    => $this->handlePluginImage('tool_a(3).png'),
                'is_open'  => true,
                'mini_url' => "/packageD/member/myRelationship/myRelationship",
                'name'     => "m-guanxi",
                'title'    => "客户",
                'url'      => "myRelationship"
            ],
            [
                'class'    => 'icon-fontclass-1',
                'id'       => '111111110',
                'image'    => $this->handlePluginImage('tool_a(6).png'),
                'is_open'  => true,
                'mini_url' => "/packageD/member/collection/collection",
                'name'     => "m-collection",
                'title'    => "收藏",
                'url'      => "collection"
            ],
            [
                'class'    => 'icon-fontclass-zuji2',
                'id'       => '111111111',
                'image'    => $this->handlePluginImage('tool_a(8).png'),
                'is_open'  => true,
                'mini_url' => "/packageD/member/footprint/footprint",
                'name'     => "m-footprin",
                'title'    => "足迹",
                'url'      => "footprint"
            ],
            [
                'class'    => 'icon-fontclass-dizhi',
                'id'       => '111111112',
                'image'    => $this->handlePluginImage('tool_a(1).png'),
                'is_open'  => true,
                'mini_url' => "/packageD/member/addressList/addressList",
                'name'     => "m-address",
                'title'    => "地址管理",
                'url'      => "address"
            ],
            [
                'class'    => 'icon-fontclass-shezhi',
                'id'       => '111111113',
                'image'    => $this->handlePluginImage('tool_a(5).png'),
                'is_open'  => true,
                'mini_url' => "/packageA/member/info/info",
                'name'     => "m-info",
                'title'    => "设置",
                'url'      => "info"
            ],
            [
                'class'    => 'icon-fontclass-youhuiquan',
                'id'       => '111111114',
                'image'    => $this->handlePluginImage('tool_a(7).png'),
                'is_open'  => true,
                'mini_url' => "/packageA/member/coupon_v2/coupon_v2",
                'name'     => "m-coupon",
                'title'    => "优惠券",
                'url'      => "coupon"
            ],
        ];
        $isAgent = MemberShopInfo::uniacid()->select('is_agent')->where('member_id', $memberId)->first();
        if ($isAgent->is_agent == 1) {
            $arr[] = [
                'class'   => 'icon-fontclass-erweima',
                'id'      => '111111115',
                'image'   => $this->handlePluginImage('tool_a(2).png'),
                'is_open' => true,
                'name'    => "m-erweima",
                'url'     => 'm-erweima',
                'title'   => "二维码",
            ];
        }
        return $arr;
    }

    /**
     * 新会员中心模板01：需要在会员中心页面显示的推荐插件
     *
     */
    public function sortPluginData()
    {
        //键值即排序
        $plugin = [
            'm-erweima', 'm-pinglun', 'm-guanxi', 'member_code', 'member_pay_code', 'fight_groups', 'video-share', 'micro-communities', 'StoreVerification', 'business_card',
            'room', 'article', 'ranking', 'sign', 'clock_in', 'material-center', 'video_demand', 'group-develop-user', 'circle', 'activity-apply', 'blind-box',
            'store-cashier', 'cashier', 'supplier', 'oil_station', 'case-library', 'micro', 'exchange', 'help-center', 'room-aide'
        ];
        return $plugin;
    }

    private function handlePluginImage($image)
    {
        if (config('app.framework') == 'platform') {
            $dir = '/';
        } else {
            $dir = '/addons/yun_shop/';
        }
        return $dir . 'static/yunshop/designer/images/' . $image;
    }
}


