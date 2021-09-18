<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2021/3/4
 * Time: 13:58
 */

namespace app\backend\modules\order\controllers;


use app\backend\modules\order\models\VueOrder;

class ShopOrderListController extends OrderListController
{
    protected function getOrder()
    {
        return VueOrder::pluginId();
    }

    protected function mergeExtraData()
    {
        $data = [
            'listUrl' => yzWebFullUrl('order.shop-order-list.get-list'), //订单查询路由
            'exportUrl' => yzWebFullUrl('order.shop-order-list.export'),
        ];

        return $data;
    }

    public function index()
    {
        return view('order.shop-list', $this->getData())->render();
    }

}