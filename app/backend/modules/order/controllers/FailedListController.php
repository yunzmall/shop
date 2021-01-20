<?php


namespace app\backend\modules\order\controllers;


use app\backend\modules\order\models\Order;
use app\backend\modules\order\models\VueOrder;
use Illuminate\Support\Facades\DB;

class FailedListController extends OrderListController
{
    protected function getOrder()
    {
        return new VueOrder();
    }

    protected function mergeExtraData()
    {
        $data = [
            'listUrl' => yzWebFullUrl('order.failed-list.get-list'), //订单查询路由
            'exportUrl' => yzWebFullUrl('order.failed-list.export'),
        ];

        return $data;
    }


    public function callbackFail()
    {

        return view('order.vue-list', $this->getData('callbackFail'))->render();
    }

    public function payFail()
    {
        return view('order.vue-list', $this->getData('payFail'))->render();
    }

    //旧方法
    public function old_callbackFail()
    {
        $orderIds = DB::table('yz_order as o')->join('yz_order_pay_order as opo', 'o.id', '=', 'opo.order_id')
            ->join('yz_order_pay as op', 'op.id', '=', 'opo.order_pay_id')
            ->join('yz_pay_order as po', 'po.out_order_no', '=', 'op.pay_sn')
            ->where('op.status', 0)
            ->where('o.pay_time', 0)
            ->where('po.status', 2)
            ->distinct()->pluck('o.id');
        $this->orderModel = Order::orders(request('search'))->whereIn('id', $orderIds);
        $this->export($this->orderModel);
        return view('order.index', $this->getData())->render();
    }


    //旧方式
    public function old_payFail()
    {
        $orderIds = DB::table('yz_order as o')->join('yz_order_pay_order as opo', 'o.id', '=', 'opo.order_id')
            ->join('yz_order_pay as op', 'op.id', '=', 'opo.order_pay_id')
            ->whereIn('o.status', [0, -1])
            ->where('op.status', 1)
            ->pluck('o.id');
        $this->orderModel = Order::orders(request('search'))->whereIn('id', $orderIds);
        $this->export($this->orderModel);
        return view('order.index', $this->getData())->render();

    }
}