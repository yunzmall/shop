<?php

namespace app\frontend\modules\order\controllers;

use app\common\components\ApiController;
use app\frontend\models\Order;

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

        return $this->order = app('OrderManager')->make('Order')->newQueryWithoutScopes()
            ->select(app('OrderManager')->make('Order')->getTable().'.*')
            ->uid()
            ->orders()
            ->hidePluginIds()
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
        $orderData=$this->getOrder()->where(app('OrderManager')->make('Order')->getTable().'.is_member_deleted',0)->paginate($pageSize)->toArray();
        $orderData["menu_group"]=$this->setMenuGroup();
        return $orderData;

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