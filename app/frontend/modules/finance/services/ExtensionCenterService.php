<?php
/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 2021/7/29
 * Time: 10:24
 */

namespace app\frontend\modules\finance\services;


use app\common\facades\Setting;
use app\common\helpers\ImageHelper;
use app\common\models\Income;
use app\common\models\member\ChildrenOfMember;
use app\common\models\Order;
use app\common\services\income\IncomeService;
use app\common\services\member\MemberCenterService;
use app\common\services\popularize\PortType;
use app\frontend\models\Member;
use app\frontend\models\MemberLevel;
use app\frontend\models\MemberRelation;
use app\frontend\models\MemberShopInfo;
use app\frontend\modules\finance\controllers\OrderAllController;
use app\frontend\modules\finance\factories\IncomePageFactory;
use app\frontend\modules\member\models\MemberModel;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use phpDocumentor\Reflection\Types\Self_;

class ExtensionCenterService
{
    protected static $request;
    protected static $memberId;
    protected static $member;
    protected static $total_income;
    protected static $relation;
    protected static $power;
    protected static $buttonEnabled;

    const PLUGIN = [
        'm-erweima' => '二维码',
        'material-center' => '素材中心',
        'article' => '拓客文章',
        'video-share' => '拓客视频',
        'micro-communities' => '种草社区',
        'business_card' => '个人名片',
        'group-develop-user' => '群拓客',
        'group-code' => '群活码',
        'room' => '直播',
        'ranking' => '排行榜',
    ];

    public static function init($request)
    {
        self::$memberId = \Yunshop::app()->getMemberId();
        self::$request = $request;
    }

    /**
     * @return Member
     * @throws \app\common\exceptions\AppException
     */
    private static function member()
    {
        if (!isset(self::$member)) {
            self::$member = Member::current();
        }
        return self::$member;
    }

    /**
     * @return array
     * @throws \app\common\exceptions\AppException
     */
    public static function getIncomePage()
    {
        $power = self::power();
        $relation_set = \Setting::get('member.relation');
        $extension_set = \Setting::get('popularize.mini');
        $jump_link = '';
        $small_jump_link = '';
        $small_extension_link = '';
        if ($relation_set['is_jump'] && !empty($relation_set['jump_link'])) {
            if (!self::isAgent()) {
                $jump_link = $relation_set['jump_link'];
                $small_jump_link = $relation_set['small_jump_link'];
                $small_extension_link = $extension_set['small_extension_link'];
            }
        }


        //提现额度
        $withdraw_limit = [
            'is_show' => false
        ];

        if(app('plugins')->isEnabled('withdrawal-limit')) {
            $limit_set = array_pluck(\Setting::getAllByGroup('withdrawal-limit')->toArray(), 'value', 'key');
            if($limit_set['is_open'] == 1 && $limit_set['is_show'] == 1) {
                $memberModel = \Yunshop\WithdrawalLimit\Common\models\MemberWithdrawalLimit::uniacid()->where('member_id',self::$memberId)->first();
                if($memberModel){
                    $limit = $memberModel->total_amount;
                }else{
                    $limit = 0;
                }
                $withdraw_limit = [
                    'is_show' => true,
                    'amount' => $limit
                ];
            }
        }


        return [
            'set' => self::getSet(),
            'jump_link' => $jump_link,
            'small_jump_link' => $small_jump_link,
            'small_extension_link' => $small_extension_link,
            'button_enabled' => self::buttonEnabled(),
            'button' => self::button(),
            'info' => self::userInfo(),
            'identity' => self::identity(),
            'income_statistic' => self::incomeStatistic(),
            'income_dynamic' => self::incomeDynamic(),
            'available' => $power['available'],
            'unavailable' => $power['unavailable'],
            'fans_fission' => self::fansFission(),
            'plugin_data' => self::pluginData(),
            'extension_order' => self::extensionOrder(),
            'service' => self::getService(),
            'withdraw_limit' => $withdraw_limit,
            'member_auth_pop_switch' => Setting::get('plugin.min_app.member_auth_pop_switch') ? 1 : 0,
        ];
    }

