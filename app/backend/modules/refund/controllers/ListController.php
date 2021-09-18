<?php
namespace app\backend\modules\refund\controllers;

use app\backend\modules\order\controllers\OrderListController;

/**
 * 退款申请列表
 * Created by PhpStorm.
 * Author: 芸众商城 www.yunzshop.com
 * Date: 2017/4/13
 * Time: 下午3:04
 */
class ListController extends OrderListController
{


    protected function setOrderModel()
    {
        $search = request()->input('search');
        $code = request()->input('code');

        $model = $this->getOrder()->statusCode($code)->orders($search);

        if ($code == 'refund') {
            $model->whereHas('hasOneRefundApply',function ($query){
                return $query->refunding();
            });
        }

        if ($code == 'refunded') {
            $model->refunded();
        }

       return $model;
    }

    protected function mergeExtraData()
    {
        $data = [
            'listUrl' => yzWebFullUrl('refund.list.get-list'), //订单查询路由
            'exportUrl' => yzWebFullUrl('refund.list.export'),
        ];

        return $data;
    }

    public function refunded()
    {
//        $this->orderModel->refunded();
//        $this->export($this->orderModel->refunded());
        return view('order.vue-list', $this->getData('refunded'))->render();
    }
    public function index()
    {
//        $this->orderModel->whereHas('hasOneRefundApply',function ($query){
//            return $query->refunding();
//        });
//        $orderModel = $this->orderModel->whereHas('hasOneRefundApply',function ($query){
//            return $query->refunding();
//        });
//        $this->export($orderModel);
        return view('order.vue-list', $this->getData('refund'))->render();
    }


    public function returnGoods()
    {
        $this->orderModel->whereHas('hasOneRefundApply',function ($query){
            return $query->refunding()->ReturnGoods();
        });
        $orderModel = $this->orderModel->whereHas('hasOneRefundApply',function ($query){
            return $query->refunding()->ReturnGoods();
        });
        $this->export($orderModel);
        return view('order.index', $this->getData())->render();
    }

    public function exchangeGoods()
    {
        $this->orderModel->whereHas('hasOneRefundApply',function ($query){
            return $query->refunding()->ExchangeGoods();
        });
        $orderModel = $this->orderModel->whereHas('hasOneRefundApply',function ($query){
            return $query->refunding()->ExchangeGoods();
        });
        $this->export($orderModel);
        return view('order.index', $this->getData())->render();
    }

    /**
     * @return mixed
     * 退换货订单
     */
    public function refundMoney()
    {
        $this->orderModel->whereHas('hasOneRefundApply',function ($query){
            return $query->refunding()->RefundMoney();
        });
        $orderModel = $this->orderModel->whereHas('hasOneRefundApply',function ($query){
            return $query->refunding()->RefundMoney();
        });
        $this->export($orderModel);
        return view('order.index', $this->getData())->render();
    }
}