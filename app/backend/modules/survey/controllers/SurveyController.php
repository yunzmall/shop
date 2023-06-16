<?php
/**
 * Created by PhpStorm.
 * User: yunzhong
 * Date: 2019/7/1
 * Time: 14:55
 */

namespace app\backend\modules\survey\controllers;


use app\backend\models\Withdraw;
use app\backend\modules\member\models\MemberShopInfo;
use app\backend\modules\menu\Menu;
use app\common\components\BaseController;
use app\common\helpers\Cache;
use app\common\models\Goods;
use app\common\models\Order;
use app\common\models\Setting;
use app\common\services\CollectHostService;
use app\common\services\System;
use app\host\HostManager;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use mysql_xdevapi\Exception;
use Predis\Connection\ConnectionException;
use Yunshop\BrowseFootprint\models\BrowseFootprintModel;
use Yunshop\BrowseFootprint\services\IndexPageService;
use Yunshop\Love\Common\Models\MemberShop;
use EasyWeChat\Factory;


class SurveyController extends BaseController
{
    public function index()
    {
        return view('survey.index');
    }

    private function _allData()
    {
        ini_set('memory_limit',-1);

        (new CollectHostService(request()->getHttpHost()))->handle();

        $notSearchPlugin=[158,161];// 抖音cps、聚推联盟商品不显示
        //销售量前10条数据
        $goods = Goods::uniacid()->whereNotIn('plugin_id',$notSearchPlugin)->orderBy('real_sales', 'desc')->offset(0)
            ->take(10)->select('id', 'title', 'real_sales', 'created_at')->get();

        //订单数据
        $start_today = strtotime(Carbon::now()->startOfDay()->format('Y-m-d H:i:s'));
        $end_today = strtotime(Carbon::now()->endOfDay()->format('Y-m-d H:i:s'));

        //不统计的订单plugin_id
        $without_count_plugin_id = \app\common\modules\shop\ShopConfig::current()->get('without_count_order_plugin_id') ?: [];
        //获取已开启的且不在不统计的订单的插件id
        $pluginIds=array_column((new \app\backend\modules\order\services\OrderViewService)->getOrderType(),'plugin_id');
        $pluginIds=array_filter($pluginIds,function($item)use($without_count_plugin_id){
            return !in_array($item,$without_count_plugin_id);
        });

        $where = [['uniacid', \YunShop::app()->uniacid]];
        $orderResult = DB::table('yz_order')->selectRaw('status, count(status) as total')->where($where)->whereIn('plugin_id',$pluginIds)->whereIn('status', [0, 1])->groupBy('status')->get();

        foreach ($orderResult as $rows) {
            //待支付订单
            if ($rows['status'] == 0) {
                $to_be_paid = $rows['total'];
            }

            //待发货订单
            if ($rows['status'] == 1) {
                $to_be_shipped = $rows['total'];
            }
        }
        //今日订单数据
        $today_order = DB::table('yz_order')->selectRaw('sum(price) as money , count(id) as total')->where($where)->whereIn('plugin_id',$pluginIds)->whereBetween('created_at', [$start_today, $end_today])->whereIn('status', [1, 2, 3])->first();

        //会员总数
        $member = DB::table('mc_members')
            ->select([DB::raw('count(1) as total')])
            ->where('mc_members.uniacid', \YunShop::app()->uniacid)
            ->join('yz_member', 'mc_members.uid', '=', 'yz_member.member_id')
            ->whereNull('yz_member.deleted_at')
            ->first();

        //=============获取图表数据

        $all_data = [
            'goods' => $goods,
            'member_count' => $member['total'],
            'member_count_icon' => 'icon-fontclass-renshu',
            'chart_data' => $this->getOrderData(),
            'system_icon' => 'icon-fontclass-deng',
            'order' => [
                'to_be_paid' => $to_be_paid ?: 0,
                'to_be_shipped' => $to_be_shipped ?: 0,
                'today_order_money' => $today_order['money'] ?: 0,
                'today_order_count' => $today_order['total'] ?: 0,
                'paid_icon' => 'icon-ht_content_tixian',
                'shipped_icon' => 'icon-ht_content_order',
                'order_money_icon' => 'icon-fontclass-yue (1)',
                'order_count_icon' => 'icon-fontclass-shangpindingdan (1)'
            ],
            'entrance' => $this->getEntrance(),
            'guide' => $this->getBasicGuidance(),
            'withdrawal' => $this->getWithdrawal(),
            'visitor' => $this->getVisitor(),
            'is_enabled_statistics' => 0,
            'order_url' => '',
            'sales_url' => '',
        ];

        if (app('plugins')->isEnabled('shop-statistics')) {
            $all_data['is_enabled_statistics'] = 1;
            $all_data['order_url'] = yzWebFullUrl('plugin.shop-statistics.backend.order.show');
            $all_data['sales_url'] = yzWebFullUrl('plugin.shop-statistics.backend.goods.show');
        }

        return $all_data;
    }

