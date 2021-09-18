<?php
/**
 * Created by PhpStorm.
 * Author: 芸众商城 www.yunzshop.com
 * Date: 2017/4/1
 * Time: 下午4:37
 */

namespace app\frontend\modules\member\services;

use app\common\modules\shop\MemberCenterConfig;
use app\common\services\member\MemberCenterService;
use app\frontend\modules\member\models\MemberFavorite;
use app\frontend\modules\member\models\MemberHistory;

class MemberCenterDataService
{
    public $apiData;

    public function __construct($apiData = null)
    {
        $this->apiData = $apiData;
    }

    public function getEnablePlugins()
    {
        $memberId = \YunShop::app()->getMemberId();
        $memberCenter = new MemberCenterService();
        $arr = $memberCenter->getMemberData($memberId);//获取会员中心页面各入口
        $arr['plugins'] = [];
        $plugin = $memberCenter->defaultPluginData($memberId);
        foreach ($arr as $key => $item) {
            if (!in_array($key,['is_open','hotel','plugins','ViewSet'])) {
                $plugin = array_merge($plugin,$item);
                unset($arr[$key]);
            }
        }

        $arr['plugin_head'] = $this->getPluginHead($memberId);

        if ($plugin) {
            $plugin = collect($plugin);
            foreach ($memberCenter->sortPluginData() as $item) {
                $data = $plugin->where('name',$item)->first();
                if ($data) {
                    $arr['plugins'][] = $data;
                }
                if (count($arr['plugins']) >= 19) {
                    break;
                }
            }
        }
        return $arr;
    }

    public function getPluginHead($memberId)
    {
        $head = [
            [
                'title' => '商品收藏',
                'class' => 'icon-fontclass-shoucang',
                'value' => MemberFavorite::getFavoriteCount($memberId)?:0,
                'mini_url'=> "/packageD/member/collection/collection",
                'url' => "collection"
            ],
            [
                'title' => '浏览记录',
                'class' => 'icon-fontclass-liulan',
                'value' => MemberHistory::getMemberHistoryCount($memberId)?:0,
                'mini_url'=> "/packageD/member/footprint/footprint",
                'url' => "footprint"
            ],
        ];

        $set = \Setting::get('plugin.instation-message');
        if (app('plugins')->isEnabled('instation-message') && !empty($set['is_open'])) {
            $head[] = [
                'title' => '消息',
                'class' => 'icon-fontclass-xiaoxi',
                'value' => \Yunshop\InstationMessage\models\InstationMessageDataModel::where('uniacid',intval(\YunShop::app()->uniacid))
                    ->where('member_id',intval($memberId))
                    ->where('is_read',intval(1))
                    ->count()?:0,
                'mini_url'=> '/packageE/stationNotice/stationNotice',
                'url' => "stationNotice"
            ];
        }
        return $head;
    }

    /**
     * @param $code
     * @param bool $returnNav 导航
     * @return array
     */
    public function getPluginData($code,$returnNav = true)
    {
        $nav = [];
        $config = MemberCenterConfig::current()->getItem('plugin_data');
        $config = collect($config)->sortBy('sort')->all();
        if ($returnNav) {
            foreach ($config as $key => $item) {
                $class = new $item['class'];
                if (!($class instanceof MemberCenterPluginBaseService)) {
                    continue;
                }
                if (!$class->getEnabled()) {
                    continue;
                }
                $nav[] = [
                    'name' => $item['name'],
                    'code' => $item['code'],
                ];
            }
        }

        $data = [];
        if (!$code) {
            //默认寻找第一个可以显示的导航
            foreach ($config as $key => $item) {
                $class = new $item['class'];
                if (!($class instanceof MemberCenterPluginBaseService)) {
                    continue;
                }
                if (!$class->getEnabled()) {
                    continue;
                }
                $find = $item;
                break;
            }
        } else {
            $find = collect($config)->where('code',$code)->first();
        }
        if (isset($find)) {
            $class = new $find['class'];
            if ($class instanceof MemberCenterPluginBaseService && $class->getEnabled()) {
                $class->init(request());
                $data = $class->getData();
            }
        }
        return ['nav' => $nav,'data' => $data];
    }

    public function getService($integrated)
    {
        //1.商城客服设置
        $shopSet = \Setting::get('shop.shop');
        $shop_cservice= $shopSet['cservice']?:'';

        //2.客服插件设置
        if (app('plugins')->isEnabled('customer-service')) {
            $set = array_pluck(\Setting::getAllByGroup('customer-service')->toArray(), 'value', 'key');
            if ($set['is_open'] == 1) {
                if (request()->type == 2) {
                    $arr = [
                        'customer_open'=>$set['mini_open'],
                        'service_QRcode' => yz_tomedia($set['mini_QRcode']),
                        'service_mobile' => $set['mini_mobile']
                    ];
                }else{
                    $arr = [
                        'cservice'=>$set['link'],
                        'service_QRcode' => yz_tomedia($set['QRcode']),
                        'service_mobile' => $set['mobile']
                    ];
                }
                $alonge_cservice = $arr;
            }
        }
        return !empty($alonge_cservice)?$alonge_cservice:$shop_cservice;

    }
}