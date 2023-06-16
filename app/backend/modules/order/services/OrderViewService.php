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


    //搜索组件引用
    public function searchImport($key)
    {

        $array = ['path'=> 'order.template.search', 'name' => 'shop-order-search'];

        $route = request()->input('route');

        $viewElement = $this->getViewSet()->first(function (OrderViewBase $view) use ($route) {
            return $view->getRoute() == $route;
        });


        if ($viewElement && $viewElement->getSearchElementPath() && $viewElement->getSearchElementName()) {
            $array = ['path'=> $viewElement->getSearchElementPath(), 'name' => $viewElement->getSearchElementName()];
        }


        return $array[$key];
    }

    /**
     * 可以搜索的支付方式
     */
    public static function searchablePayType()
    {
        return PayFactory::getPayType();
    }


    public static function payTypeGroup()
    {
        return PayTypeGroup::select('id', 'name')->get()->toArray();
    }


}