    public function survey()
    {
//        dd($this->_allData());
        return $this->successJson('成功', $this->_allData());
    }

    private function getOrderData()
    {
        $times = $this->timeRangeItems();
        $result = [];
        foreach ($times as $time) {
            $item['total'] = $this->orderTotals(null, 'create_time', $time) ?: 0;
            $item['complete'] = $this->orderTotals(3, 'finish_time', $time) ?: 0;
            $item['deliver_goods'] = $this->orderTotals(2, 'send_time', $time) ?: 0;
            $item['date'] = $time;
            $result[] = $item;
        }
        return $result;
    }

    /**
     * 获取一星期的时间
     * @return array
     */
    public function timeRangeItems()
    {
        $result = [];
        for ($i = 6; $i > -1; $i--) {
            Carbon::now()->subDay($i)->format('Y-m-d');
            $result[] = Carbon::now()->subDay($i)->format('Y-m-d');
        }
        return $result;
    }

    private $orderTotals;

    private function orderTotals($status, $timeField, $date)
    {
        if (!isset($this->orderTotals[$timeField])) {
            $allDate = Order::uniacid()->getQuery()
                ->select(DB::raw("count(1) as total, FROM_UNIXTIME(" . $timeField . ",'%Y-%m-%d') as date_str"))
                ->whereBetween($timeField, [Carbon::now()->subDay(6)->startOfDay()->timestamp, Carbon::now()->endOfDay()->timestamp])
                ->groupBy(DB::raw('YEAR(date_str), MONTH(date_str), DAY(date_str)'));
            if (isset($status)) {
                $allDate->where('status', $status);
            }
            $allDate = $allDate->get();
            $this->orderTotals[$timeField] = [];
            foreach ($allDate as $item) {
                $this->orderTotals[$timeField][$item['date_str']] = $item['total'];
            }
        }
        return $this->orderTotals[$timeField][$date];
    }



    private function getSystemStatus()
    {
        return (new System())->index();
    }

    /**
     * 基础功能指引
     * @return array
     */
    private function getBasicGuidance()
    {
        $guide_list = $this->getGuideList();
        foreach ($guide_list as $k => &$v) {
            foreach ($v['list'] as $k2 => &$v2) {
                if (isset($v2['supper_admin']) and $v2['supper_admin'] === 1) {
                    if (!(\YunShop::app()->role === 'founder')) {
                        unset($guide_list[$k]['list'][$k2]);
                        continue;
                    }
                }

                $v2['url'] = yzWebFullUrl($v2['route'], $v2['param'] ?: []);
                $v2['is_enabled'] = 0;

                if ($v2['is_plugin'] == 1) {
                    $route_array = explode('.', $v2['route']);
                    if (app('plugins')->isEnabled($route_array[1])) {
                        $v2['is_enabled'] = 1;
                    } else {
                        $v2['url'] = '';
                    }
                }

                if (isset($v2['special_param']) and $v2['special_param']) {
                    $v2['url'] .= $v2['special_param'];
                }

                unset($v2['route'], $v2['param'], $v2['special_param']);
            }
        }
        $data = array(
            'list' => $guide_list
        );
        return $data;
    }