    protected static function getSet()
    {
        $type = PortType::determineType(self::$request['type']);

        $background_color = '';
        if ($type) {
            $info = Setting::get('popularize.'.$type);
            $background_color = $info['background_color'] ? : '';
        }
        return ['background_color' => $background_color];
    }

    /**
     * 按钮
     * @return array
     */
    protected static function button()
    {
        $button = [];
        $buttonEnabled = self::buttonEnabled();
        $lang_set = \Setting::get('shop.lang', ['lang' => 'zh_cn']);
        if (!empty($buttonEnabled['plugin_settle_show'])) {
            $button[] = [
                'title'    => "领取收益",
                'icon'    => 'icon-fontclass-leiji',
                'mini_url' => "/packageA/member/extension/myEarnings/myEarnings",
                'url'      => "myEarnings"
            ];
        }
        $button[] = [
            'title'    => ($lang_set['income_name'] ? : '收入')."明细",
            'icon'    => 'icon-fontclass-shouru1',
            'mini_url' => "/packageA/member/extension/incomedetails/incomedetails",
            'url'      => "incomedetails"
        ];
        $button[] = [
            'title'    => ($lang_set['name_of_withdrawal'] ? : '提现')."明细",
            'icon'    => 'icon-fontclass-ticheng',
            'mini_url' => "/packageA/member/presentationRecord_v2/presentationRecord_v2?orderType=extension",
            'url'      => "presentationRecord"
        ];
        if (!empty($buttonEnabled['is_show_performance'])) {
            $button[] = [
                'title'    => "营业额",
                'icon'    => 'icon-fontclass-yingye',
                'mini_url' => "/packageA/member/extension/Performance/Performance",
                'url'      => "Performance"
            ];
        }
        $plugins = app('plugins')->getEnabledPlugins('*');
        foreach ($plugins as $plugin) {
            $class = $plugin->app();
            if (method_exists($class,'incomePageButton')) {
                $button[] = $class->incomePageButton(self::$memberId);
            }
        }
        return array_values(array_filter($button));
    }

    protected static function buttonEnabled()
    {
        if (!isset(self::$buttonEnabled)) {
            $is_show_performance = OrderAllController::isShow();
            $share_page = self::getSharePageStatus();
            self::$buttonEnabled = [
                'is_show_performance' => $is_show_performance,              //营业额按钮显示
                'share_page' => $share_page,                                //收入分享按钮显示
                'plugin_settle_show' => PluginSettleService::doesIsShow(),  //领取收益按钮显示
                'show_member_id' => PortType::showMemberId(self::$request['type']),
                'is_show_unable' => PortType::isShowUnable(self::$request['type']),//更多权限是否显示
                'withdraw_date' => self::getWithdrawDate(),
            ];
        }
       return self::$buttonEnabled;
    }

    private static function getWithdrawDate()
    {
        $income_set = Setting::get('withdraw.income');
        $withdraw_date = [
            'day' => 0, //可提现日期
            'disable' => 0 //是否禁用
        ];
        $day_msg = '无提现限制';
        if (is_array($income_set['withdraw_date'])) {
            $day = date('d');
            $day_msg = '可提现日期为：'.implode(',',$income_set['withdraw_date']).'号';
            $withdraw_date = [
                'day' => min($income_set['withdraw_date']),
                'disable' => 1
            ];
            foreach ($income_set['withdraw_date'] as $date) {
                if ($day == $date) {
                    $withdraw_date = [
                        'day' => $date,
                        'disable' => 0,
                    ];
                    break;
                }
                if ($day < $date) {
                    $withdraw_date = [
                        'day' => $date,
                        'disable' => 1,
                    ];
                }


            }
        }
        $withdraw_date['day_msg'] = $day_msg;
        return $withdraw_date;
    }

