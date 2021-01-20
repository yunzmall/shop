<?php

namespace app\backend\modules\order\controllers;

use app\backend\modules\order\fix\OrderPayFailRepair;
use app\backend\modules\order\models\OrderOperationLog;
use app\common\components\BaseController;
use app\common\exceptions\AppException;
use app\common\models\Address;
use app\common\models\Member;
use app\common\models\MemberAddress;
use app\common\models\Order;
use app\common\models\order\FirstOrder;
use app\common\models\OrderAddress;
use app\common\models\OrderGoods;
use app\common\models\OrderPay;
use app\common\models\PayOrder;
use app\common\facades\Setting;
use Carbon\Carbon;

/**
 * Created by PhpStorm.
 * Author: 芸众商城 www.yunzshop.com
 * Date: 21/02/2017
 * Time: 11:34
 */
class FixController extends BaseController
{
    public $transactionActions = ['payFail'];

    public function failedJobs()
    {
        dump("开始修复");

        //  一周内失败的订单收货事件
        $orderJobs = \app\common\models\OrderCreatedJob::where('status', 'waiting')->where('created_at', '>', Carbon::now()->subDays(7)->getTimestamp())->get();
        foreach ($orderJobs as $order_pay) {
            dump("修复{$order_pay->order_id}下单任务");
            app(\Illuminate\Contracts\Bus\Dispatcher::class)->dispatch(new \app\Jobs\OrderCreatedEventQueueJob($order_pay->order_id));
        }
        unset($orderJobs);
        $orderJobs = \app\common\models\OrderPaidJob::where('status', 'waiting')->where('created_at', '>', Carbon::now()->subDays(7)->getTimestamp())->get();
        foreach ($orderJobs as $order_pay) {
            dump("修复{$order_pay->order_id}支付任务");

            app(\Illuminate\Contracts\Bus\Dispatcher::class)->dispatch(new \app\Jobs\OrderPaidEventQueueJob($order_pay->order_id));
        }
        unset($orderJobs);
        $orderJobs = \app\common\models\OrderSentJob::where('status', 'waiting')->where('created_at', '>', Carbon::now()->subDays(7)->getTimestamp())->get();
        foreach ($orderJobs as $order_pay) {
            dump("修复{$order_pay->order_id}发货任务");

            app(\Illuminate\Contracts\Bus\Dispatcher::class)->dispatch(new \app\Jobs\OrderSentEventQueueJob($order_pay->order_id));
        }
        unset($orderJobs);
        $orderJobs = \app\common\models\OrderReceivedJob::where('status', 'waiting')->where('created_at', '>', Carbon::now()->subDays(7)->getTimestamp())->get();
        foreach ($orderJobs as $order_pay) {
            dump("修复{$order_pay->order_id}收货任务");

            app(\Illuminate\Contracts\Bus\Dispatcher::class)->dispatch(new \app\Jobs\OrderReceivedEventQueueJob($order_pay->order_id));
        }
        dump("开始完毕");

    }

    public function info()
    {
        $order = Order::find(176);
        dump($order->toArray());
        dump($order->orderPays->toArray());

        dump($order->orderOperationLogs->toArray());
        dump($order->hasOneRefundApply->toArray());

    }

    public function fixOrderAddress()
    {
        $orders = Order::where(
            [
                'plugin_id' => 0,
                'is_virtual' => 0,
            ]
        )->where('id', [534])->get();
        $orders->each(function ($order) {

            $memberAddress = $order->belongsToMember->defaultAddress;
            $result['address'] = implode(' ', [$memberAddress->province, $memberAddress->city, $memberAddress->district, $memberAddress->address]);
            $result['mobile'] = $memberAddress->mobile;
            $result['address'] = implode(' ', [$memberAddress->province, $memberAddress->city, $memberAddress->district, $memberAddress->address]);
            $result['realname'] = $memberAddress->username;
            $result['order_id'] = $order->id;

            list($result['province_id'], $result['city_id'], $result['district_id']) = Address::whereIn('areaname', [$memberAddress->province, $memberAddress->city, $memberAddress->district])->pluck('id');

            $orderAddress = new OrderAddress($result);
            $orderAddress->save();
            $order->dispatch_type_id = 1;
            $order->save();
        });

    }

    public function fixOrderPayId()
    {

        $r = Order::where('pay_time', '>', 0)->where(function ($query) {
            return $query->wherePayTypeId(0)->orWhere('order_pay_id', 0);
        })->get();
        $r->each(function ($order) {

            $orderPay = OrderPay::where(['order_ids' => '["' . $order->id . '"]'])->orderBy('id', 'desc')->first();

            if (isset($orderPay)) {
                $order->pay_type_id = $orderPay->pay_type_id;
                $order->order_pay_id = $orderPay->id;
                $order->save();
            }

        });
        echo 1;
        exit;

    }

    public function time()
    {
        Order::whereIn('status', [0, 1, 2, 3])->where('create_time', 0)->update(['create_time' => time()]);
        Order::whereIn('status', [1, 2, 3])->where('pay_time', 0)->update(['pay_time' => time()]);
        Order::whereIn('status', [2, 3])->where('send_time', 0)->update(['send_time' => time()]);
        Order::whereIn('status', [3])->where('finish_time', 0)->update(['finish_time' => time()]);
        Order::where('status', '-1')->where('cancel_time', 0)->update(['cancel_time' => time()]);
        echo 'ok';

    }

    public function deleteInvalidOrders()
    {
        Order::doesntHave('hasManyOrderGoods')->delete();
        Order::where('goods_price', '<=', 0)->delete();
        OrderGoods::where('goods_price', '<=', 0)->delete();
        echo 'ok';

    }

    public function payType()
    {
        Order::whereIn('status', [1, 2, 3])->where('pay_type_id', 0)->update(['pay_type_id' => 1]);
        echo 'ok';

    }

    public function dispatchType()
    {
        Order::whereIn('status', [2, 3])->where('dispatch_type_id', 0)->update(['dispatch_type_id' => 1]);
        echo 'ok';

    }

    public function index()
    {
        $payOrders = PayOrder::where('updated_at', '>', 0)->get();

        $payOrders->each(function ($payOrder) {
            $orderPay = OrderPay::wherePaySn($payOrder->out_order_no)->first();
            $orders = Order::whereIn('id', $orderPay->order_ids)->get();

            $orders->each(function ($order) use ($payOrder) {
                if ($order->pay_type_id == 0 && $order->status > 0) {
                    if ($payOrder->third_type == '余额') {
                        $order->pay_type_id = 3;
                    } elseif ($payOrder->third_type == '支付宝') {
                        $order->pay_type_id = 2;
                    } elseif ($payOrder->third_type == '微信') {
                        $order->pay_type_id = 1;
                    }
                    $order->save();
                }
            });
        });

    }

    public function t()
    {
        $a = PayOrder::where('trade_no', '4200000437201910259512165417')->first();
        $b = OrderPay::where('pay_sn', 'PN20191025210634uf')->first();
        $c = OrderOperationLog::where('order_id', 10044)->get();
        dd($a->toArray(), $b->toArray(), $b->orders->toArray(), $c->toArray());
    }

    /**
     * @throws \app\common\exceptions\AppException
     */
    public function payFail()
    {
        $order = Order::find(request('order_id'));
        $order->status = 0;
        $order->save();
        if (!$order) {
            throw new AppException('未找到订单');
        }
        $a = new OrderPayFailRepair($order);
        $a->handle();
        dump($a->message);
    }
}