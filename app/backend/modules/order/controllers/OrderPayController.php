<?php
/**
 * 订单详情
 * Created by PhpStorm.
 * Author:
 * Date: 2017/3/4
 * Time: 上午11:16
 */

namespace app\backend\modules\order\controllers;

use app\backend\modules\order\models\Order;
use app\common\components\BaseController;
use app\common\models\OrderPay;
use app\common\models\PayCallbackException;

class OrderPayController extends BaseController
{
    /**
     * @return string
     * @throws \Throwable
     */
    public function index()
    {
        $orderId = request()->query('order_id');
        $order = Order::with(['orderPays' => function ($query) {
            $query->with('orders');
        }])->find($orderId);

        return view('order.orderPay', [
            'orderPays' => json_encode($order->orderPays)
        ])->render();
    }

    public function vue()
    {
        $orderId = intval(request()->input('order_id'));
        $order = Order::with(['orderPays' => function ($query) {
            $query->with('orders');
        }])->find($orderId);

        return $this->successJson('orderPay', $order->orderPays);
    }

    public function callbackException()
    {
        //回调失败退款记录
        $data = [];
        return view('order.pay_error_refund', [
            'orderPays' => json_encode($data)
        ])->render();
    }

    public function exceptionList()
    {
        $search = request()->input('search');

        $page = PayCallbackException::getList($search)->orderBy('id','desc')->paginate(15);

        if ($page->isNotEmpty()) {
            $page->map(function (PayCallbackException $payException) {
                $orders = $payException->orderPay->orders;
                $payException->orders = $orders;
            });
        }

        return $this->successJson('exceptionList', $page);
    }

    public function payErrorRefund()
    {

        $id = intval(request()->input('id'));

        if (empty($id)) {
            return $this->errorJson('参数错误');
        }

        $payException = PayCallbackException::find($id);


        if (!$payException || $payException->error_code != PayCallbackException::ORDER_CLOSE) {
            return $this->errorJson('异常类型不支持操作退款');
        }

        if ( $payException->status == PayCallbackException::STATUS_SUCCESS) {
            return $this->successJson('支付已退款');
        }


        $result =  $payException->refund();

        if (!$result['status']) {
            return $this->errorJson($result['msg']);
        }


        return $this->successJson('操作退款成功');
    }

}