    /**
     * 会员基础信息
     * @return array
     * @throws \app\common\exceptions\AppException
     */
    protected static function userInfo()
    {
        $memberModel = self::member();
        //IOS时，把微信头像url改为https前缀
        $avatar = ImageHelper::iosWechatAvatar($memberModel->avatar);

        $autoWithdraw = 0;
        if (app('plugins')->isEnabled('mryt')) {
            $uid = \YunShop::app()->getMemberId();
            $autoWithdraw = (new \Yunshop\Mryt\services\AutoWithdrawService())->isWithdraw($uid);
        }

        if (app('plugins')->isEnabled('team-dividend')) {
            $uid = \YunShop::app()->getMemberId();
            $autoWithdraw = (new \Yunshop\TeamDividend\services\AutoWithdrawService())->isWithdraw($uid);
        }
        return [
            'avatar' => $avatar,
            'nickname' => $memberModel->nickname,
            'member_id' => $memberModel->uid,
            'autoWithdraw' => $autoWithdraw,
            'has_avatar' => $memberModel->has_avatar,
        ];
    }

    /**
     * 会员身份信息
     * @return array
     * @throws \app\common\exceptions\AppException
     */
    protected static function identity()
    {
        $identity = [];
        $memberModel = self::member();
        //会员等级
        if ($memberModel->yzMember->level_id == 0) {
            $set = Setting::get('shop.member');
            $identity[] = $set['level_name']?:'普通会员';
        } else {
            $level = MemberLevel::getMemberLevel($memberModel->yzMember->level_id);
            $identity[] = $level?$level->level_name:'';
        }

        $plugins = app('plugins')->getEnabledPlugins('*');
        foreach ($plugins as $plugin) {
            $class = $plugin->app();
            if (method_exists($class,'incomePageIdentity')) {
                $new = $class->incomePageIdentity(self::$memberId);
                $identity = is_array($new) ? array_merge($new,$identity) : $identity;
            }
        }
        return array_values(array_filter(array_unique($identity)));
    }

    public static function popularizeSet($type)
    {
        $type = PortType::determineType($type);

        if ($type) {
            $info = \Setting::get('popularize.'.$type);
            if (isset($info['plugin_mark'])) {
                return $info['plugin_mark'];
            }
        }

        return array();
    }

    /**
     * 权限
     * @return array
     * @throws \app\common\exceptions\AppException
     */
    protected static function power()
    {
        if (!isset(self::$power)) {
            $lang_set = self::getLangSet();

            $is_relation = self::isOpenRelation();

            $config = self::getIncomePageConfig();

            $total_income = self::getTotalIncome();

            //是否显示推广插件入口
            $popularize_set = self::popularizeSet(self::$request['type']);
            $available = [];
            $unavailable = [];
            foreach ($config as $key => $item) {
                $incomeFactory = new IncomePageFactory(new $item['class'], $lang_set, $is_relation, self::isAgent(), $total_income,$key);

                if (!$incomeFactory->isShow()) {
                    continue;
                }

                //不显示
                if (in_array($incomeFactory->getMark(), $popularize_set)) {
                    continue;
                }

                $income_data = $incomeFactory->getIncomeData(false);

                if ($incomeFactory->isAvailable()) {
                    $available[] = $income_data;
                } else {
                    $unavailable[] = $income_data;
                }
            }
            self::$power = ['available' => $available, 'unavailable' => $unavailable];
        }
        return self::$power;
    }

