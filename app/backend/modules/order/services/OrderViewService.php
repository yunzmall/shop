<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/12/3
 * Time: 14:13
 */

namespace app\backend\modules\order\services;


use app\backend\modules\order\services\type\OrderViewBase;
use app\common\models\PayType;
use app\common\models\PayTypeGroup;
use app\common\services\PayFactory;

class OrderViewService
{

    protected $viewSet;

    public function __construct()
    {

    }

    public function getViewSet()
    {
        if (!isset($this->viewSet)) {
            $this->viewSet = $this->_getViewSet();
        }
        return $this->viewSet;

    }
    protected function _getViewSet()
    {
        $viewSet= collect([]);

        $configs = \app\common\modules\shop\ShopConfig::current()->get('shop-foundation.order-list.type');
        // 从配置文件中载入,按优先级排序
        $viewConfigs = collect($configs)->sortBy('priority');
        //遍历取到第一个通过验证的订单类型返回
        foreach ($viewConfigs as $configItem) {
            //通过验证返回
            if (class_exists($configItem['view'])) {
                $viewSet->push((new $configItem['view']));
            }

        }
        return $viewSet;

    }

    public function getOrderType()
    {

        $items = $this->getViewSet()->map(function(OrderViewBase $view) {

            $result['name'] = $view->getName();
            $result['need_display'] = $view->needDisplay();
            $result['route'] = $view->getRoute();
            $result['plugin_id'] = $view->getPluginId();
            $result['code'] = $view->getcode();

            return $result;
        })->toArray();

        return $items;
    }


    public function importVue()
    {

        $routes = $this->getViewSet()->filter(function (OrderViewBase $view) {
            return $view->getVueFilePath() &&  $view->getVuePrimaryName();
        })->map(function(OrderViewBase $view) {
            return [
                'path'=> $view->getVueFilePath(),
                'primary' => $view->getVuePrimaryName()
            ];
        })->values()->toArray();

        return $routes;
    }


    //自定义添加搜索项
    public static function searchTerm()
    {
        return (new OrderViewService())->getSearchTermConfig();
    }

    public function getSearchTermConfig()
    {
        $searchTerm = [];

        if (app('plugins')->isEnabled('package-deliver')) {

            $package_deliver = [
                ['name' => 'package_deliver_id', 'placeholder' => '自提点ID', 'type' => 'text'],
                ['name' => 'package_deliver_name', 'placeholder' => '自提点名称', 'type' => 'text']
            ];
            $searchTerm = array_merge($searchTerm, $package_deliver);
        }

        return $searchTerm;
    }

    /**
     * 可以搜索的支付方式
     */
    public static function searchablePayType()
    {
        $payType = [
            ['name' => '微信小程序支付', 'value' => 55],
            ['name' => '微信H5', 'value' => 50],
            ['name' => '确认支付', 'value' => 54],
            ['name' => '货到付款', 'value' => 17],
            ['name' => '汇聚快捷支付', 'value' => 59],
            ['name' => '汇聚微信', 'value' =>  PayFactory::PAY_WECHAT_HJ],
            ['name' => '汇聚支付宝', 'value' => PayFactory::PAY_ALIPAY_HJ],
        ];

        return $payType;
    }


    public static function payTypeGroup()
    {
        return PayTypeGroup::select('id', 'name')->get()->toArray();
    }


}