    /**
     * 主要入口
     * @return array
     */
    private function getEntrance()
    {
        $data = array(
            'home_url' => yzAppFullUrl('home'),
            'home_code' => '',
            'more_home_url' => yzWebFullUrl('setting.shop.entry'),
            'is_enabled_mini_app' => 0,
            'is_mini_app_config' => 0,
            'mini_app_code' => '',
            'is_enabled_customer_service' => 0,
            'customer_service_url' => '',
            'is_enabled_contract' => 0,
            'is_contract_config' => 0,
            'contract_url' => '',
        );

        // 商城首页二维码
        $code = new \app\common\helpers\QrCodeHelper($data['home_url'], 'app/public/qr/home/' . \YunShop::app()->uniacid);
        if ($home_code = $code->url()) {
            $data['home_code'] = $home_code;
        } else $data['home_code_error'] = '生成网页二维码失败';

        // 小程序二维码
        if (app('plugins')->isEnabled('min-app')) {
            $data['is_enabled_mini_app'] = 1;
            $setting = \Setting::get('plugin.min_app');
            if ($setting['switch'] and $setting['key'] and $setting['secret']) {
                $data['is_mini_app_config'] = 1;
                if (!(($mini_code = Cache::get('survey_mini_app_code')) and file_exists($mini_code))) {

                    // 生成小程序二维码，图片保存到storage目录
                    $config = [
                        'app_id' => $setting['key'],
                        'secret' => $setting['secret'],
                    ];
                    $min_url = 'packageG/index/index';
                    try {
                        $app = Factory::miniProgram($config);
                        $response = $app->app_code->getUnlimit('scene-value', ['page' => $min_url, 'width' => 600]);

                        if (is_array($response) && isset($response['errcode'])) {
                            throw new \Exception('生成小程序码失败,' . $response['errcode'] . ':' . $response['errmsg']);
                        }

                        if ($response instanceof \EasyWeChat\Kernel\Http\StreamResponse) {
                            if (config('app.framework') == 'platform') {
                                $file_path = base_path('storage/app/public/qr/home');
                            } else {
                                $file_path = dirname(dirname(base_path())) . '/attachment/app/public/qr/home';
                            }
                            $filename = $response->saveAs($file_path, 'home_mini_code_' . \YunShop::app()->uniacid);
                        }

                        if (isset($filename)) {
                            Cache::put('survey_mini_app_code', $file_path . '/' . $filename, 60);
                            $data['mini_app_code'] = yz_tomedia($file_path . '/' . $filename);
                        } else {
                            $data['mini_app_code_error'] = '保存小程序码失败';
                        }
                    } catch (\Exception $e) {
                        $data['mini_app_code_error'] = '生成小程序码失败,请检查配置';
                    }
                } else {
                    $data['mini_app_code'] = yz_tomedia($mini_code);
                }
            } else {
                $data['is_mini_app_config'] = 0;
            }
        }

        // 客服登录
        if (app('plugins')->isEnabled('yunqian-pc')) {
            $data['is_enabled_customer_service'] = 1;
            $domain = request()->getSchemeAndHttpHost();
            $data['customer_service_url'] = $domain . "/addons/yun_shop/plugins/yunqian-pc/public/index.html?i=" . \YunShop::app()->uniacid;
            if (config('app.framework') == 'platform') {
                $data['customer_service_url'] = $domain . "/plugins/yunqian-pc/platform/index.html?i=" . \YunShop::app()->uniacid;
            }
        }

        // 电子合同管理
        if (app('plugins')->isEnabled('yun-sign')) {
            $data['is_enabled_contract'] = 1;
            $setting = \Setting::get('plugin.yun-sign');
            if ($setting['short_url_front']) {
                $data['is_contract_config'] = 1;
                $data['contract_url'] = $setting['short_url_front'];
            }
        }

        return $data;
    }

    private function deleteCode()
    {

        // 删除旧数据
        Setting::uniacid()->where('group', 'shop')->where('key', 'like', 'mini_code%')->delete();
    }

    /**
     * 提现动态
     * @return array
     */
    private function getWithdrawal()
    {
        $list = Withdraw::with(['hasOneMember' => function ($query) {
            return $query->select('uid', 'nickname', 'avatar');
        }])->select('id', 'member_id', 'created_at', 'amounts')
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        $data = array(
            'url' => yzWebFullUrl('withdraw.records'),
            'list' => $list
        );
        return $data;
    }