    /**
     * 收入统计
     * @return array
     */
    public static function incomeStatistic()
    {
        if (!empty(self::$request['income_statistic_type'])) {
            list($start,$end) = self::timeSolt(self::$request['income_statistic_type']);
        }

        //收入统计
        $total_amount = Income::uniacid()
            ->where('member_id',self::$memberId);
//            ->whereIn('incometable_type', $incomeConfig);
        if (!empty($start) && !empty($end)) {
            $total_amount = $total_amount->whereBetween('created_at',[$start,$end]);
        }
        $total_amount = $total_amount->sum('amount');

        $income  = Income::uniacid()
            ->select(DB::raw('SUM(IF(status=1,amount,0)) as withdraw,'
                .'SUM(IF(status=1 and pay_status IN(0,1),amount,0)) as withdrawing'))
            ->where('member_id',self::$memberId)
//            ->whereIn('incometable_type', $incomeConfig)
            ->first();

        //粉丝统计
        $total_fans = ChildrenOfMember::uniacid()
            ->where('member_id',self::$memberId)
            ->count();

        $first_fans = MemberShopInfo::uniacid()
            ->where('parent_id',self::$memberId);
        if (!empty($start) && !empty($end)) {
            $first_fans = $first_fans->whereBetween('child_time',[$start,$end]);
        }
        $first_fans = $first_fans->count();

        $un_withdraw = 0;
        $income_config = \app\backend\modules\income\Income::current()->getItems();
        foreach ($income_config as $item) {
            //余额不计算 拍卖预付款不计算
            if ($item['type'] == 'balance' || $item['type'] == 'auction_prepayment') {
                continue;
            }

            $incomeAmount = Income::uniacid()->canWithdraw()
                ->where('member_id', self::$memberId)
                ->where('incometable_type', $item['class'])
                ->sum('amount');
            if ($incomeAmount > 0) {
                $un_withdraw += $incomeAmount;
            }
        }

        return [
            [
                'title' => '累计收入',
                'url' => 'incomedetails',
                'mini_url' => '/packageA/member/extension/incomedetails/incomedetails',
                'value' => $total_amount ? : 0
            ],
            [
                'title' => '已提现收入',
                'url' => 'presentationRecord',
                'mini_url' => '/packageA/member/presentationRecord_v2/presentationRecord_v2?orderType=extension',
                'value' => $income &&$income->withdraw ? $income->withdraw : 0
            ],
            [
                'title' => '未提现收入',
                'url' => 'withdrawal',
                'mini_url' => '/packageA/member/withdrawal/withdrawal',
                'value' => sprintf("%01.2f", $un_withdraw)],
            [
                'title' => '提现中收入',
                'url' => 'presentationRecord',
                'mini_url' => '/packageA/member/presentationRecord_v2/presentationRecord_v2?orderType=extension',
                'value' => $income && $income->withdrawing ? $income->withdrawing : 0],
            [
                'title' => '团队粉丝',
                'url' => 'myRelationship',
                'mini_url' => '/packageD/member/myRelationship/myRelationship',
                'value' => $total_fans ? : 0
            ],
            [
                'title' => '直推粉丝',
                'url' => 'myRelationship',
                'mini_url' => '/packageD/member/myRelationship/myRelationship',
                'value' => $first_fans ? : 0
            ],
        ];
    }

    /**
     * 收入动态
     * @return array
     */
    public static function incomeDynamic()
    {
        list($start,$end) = self::getSearchTime();
        $income = Income::uniacid()
            ->select(DB::raw('SUM(amount) as total_amount,COUNT(*) as income_count,FROM_UNIXTIME(created_at,"%m%d") as time_str'))
            ->where('member_id',self::$memberId)
            ->whereBetween('created_at',[$start,$end])
            ->groupBy('time_str')
            ->get();
        $x_axis = [];
        $income_count = [];
        $total_amount = [];
        while ($start <= $end) {
            $x_axis[] = date('m/d',$start);
            $data = $income->where('time_str',date('md',$start))->first();
            $income_count[] = $data ? $data['income_count'] : 0;
            $total_amount[] = $data ? $data['total_amount'] : 0;
            $start = strtotime('+1 days',$start);
        }
        return [
            'x_axis' => $x_axis,
            'income_count' => $income_count,
            'total_amount' => $total_amount,
        ];
    }

