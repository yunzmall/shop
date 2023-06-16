<?php

namespace app\frontend\modules\order\listeners;

use app\backend\modules\job\models\FailedJob;
use app\common\events\order\AfterOrderCanceledEvent;
use app\common\events\order\AfterOrderCancelSentEvent;
use app\common\events\order\AfterOrderCreatedEvent;
use app\common\events\order\AfterOrderPackageSentEvent;
use app\common\events\order\AfterOrderPaidEvent;
use app\common\events\order\AfterOrderReceivedEvent;
use app\common\events\order\AfterOrderSentEvent;
use app\common\facades\Setting;
use app\common\listeners\order\FirstOrderListener;
use app\common\models\Order;
use app\common\models\UniAccount;
use app\common\services\SystemMsgService;
use app\framework\Support\Facades\Log;
use app\frontend\modules\order\services\MessageService;
use app\frontend\modules\order\services\MiniMessageService;
use app\frontend\modules\order\services\OrderService;
use app\frontend\modules\order\services\OtherMessageService;
use app\frontend\modules\order\services\SmsMessageService;
use Carbon\Carbon;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Database\Events\TransactionBeginning;
use Illuminate\Database\Events\TransactionCommitted;
use Illuminate\Database\Events\TransactionRolledBack;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

/**
 * Created by PhpStorm.
 * User: shenyang
 * Date: 2017/6/5
 * Time: 下午8:53
 */
class orderListener
{
    public function onCreated(AfterOrderCreatedEvent $event)
    {
        $order = Order::find($event->getOrderModel()->id);
        if($order->notSendMessage()) {
            return;
        }
        (new MessageService($order))->created();

        (new OtherMessageService($order))->created();
    }

    public function onPaid(AfterOrderPaidEvent $event)
    {
        $order = Order::find($event->getOrderModel()->id);
        if($order->notSendMessage()) {
            return;
        }
        (new MiniMessageService($order))->received();
        if (!$order->isVirtual()) {
            (new MessageService($order))->paid();
            (new OtherMessageService($order))->paid();
        }
        (new SystemMsgService())->paid($order);
        // todo 预扣库存转化为实际库存
    }

    public function onCanceled(AfterOrderCanceledEvent $event)
    {
        $order = Order::find($event->getOrderModel()->id);
        if($order->notSendMessage()) {
            return;
        }
        (new MessageService($order))->canceled();
    }

    public function onSent(AfterOrderSentEvent $event)
    {
        $order = Order::find($event->getOrderModel()->id);
        if($order->notSendMessage()) {
            return;
        }
        if (!$order->isVirtual()) {
            //多包裹发货微信通知走包裹发送事件 AfterOrderPackageSentEvent
            if(!$order->is_all_send_goods){
                (new MessageService($order))->sent();
            }
            (new OtherMessageService($order))->sent();
            (new SmsMessageService($order))->sent();
        }
    }

    public function onPackageSent(AfterOrderPackageSentEvent $event)
    {
        $order = Order::find($event->getOrderModel()->id);
        if($order->notSendMessage()) {
            return;
        }
        if (!$order->isVirtual()) {
            (new MessageService($order))->packageSent();
        }
    }

    public function onCanceledSent(AfterOrderCancelSentEvent $event)
    {
        $order = Order::find($event->getOrderModel()->id);

        if ($order) {
            $order->delOrderSent();
        }

    }

    public function onReceived(AfterOrderReceivedEvent $event)
    {
        $order = Order::find($event->getOrderModel()->id);
        if($order->notSendMessage()) {
            return;
        }
        (new MessageService($order))->received();
        (new OtherMessageService($order))->received();
        (new SystemMsgService($order->uniacid))->received($order);
    }

