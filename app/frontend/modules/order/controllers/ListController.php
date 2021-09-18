<?php

namespace app\frontend\modules\order\controllers;

use app\common\components\ApiController;
use app\frontend\models\Order;
use Illuminate\Support\Facades\DB;

class ListController extends ApiController
{
    /**
     * @var Order
     */
    protected $order;

    /**
     * @return Order
     */
    protected function getOrder()
    {
        if(!isset($this->order)){
            return $this->_getOrder();
        }
        return $this->order;
    }

    /**
     * @return Order
     */
    protected function _getOrder()
    {
        $another_where = [];
        if (!is_null($event_arr = \app\common\modules\shop\ShopConfig::current()->get('order_list_search_where'))) {
            foreach ($event_arr as $v){
                $class = array_get($v, 'class');
                $function = array_get($v, 'function');
                $res = $class::$function();
                if ($res['result'] == 1) $another_where = array_merge($another_where, $res['data']);
            }
        }

        return $this->order = app('OrderManager')->make('Order')->newQueryWithoutScopes()
            ->select(app('OrderManager')->make('Order')->getTable().'.*')
            ->uid()
            ->orders()
            ->hidePluginIds()
            // ->hidePluginIds([96])
            ->where($another_where)
            ->keywordSearch(request()->input('keyword', ''));
    }

    /**
     * 在订单中心设置自定义按钮
     */
    private function setMenuGroup(){
        $menuGroup = [];
        $menuClass = app()->tagged("orderCenterMenuGroup");
        foreach ($menuClass as $menus) {
            $menu = new $menus;
            if($menu->enable()){
                $menuGroup=
                    [
                        "name"=>$menu->getName(),
                        "enable"=>$menu->enable(),
                        "id"=>$menu->getId(),
                        "api"=>$menu->getApi(),
                        "value"=>$menu->getValue(),
                    ];

            }


        }
        return $menuGroup;
    }

    protected function getData()
    {
        $pageSize = request()->input('pagesize',20);
        $model = $this->getOrder()->where(app('OrderManager')->make('Order')->getTable().'.is_member_deleted',0)->paginate($pageSize);


        //慈善基金-订单金额
        if (!is_null(\app\common\modules\shop\ShopConfig::current()->get('charity_fund_charity_money')) ) {
            $is_open = \Yunshop\CharityFund\services\SetConfigService::getSetConfig('is_open');
            if($is_open){
                $orderIds = $model->pluck('id');

                $class    = array_get(\app\common\modules\shop\ShopConfig::current()->get('charity_fund_charity_money'), 'class');
                $function = array_get(\app\common\modules\shop\ShopConfig::current()->get('charity_fund_charity_money'), 'function');
                $ret      = $class::$function($orderIds);

                $model->map(function($item) use ($ret){
                    return $item->charity_fund_money = $ret[$item->id] ?: 0;
                });
            }
        }

        $orderData = $model->toArray();

        $orderData["menu_group"]=$this->setMenuGroup();
        $orderData['data'] = $this->setOtherData($orderData['data']);
        return $orderData;

    }

    public function setOtherData($order)
    {
        $config = \app\common\modules\shop\ShopConfig::current()->get('shop-foundation.order-list-other-data');
        foreach ($config as $key => $item) {
            $class = array_get($item,'class');
            $function = array_get($item,'function');
            if(class_exists($class) && method_exists($class,$function) && is_callable([$class,$function])){
                $order = $class::$function($order);
            }
        }
        return $order;
    }

    /**
     * 所有订单(不包括"已删除"订单)
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        return $this->successJson($msg = 'ok', $data = $this->getData());

    }

    /**
     * 待付款订单
     * @return \Illuminate\Http\JsonResponse
     */
    public function waitPay()
    {
        $this->getOrder()->waitPay();
        return $this->successJson($msg = 'ok', $data = $this->getData());

    }

    /**
     * 待发货订单
     * @return \Illuminate\Http\JsonResponse
     */
    public function waitSend()
    {
        $this->getOrder()->waitSend();
        return $this->successJson($msg = 'ok', $data = $this->getData());

    }

    /**
     * 待收货订单
     * @return \Illuminate\Http\JsonResponse
     */
    public function waitReceive()
    {
        $this->getOrder()->waitReceive();

        return $this->successJson($msg = 'ok', $data = $this->getData());
    }

    /**
     * 已完成订单
     * @return \Illuminate\Http\JsonResponse
     */
    public function completed()
    {
        $this->getOrder()->completed();

        return $this->successJson($msg = 'ok', $data = $this->getData());
    }
}