    /**
     * 收入占比
     * @return array
     */
    public static function incomeProportion()
    {
        if (!empty(self::$request['income_statistic_type'])) {
            list($start,$end) = self::timeSolt(self::$request['income_statistic_type']);
        }
        $incomeConfig = \app\backend\modules\income\Income::current()->getItems();
        $incomeConfig = collect($incomeConfig)->pluck('class')->toArray();
        $income = Income::uniacid()
            ->select('dividend_code','type_name',DB::raw('SUM(amount) as total_amount'))
            ->whereIn('incometable_type', $incomeConfig)
            ->where('member_id',self::$memberId);
        if (!empty($start) && !empty($end)) {
            $income = $income->whereBetween('created_at',[$start,$end]);
        }
        $income = $income->groupBy('incometable_type')
            ->get();

        $income = collect(array_values($income->sortByDesc('total_amount')->all()));
        //集合切割
        $order = $income->splice(5);
        $proportion = [];
        foreach ($income->all() as $item) {
            $type_name = IncomeService::dividendCodeCustomPluginName($item['dividend_code']);
            $proportion[] = [
                'name' => $type_name ? : $item['type_name'],
                'value' => $item['total_amount'] ? : 0,
            ];
        }
        $proportion[] = [
            'name' => '其他',
            'value' => $order->sum('total_amount') ? bcmul($order->sum('total_amount'),1,2) : 0,
        ];
        return $proportion;
    }

    /**
     * 粉丝裂变数据
     * @return array
     */
    public static function fansFission()
    {
        list($start,$end) = self::getSearchTime();
        //每天新增全部统计
        $fans = MemberShopInfo::uniacid()
            ->select(DB::raw('COUNT(*) as new_fans,FROM_UNIXTIME(child_time,"%Y%m%d") as time_str'))
            ->where('parent_id',self::$memberId)
//            ->where('child_time','>',0)
            ->groupBy('time_str')
            ->get();

        $totalFans = round(($fans->sum('new_fans') ? : 0),2);
        $maxHistory = $fans->max('new_fans') ? : 0;
        $totalFans15 = round(($fans->where('time_str','>=',date('Ymd',$start))->sum('new_fans') ? : 0),2);
        $max15 = $fans->where('time_str','>=',date('Ymd',$start))->max('new_fans') ? : 0;
        $x_axis = [];
        $totalFansSeries = [];
        $fansNewSeries = [];
        while ($start <= $end) {
            $x_axis[] = date('m/d',$start);
            $totalFansSeries[] = $fans->where('time_str','<=',date('Ymd',$start))->sum('new_fans') ? : 0;
            $fansNewSeries[] = $fans->where('time_str',date('Ymd',$start))->sum('new_fans') ? : 0;
            $start = strtotime('+1 days',$start);
        }

        return [
            'data' => [
                ['title' => '粉丝总数','value' => $totalFans],
                ['title' => '15日粉丝总数','value' => $totalFans15],
                ['title' => '历史单日最高','value' => $maxHistory],
                ['title' => '15日内单日最高','value' => $max15],
            ],
            'x_axis' => $x_axis,
            'totalFansSeries' => $totalFansSeries,
            'fansNewSeries' => $fansNewSeries,
        ];
    }

    /**
     * 粉丝转化数据
     * @return array
     */
    public static function fansConversion()
    {
        $prefix = app('db')->getTablePrefix();
        list($start,$end) = self::getSearchTime();
        //每天新增全部统计
        $order = ChildrenOfMember::uniacid()
            ->select(DB::raw('COUNT('.$prefix.'yz_order.id) as order_count,SUM(price) as order_price,'
                .'FROM_UNIXTIME('.$prefix.'yz_order.create_time,"%Y%m%d") as time_str'))
            ->leftJoin('yz_order',function ($join) {
                $join->on('yz_order.uid','yz_member_children.child_id')
                    ->whereIn('yz_order.status',[1,2,3]);
            })
            ->where('member_id',self::$memberId)
            ->where('level',1)//直推
            ->groupBy('time_str')
            ->get();

        $totalOrderCount = $order->sum('order_count') ? bcmul($order->sum('order_count'),1,2) : 0;
        $maxOrderCount =  $order->max('order_count') ? : 0;
        $totalOrderPrice = $order->sum('order_price') ? bcmul($order->sum('order_price'),1,2) : 0;
        $maxOrderPrice =  $order->max('order_price') ? : 0;
        $x_axis = [];
        $orderCountSeries = [];
        $orderPriceSeries = [];
        while ($start <= $end) {
            $x_axis[] = date('m/d',$start);
            $orderCountSeries[] = $order->where('time_str',date('Ymd',$start))->sum('order_count') ? : 0;
            $orderPriceSeries[] = $order->where('time_str',date('Ymd',$start))->sum('order_price') ? : 0;
            $start = strtotime('+1 days',$start);
        }

        return [
            'data' => [
                ['title' => '推广订单总数','value' => $totalOrderCount],
                ['title' => '单日最高订单数','value' => $maxOrderCount],
                ['title' => '推广订单总额','value' => $totalOrderPrice],
                ['title' => '单日最高订单总额','value' => $maxOrderPrice],
            ],
            'x_axis' => $x_axis,
            'orderCountSeries' => $orderCountSeries,
            'orderPriceSeries' => $orderPriceSeries,
        ];
    }