    /**
     * 访客播报
     * @return array
     */
    private function getVisitor()
    {
        $data = [
            'is_enabled' => 0,
            'url' => '',
            'list' => array()
        ];

        if (app('plugins')->isEnabled('browse-footprint')) {
            $data['is_enabled'] = 1;
            $data['url'] = yzWebFullUrl('plugin.browse-footprint.admin.index.index');
            $prefix = app('db')->getTablePrefix();
            $uniacid = \YunShop::app()->uniacid;
            $list = BrowseFootprintModel::select('created_at', 'port_type', 'ip', 'ip_name', 'member_id', 'cookie', 'day', 'url')
                ->from(DB::raw("(select * from {$prefix}yz_browse_footprint where uniacid = $uniacid order by id desc limit 5000) as {$prefix}yz_browse_footprint"))
                ->with([
                    'hasOneMember' => function ($q) {
                        $q->select('uid', 'nickname', 'avatar', 'realname');
                    }
                ])
                ->search([])
                ->groupBy('cookie', 'member_id')
                ->orderBy('created_at', 'desc')
                ->orderBy('id', 'desc')
                ->paginate(20);

            // 判断新老访客
            $cookie_arr = $list->pluck('cookie')->toArray();
            $member_arr = $list->pluck('member_id')->toArray();
            $merge_arr = array_merge($cookie_arr, $member_arr);
            $day_arr = BrowseFootprintModel::selectRaw('min(day) as min_day,cookie,member_id')
                ->from(DB::raw("(select * from {$prefix}yz_browse_footprint where uniacid = $uniacid order by id desc limit 5000) as {$prefix}yz_browse_footprint"))
                ->where(function ($query) use ($merge_arr) {
                    foreach ($merge_arr as $item) {
                        if ($item == null) {
                            continue;
                        }
                        if (is_numeric($item)) {
                            $query->orWhere('member_id', $item);
                        } else {
                            $query->orWhere('cookie', $item);
                        }
                    }
                })
                ->groupBy('cookie')
                ->get();

            $member_ids = $real_day_arr = [];
            foreach ($day_arr as $item) {
                if ($item->member_id != 0 && in_array($item->member_id, $member_ids)) {
                    $bccomp = bccomp($item->min_day, $real_day_arr[$item->member_id]);//左边大=》1  右边大=》-1
                    //更小就替换
                    if (!$bccomp) {
                        $real_day_arr[$item->member_id] = $item->min_day;
                    }
                    continue;//替换中断
                }

                $real_day_arr[$item->member_id] = $item->min_day;
                $member_ids[] = $item->member_id;
            }
            unset($real_day_arr[0]);//去除0的数据

            $portion_day_arr = $day_arr->pluck('min_day', 'cookie');

            foreach ($list as $key => $item) {
                $list_data[] = [
                    'access_time' => $item->created_at->toDateTimeString(),
                    'cookie_type' => $this->getCookieType($item->day, $item->cookie, $portion_day_arr, $real_day_arr, $item->member_id),
                    'name' => $item->hasOneMember->username ?: '',
                    'avatar_image' => $item->hasOneMember->avatar_image ?: '',
                    'uid' => $item->hasOneMember->uid
                ];
            }

            $data['list'] = $list_data ?: [];
        }

        return $data;
    }

    /**
     * 判断新老访客
     * @param $item_day
     * @param $item_cookie
     * @param $portion_day_arr
     * @param $real_day_arr
     * @param $item_member_id
     * @return string
     */
    private function getCookieType($item_day, $item_cookie, $portion_day_arr, $real_day_arr, $item_member_id)
    {
        //存在
        if (!empty($real_day_arr[$item_member_id])) {
            $times[$item_cookie] = $real_day_arr[$item_member_id];
        } else {
            $times[$item_cookie] = $portion_day_arr[$item_cookie];
        }

        return $item_day == $times[$item_cookie] ? 'new' : 'old';

    }