    public function subscribe(Dispatcher $events)
    {
        $events->listen(AfterOrderCreatedEvent::class, self::class . '@onCreated');

        // 首单
        //$events->listen(AfterOrderPaidImmediatelyEvent::class, FirstOrderListener::class . '@handle');
        $events->listen(AfterOrderPaidEvent::class, FirstOrderListener::class . '@handle');
        // 订单取消,取消首单标识
        $events->listen(AfterOrderCanceledEvent::class, FirstOrderListener::class . '@cancel');

        $events->listen(AfterOrderPaidEvent::class, self::class . '@onPaid');
        $events->listen(AfterOrderCanceledEvent::class, self::class . '@onCanceled');
        $events->listen(AfterOrderSentEvent::class, self::class . '@onSent');
        $events->listen(AfterOrderCancelSentEvent::class, self::class . '@onCanceledSent'); //订单取消发货
        $events->listen(AfterOrderReceivedEvent::class, self::class . '@onReceived');
        $events->listen(AfterOrderPaidEvent::class, \app\common\listeners\member\AfterOrderPaidListener::class . '@handle', 1);
        $events->listen(AfterOrderReceivedEvent::class, \app\common\listeners\member\AfterOrderReceivedListener::class . '@handle', 1);
        $events->listen(AfterOrderPackageSentEvent::class, self::class . '@onPackageSent');
        $events->listen(TransactionCommitted::class, function (TransactionCommitted $event) {
            app(\Illuminate\Contracts\Bus\Dispatcher::class)->dbTransactionCommitted($event);
        });
        $events->listen(TransactionRolledBack::class, function ($event) {
            app(\Illuminate\Contracts\Bus\Dispatcher::class)->dbTransactionRollBack($event);
        });
		$events->listen(TransactionBeginning::class, function ($event) {
			app(\Illuminate\Contracts\Bus\Dispatcher::class)->dbTransactionBeginning($event);
		});
		
        // 订单自动任务
        $events->listen('cron.collectJobs', function () {

            //todo 订单支付回调时间超过订单关闭时间，报错误自动退款
            \Cron::add('GenerateOrderSn', '*/30 * * * *', function () {
                (new \app\frontend\modules\order\listeners\PayExceptionRefundCron())->handle();
                return;
            });


            \Cron::add("DaemonQueue", '*/1 * * * *', function () {

                $supervisor = app('supervisor');
                $supervisor->setTimeout(5000);  // microseconds
                $states = $supervisor->getState();
                foreach ($states as $state) {
                    if (is_object($state) && $state->value()['statecode'] != 1) {
                        $supervisor->startAllProcesses();
                        break;
                    }
                }
                $allProcessInfos = $supervisor->getAllProcessInfo();
                if(is_object($allProcessInfos)){
                    foreach ($allProcessInfos as $allProcessInfo) {
                        foreach ($allProcessInfo->value() as $value) {
                            if ($value != 20) {
                                $supervisor->startProcess($value['group'] . ':' . $value['name']);
                            }
                        }
                    }
                }

                // mysql重启后，自动重启supervisor
                $serverInfo = app('db.connection')->getPdo()->getAttribute(\PDO::ATTR_SERVER_INFO);
                $tmp = substr($serverInfo, stripos($serverInfo, 'uptime: ') + 8);
                $uptime = substr($tmp, 0, stripos($tmp, ' '));
                if ($uptime < \Cache::get('mysqlUptime')) {
                    $supervisor->restart();
                }
                \Cache::put('mysqlUptime', $uptime, 60 * 24);
            });

            // 临时修复订单收货事件
//            \Cron::add("FailedJobFix", '* */1 * * *', function () {
//
//                $orderJobs = \app\common\models\OrderCreatedJob::where('status','waiting')->where('created_at','>',Carbon::now()->subDays(7)->toDateTimeString())->get();
//                foreach ($orderJobs as $order_pay) {
//                    app(\Illuminate\Contracts\Bus\Dispatcher::class)->dispatch(new \app\Jobs\OrderCreatedEventQueueJob($order_pay->order_id));
//                }
//
//                $orderJobs = \app\common\models\OrderPaidJob::where('status','waiting')->where('created_at','>',Carbon::now()->subDays(7)->toDateTimeString())->get();
//                foreach ($orderJobs as $order_pay) {
//                    app(\Illuminate\Contracts\Bus\Dispatcher::class)->dispatch(new \app\Jobs\OrderPaidEventQueueJob($order_pay->order_id));
//                }
//
//                $orderJobs = \app\common\models\OrderSentJob::where('status','waiting')->where('created_at','>',Carbon::now()->subDays(7)->toDateTimeString())->get();
//                foreach ($orderJobs as $order_pay) {
//                    app(\Illuminate\Contracts\Bus\Dispatcher::class)->dispatch(new \app\Jobs\OrderSentEventQueueJob($order_pay->order_id));
//                }
//
//                $orderJobs = \app\common\models\OrderReceivedJob::where('status','waiting')->where('created_at','>',Carbon::now()->subDays(7)->toDateTimeString())->get();
//                foreach ($orderJobs as $order_pay) {
//                    app(\Illuminate\Contracts\Bus\Dispatcher::class)->dispatch(new \app\Jobs\OrderReceivedEventQueueJob($order_pay->order_id));
//                }
//            });
            // 虚拟订单修复
            \Log::info("--虚拟订单修复--");
            \Cron::add("VirtualOrderFix", '* */1 * * *', function () {
                $orders = DB::table('yz_order')->whereIn('status', [1, 2])->where('is_virtual', 1)->where('refund_id', 0)->where('is_pending', 0)->get();
                // 所有超时未收货的订单,遍历执行收货
                $orders->each(function ($order) {
                    OrderService::fixVirtualOrder($order);

                });
                // todo 使用队列执行
            });
            $uniAccount = UniAccount::getEnable();
            foreach ($uniAccount as $u) {
                Setting::$uniqueAccountId =  \YunShop::app()->uniacid = $accountId = $uniacid = $u->uniacid;
                //订单自动发货
                $sendMin = 5;
//                if ((int)Setting::get('shop.trade.send')) {
                    // 开启自动收货时
                    Log::info("--{$accountId}订单自动发货任务注册--");
                    \Cron::add("OrderSend{$u->uniacid}", '*/' . $sendMin . ' * * * *', function () use ($accountId) {
                        Log::info("--{$accountId}订单自动发货开始执行--");
                        OrderService::autoSend($accountId);
                    });
//                }

                // 订单自动收货执行间隔时间 默认60分钟
                $receive_min = 5;//(int)\Setting::get('shop.trade.receive_time') ?: 60;

                if ((int)\Setting::get('shop.trade.receive')) {
                    // 开启自动收货时
                    \Log::info("--{$u->uniacid}订单自动完成任务注册--");
                    \Cron::add("OrderReceive{$u->uniacid}", '*/' . $receive_min . ' * * * *', function () use ($uniacid) {
                        \Log::info("--{$uniacid}订单自动完成开始执行--");

                        // 所有超时未收货的订单,遍历执行收货
                        OrderService::autoReceive($uniacid);
                        // todo 使用队列执行
                    });
                }

                // 订单自动关闭执行间隔时间 默认60分钟
                $close_min = 5;//(int)\Setting::get('shop.trade.close_order_time') ?: 59;


                if ((int)\Setting::get('shop.trade.close_order_days')) {
                    // 开启自动关闭时
                    \Log::info("--订单自动关闭start--");
                    \Cron::add("OrderClose{$u->uniacid}", '*/' . $close_min . ' * * * * ', function () use ($uniacid) {
                        // 所有超时付款的订单,遍历执行关闭
                        OrderService::autoClose($uniacid);
                        // todo 使用队列执行
                    });
                }
                // todo 预扣库存超过两小时自动加回

                // 收银台订单检测 自动收货
//                \Log::info("--收银台订单自动完成start--");
//                \Cron::add("CashireOrderReceive{$u->uniacid}", '*/1 * * * * *', function () {
//                    $start_time = time() - (60 * 60 * 24);
//                    $end_time = time();
//                    //遍历执行收货
//                    $orders = \app\backend\modules\order\models\Order::waitReceive()
//                        ->where('plugin_id', 31)
//                        ->whereBetween('pay_time', [$start_time, $end_time])
//                        ->normal()
//                        ->get();
//                    if (!$orders->isEmpty()) {
//                        $orders->each(function ($order) {
//                            try {
//                                OrderService::orderReceive(['order_id' => $order->id]);
//                            } catch (\Exception $e) {
//
//                            }
//                        });
//                    }
//                });
            }
        });
    }
}