    /**
     * 插件入口
     * @return array
     */
    public static function pluginData()
    {
        $memberCenter = new MemberCenterService();
        $arr = $memberCenter->getMemberData(self::$memberId);//获取会员中心页面各入口
        $plugin = $memberCenter->defaultPluginData(self::$memberId);
        foreach ($arr as $key => $item) {
            if (!in_array($key,['is_open','hotel','plugins','ViewSet'])) {
                $plugin = array_merge($plugin,$item);
            }
        }
        unset($arr);

        $returnPlugin = [];
        if ($plugin) {
            $plugin = collect($plugin);
            foreach (self::PLUGIN as $key => $item) {
                $data = $plugin->where('name',$key)->first();
                if ($data) {
                    $data['title'] = $item;
                    $returnPlugin[] = $data;
                }
            }
        }
        return $returnPlugin;
    }

    /**
     * 推广订单
     * @return mixed
     */
    public static function extensionOrder()
    {
        $order = Order::uniacid()
            ->select('yz_order.id','order_sn','goods_price','price','create_time','status')
            ->join('yz_member_children',function ($join) {
                $join->on('yz_order.uid','yz_member_children.child_id')
                    ->where('yz_member_children.member_id',self::$memberId)
                    ->where('level',1);
            })
            ->orderBy('yz_order.id','desc')
            ->paginate(6)->toArray();
        return $order;
    }

    /**
     * 推广粉丝
     * @return mixed
     */
    public static function extensionFans()
    {
        $data = MemberShopInfo::uniacid()
            ->select('mc_members.nickname','mc_members.avatar',DB::raw('uid as child_id,child_time as created_at'))
            ->join('mc_members',function ($join) {
                $join->on('mc_members.uid','yz_member.member_id');
            })
            ->where('yz_member.parent_id',self::$memberId)
//            ->where('child_time','>',1)//直推
            ->orderBy('child_time','desc')
            ->paginate(6)->toArray();

        $uid = array_column($data['data'],'child_id');//取出所有uid
        if ($uid) {
            $order = Order::uniacid()
                ->select('uid',DB::raw('COUNT(id) as order_count,SUM(price) as order_price'))
                ->whereIn('uid',$uid)
                ->whereIn('status',[-1,1,2,3])
                ->where('pay_time','>',0)
                ->groupBy('uid')
                ->get()->toArray();
            $order = array_column($order,null,'uid');
            foreach ($data['data'] as &$value) {
                $value['order_count'] = 0;
                $value['order_price'] = 0;
                if (!empty($order[$value['child_id']])) {
                    $value['order_count'] = $order[$value['child_id']]['order_price'] ? : 0;
                    $value['order_price'] = $order[$value['child_id']]['order_count'] ? : 0;
                }
            }
        }
        return $data;
    }

    private static function getSearchTime()
    {
        $start = Carbon::now()->addDays(-14)->startOfDay()->timestamp;
        $end   = Carbon::now()->endOfDay()->timestamp;
        return [$start,$end];
    }