    // 基础功能指引
    private function getGuideList()
    {
        $basic_guidance = array(
            [
                'title' => '基础设置',
                'list' => [
                    [
                        'name' => '安装应用',
                        'route' => 'plugin.plugins-market.Controllers.new-market.show',
                        'is_plugin' => 1,
                        'icon' => 'icon-fontclass-anzhuang',
                        'supper_admin' => 1,
                    ],
                    [
                        'name' => '启动应用',
                        'route' => 'plugins.get-plugin-data',
                        'is_plugin' => 0,
                        'icon' => 'icon-fontclass-mendianfenlei',
                        'supper_admin' => 1,
                    ],
                    [
                        'name' => '设置商城信息',
                        'route' => 'setting.shop.index',
                        'is_plugin' => 0,
                        'icon' => 'icon-fontclass-mendianguanli'
                    ],
                    [
                        'name' => '设置短信',
                        'route' => 'setting.shop.sms',
                        'is_plugin' => 0,
                        'icon' => 'icon-fontclass-yanzhengma'
                    ],
                    [
                        'name' => '设置物流查询',
                        'route' => 'setting.shop.express-info',
                        'is_plugin' => 0,
                        'icon' => 'icon-massage_trade_logistics'
                    ],
                    [
                        'name' => '添加操作员',
                        'route' => 'user.user.index',
                        'is_plugin' => 0,
                        'icon' => 'icon-massage_client'
                    ],
                    [
                        'name' => '前端入口',
                        'route' => 'setting.shop.entry',
                        'is_plugin' => 0,
                        'icon' => 'icon-wj_record'
                    ],
                    [
                        'name' => '接入微信公众号',
                        'route' => 'plugin.wechat.admin.setting.setting',
                        'is_plugin' => 1,
                        'icon' => 'icon-fontclass-gongzonghao1'
                    ],
                    [
                        'name' => '接入微信小程序',
                        'route' => 'plugin.min-app.Backend.Controllers.base-set',
                        'is_plugin' => 1,
                        'icon' => 'icon-all_smallprogram'
                    ],
                    [
                        'name' => '支付方式',
                        'route' => 'setting.shop.pay',
                        'is_plugin' => 0,
                        'icon' => 'icon-fontclass-zhifufangshi'
                    ],
                    [
                        'name' => '微信模板消息',
                        'route' => 'setting.shop.notice',
                        'is_plugin' => 0,
                        'icon' => 'icon-xuanchuantuiguang'
                    ]
                ]
            ],
            [
                'title' => '商品与页面',
                'list' => [
                    [
                        'name' => '商品分类层级',
                        'route' => 'setting.shop.category',
                        'is_plugin' => 0,
                        'icon' => 'icon-fontclass-fenleicengji'
                    ],
                    [
                        'name' => '商品分类设置',
                        'route' => 'goods.category.index',
                        'is_plugin' => 0,
                        'icon' => 'icon-fontclass-anzhuang'
                    ],
                    [
                        'name' => '商品品牌设置',
                        'route' => 'goods.brand.index',
                        'is_plugin' => 0,
                        'icon' => 'icon-fontclass-shouchu'
                    ],
                    [
                        'name' => '配送模板设置',
                        'route' => 'goods.dispatch.index',
                        'is_plugin' => 0,
                        'icon' => 'icon-fontclass-jinhuo'
                    ],
                    [
                        'name' => '发布商品',
                        'route' => 'goods.goods.create',
                        'is_plugin' => 0,
                        'icon' => 'icon-fontclass-tuanduifenxiaoshang'
                    ],
                    [
                        'name' => '商品助手',
                        'route' => 'plugin.goods-assistant.admin.import.taobao',
                        'is_plugin' => 1,
                        'icon' => 'icon-fontclass-mendianshangpin'
                    ],
                    [
                        'name' => '装修页面',
                        'route' => 'plugin.decorate.admin.page.get-list',
                        'is_plugin' => 1,
                        'param' => [
                            'i' => \YunShop::app()->uniacid
                        ],
                        'special_param' => '#/home',
                        'icon' => 'icon-energetank_vipcard'
                    ],
                    [
                        'name' => '设置底部导航',
                        'route' => 'plugin.decorate.admin.page.get-list',
                        'is_plugin' => 1,
                        'param' => [
                            'i' => \YunShop::app()->uniacid
                        ],
                        'special_param' => '#/bottom_navigation_list',
                        'icon' => 'icon-fontclass-dibudaohang'
                    ],
                    [
                        'name' => '选择页面模板',
                        'route' => 'plugin.decorate.admin.page.get-list',
                        'is_plugin' => 1,
                        'param' => [
                            'i' => \YunShop::app()->uniacid
                        ],
                        'special_param' => '#/diy_template_manage',
                        'icon' => 'icon-ht_operation_add'
                    ],
                    [
                        'name' => '模板市场',
                        'route' => 'plugin.decorate.admin.decorate-diy.index',
                        'is_plugin' => 1,
                        'icon' => 'icon-ht_content_apply'
                    ],
                    [
                        'name' => '自定义文字',
                        'route' => 'setting.lang.index',
                        'is_plugin' => 0,
                        'icon' => 'icon-ht_textstyle_blod'
                    ]
                ]
            ],
            [
                'title' => '会员基础功能',
                'list' => [
                    [
                        'name' => '手机验证码登录',
                        'route' => 'setting.shop.member',
                        'is_plugin' => 0,
                        'icon' => 'icon-fontclass-yanzhengma'
                    ],
                    [
                        'name' => '强制绑定手机号',
                        'route' => 'setting.shop.member',
                        'is_plugin' => 0,
                        'icon' => 'icon-fontclass-shoujihao'
                    ],
                    [
                        'name' => '会员等级设置',
                        'route' => 'setting.shop.member',
                        'is_plugin' => 0,
                        'icon' => 'icon-huiyuanguanli-copy'
                    ],
                    [
                        'name' => '邀请码设置',
                        'route' => 'setting.shop.member',
                        'is_plugin' => 0,
                        'icon' => 'icon-fontclass-yaoqingma'
                    ],
                    [
                        'name' => '会员注册资料',
                        'route' => 'setting.form.index',
                        'is_plugin' => 0,
                        'icon' => 'icon-dingdan1'
                    ],
                    [
                        'name' => '会员注册协议/页面',
                        'route' => 'setting.shop.register',
                        'is_plugin' => 0,
                        'icon' => 'icon-fontclass-shouyintaidingdan'
                    ],
                    [
                        'name' => '会员等级管理',
                        'route' => 'member.member-level.index',
                        'is_plugin' => 0,
                        'icon' => 'icon-fontclass-huiyuandengji'
                    ],
                    [
                        'name' => '关系链设置',
                        'route' => 'member.member-relation.index',
                        'is_plugin' => 0,
                        'icon' => 'icon-all_link'
                    ],
                    [
                        'name' => '企业微信管理',
                        'route' => 'plugin.work-wechat-platform.admin.crop.index',
                        'is_plugin' => 1,
                        'icon' => 'icon-fontclass-qiyeweix'
                    ],
                    [
                        'name' => '导入会员',
                        'route' => 'plugin.import-members.Backend.Modules.Member.Controllers.page',
                        'is_plugin' => 1,
                        'icon' => 'icon-fontclass-fenxiangjiang'
                    ],
                    [
                        'name' => '会员等级同步',
                        'route' => 'plugin.level-cogradient.admin.set.index',
                        'is_plugin' => 1,
                        'icon' => 'icon-fontclass-huiyuantongbu'
                    ],
                    [
                        'name' => '会员标签',
                        'route' => 'plugin.member-tags.Backend.controllers.tag.index',
                        'is_plugin' => 1,
                        'icon' => 'icon-goods-class'
                    ],
                ]
            ],
            [
                'title' => '订单与财务',
                'list' => [
                    [
                        'name' => '自动关闭未付款订单',
                        'route' => 'setting.shop.trade',
                        'is_plugin' => 0,
                        'icon' => 'icon-fontclass-wenzhangzhongxin'
                    ],
                    [
                        'name' => '自动收货',
                        'route' => 'setting.shop.trade',
                        'is_plugin' => 0,
                        'icon' => 'icon-fontclass-shouhuo'
                    ],
                    [
                        'name' => '交易设置（退款）',
                        'route' => 'setting.shop.trade',
                        'is_plugin' => 0,
                        'icon' => 'icon-fontclass-fanli'
                    ],
                    [
                        'name' => '发票设置',
                        'route' => 'setting.shop.trade',
                        'is_plugin' => 0,
                        'icon' => 'icon-fontclass-fufeijilu'
                    ],
                    [
                        'name' => '支付协议',
                        'route' => 'setting.shop.trade',
                        'is_plugin' => 0,
                        'icon' => 'icon-fontclass-shouquanshu'
                    ],
                    [
                        'name' => '商城电子合同',
                        'route' => 'plugin.shop-esign.admin.set.index',
                        'is_plugin' => 1,
                        'icon' => 'icon-yq_hetongzhongxin'
                    ],
                    [
                        'name' => '支付密码',
                        'route' => 'password.setting.index',
                        'is_plugin' => 0,
                        'icon' => 'icon-fontclass-mima'
                    ],
                    [
                        'name' => '批量发货',
                        'route' => 'order.batch-send.index',
                        'is_plugin' => 0,
                        'icon' => 'icon-fontclass-fahuo'
                    ],
                    [
                        'name' => '快递助手',
                        'route' => 'plugin.exhelper.admin.print-once.search',
                        'is_plugin' => 1,
                        'icon' => 'icon-ht_content_goods'
                    ],
                    [
                        'name' => '提现设置',
                        'route' => 'finance.withdraw-set.see',
                        'is_plugin' => 0,
                        'icon' => 'icon-fontclass-shezhi2'
                    ],
                    [
                        'name' => '提现记录',
                        'route' => 'withdraw.records',
                        'is_plugin' => 0,
                        'icon' => 'icon-fontclass-huodongshuoming'
                    ],
                    [
                        'name' => '批量充值',
                        'route' => 'excelRecharge.page.index',
                        'is_plugin' => 0,
                        'icon' => 'icon-fontclass-yue'
                    ],
                ]
            ],
            [
                'title' => '常用营销活动',
                'list' => [
                    [
                        'name' => '优惠券',
                        'route' => 'coupon.coupon.index',
                        'is_plugin' => 0,
                        'icon' => 'icon-fontclass-youhuiq'
                    ],
                    [
                        'name' => '满额减',
                        'route' => 'enoughReduce.index.index',
                        'is_plugin' => 0,
                        'icon' => 'icon-fontclass-huiyuankaquanrukou'
                    ],
                    [
                        'name' => '满额包邮',
                        'route' => 'enoughReduce.index.index',
                        'is_plugin' => 0,
                        'icon' => 'icon-mane01'
                    ],
                    [
                        'name' => '余额设置',
                        'route' => 'finance.balance-set.see',
                        'is_plugin' => 0,
                        'icon' => 'icon-fontclass-tichengmingxi'
                    ],
                    [
                        'name' => '积分设置',
                        'route' => 'finance.point-set.index',
                        'is_plugin' => 0,
                        'icon' => 'icon-fontclass-lanniujifen'
                    ],
                    [
                        'name' => '推广海报',
                        'route' => 'plugin.new-poster.admin.poster.index',
                        'is_plugin' => 1,
                        'icon' => 'icon-jk_haibao'
                    ],
                    [
                        'name' => '每日签到',
                        'route' => 'plugin.sign.Backend.Modules.Sign.Controllers.sign',
                        'is_plugin' => 1,
                        'icon' => 'icon-daqia'
                    ],
                    [
                        'name' => '幸运大抽奖',
                        'route' => 'plugin.lucky-draw.admin.controllers.activity.index',
                        'is_plugin' => 1,
                        'icon' => 'icon-fontclass-choujiang'
                    ],
                    [
                        'name' => '素材中心',
                        'route' => 'plugin.material-center.admin.material.index',
                        'is_plugin' => 1,
                        'icon' => 'icon-ht_show_formicon'
                    ],
                    [
                        'name' => '新人奖',
                        'route' => 'plugin.new-member-prize.admin.controllers.activity.index',
                        'is_plugin' => 1,
                        'icon' => 'icon-gudingjiangli'
                    ],
                    [
                        'name' => '定金阶梯团',
                        'route' => 'plugin.deposit-ladder.backend.activity.show',
                        'is_plugin' => 1,
                        'icon' => 'icon-fontclass-jieti'
                    ],
                    [
                        'name' => '短视频',
                        'route' => 'plugin.video-share.admin.set',
                        'is_plugin' => 1,
                        'icon' => 'icon-fontclass-ship'
                    ],

                ]
            ]
        );
        return $basic_guidance;
    }

}