    private static function timeSolt($type)
    {
        switch ($type) {
            case 1://今日
                $start = Carbon::today()->startOfDay()->timestamp;
                $end   = Carbon::today()->endOfDay()->timestamp;
                break;
            case 2://昨日
                $start = Carbon::yesterday()->startOfDay()->timestamp;
                $end   = Carbon::yesterday()->endOfDay()->timestamp;
                break;
            case 3://本周
                $start = Carbon::now()->startOfWeek()->timestamp;
                $end   = Carbon::now()->endOfWeek()->timestamp;
                break;
            case 4://上周
                $start = Carbon::now()->addWeeks(-1)->startOfWeek()->timestamp;
                $end   = Carbon::now()->addWeeks(-1)->endOfWeek()->timestamp;
                break;
            case 5://本月
                $start = Carbon::now()->startOfMonth()->timestamp;
                $end   = Carbon::now()->endOfMonth()->timestamp;
                break;
            case 6://上月
                $start = Carbon::now()->addMonth(-1)->startOfMonth()->timestamp;
                $end   = Carbon::now()->addMonth(-1)->endOfMonth()->timestamp;
                break;
            default:
                $start = '';
                $end   = '';
        }
        return [$start,$end];
    }

    /**
     * 获取商城中的插件名称自定义设置
     * @return mixed
     */
    private static function getLangSet()
    {
        $lang = Setting::get('shop.lang', ['lang' => 'zh_cn']);
        return $lang[$lang['lang']];
    }

    private static function relation()
    {
        if (!isset(self::$relation)) {
            self::$relation = MemberRelation::uniacid()->first();
        }
        return self::$relation;
    }

    /**
     * 关系链是否开启
     * @return bool
     */
    private static function isOpenRelation()
    {
        $relation = self::relation();
        if (!is_null($relation) && $relation->status == 1) {
            return true;
        }
        return false;
    }

    private static function getSharePageStatus()
    {
        $relation = self::relation();
        if (!is_null($relation) && $relation->share_page == 1) {
            return true;
        }
        return false;
    }

    /**
     * 收入页面配置 config
     * @return mixed
     */
    private static function getIncomePageConfig()
    {
        return \app\backend\modules\income\Income::current()->getPageItems();
    }

    private static function getTotalIncome()
    {
        if (!isset(self::$total_income)) {
            $incomeConfig = \app\backend\modules\income\Income::current()->getItems();

            $incomeConfig = collect($incomeConfig)->pluck('class')->toArray();

            self::$total_income = Income::uniacid()
                ->select('member_id', 'incometable_type',
                    DB::raw('SUM(amount) as total_amount,SUM(IF(status=0,amount,0)) as usable_total'))
                ->whereIn('incometable_type', $incomeConfig)
                ->where('member_id',self::$memberId)
                ->groupBy('incometable_type', 'member_id')
                ->get();
        }
        return self::$total_income;
    }

    private static function getService()
    {
        //1.商城客服设置
        $shopSet = Setting::get('shop.shop');
        $cservice = $shopSet['cservice'];
        if (request()->type == 2) {
            $cservice = $shopSet['cservice_mini'];
        }
        $shop_cservice = !empty($cservice) ? $cservice : '';

        //2.客服插件设置
        if (app('plugins')->isEnabled('customer-service')) {
            $set = array_pluck(\Setting::getAllByGroup('customer-service')->toArray(), 'value', 'key');
            if ($set['is_open'] == 1) {
                if (request()->type == 2) {
                    $arr = [
                        'cservice'=>$set['mini_link']?:$shop_cservice,
                        'customer_open'=>$set['mini_open'],
                        'service_QRcode' => yz_tomedia($set['mini_QRcode']),
                        'service_mobile' => $set['mini_mobile']
                    ];
                }else{
                    $arr = [
                        'cservice'=>$set['link']?:$shop_cservice,
                        'service_QRcode' => yz_tomedia($set['QRcode']),
                        'service_mobile' => $set['mobile']
                    ];
                }
                $alonge_cservice = $arr;
            }
        }
        return !empty($alonge_cservice)?$alonge_cservice:$shop_cservice;

    }

    /**
     * 登陆会员是否是推客
     * @return bool
     */
    private static function isAgent()
    {
        return MemberModel::isAgent();
    }
}
