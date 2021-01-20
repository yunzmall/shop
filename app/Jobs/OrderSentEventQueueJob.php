<?php
/**
 * Created by PhpStorm.
 * User: shenyang
 * Date: 2017/9/18
 * Time: 下午3:46
 */

namespace app\Jobs;

use app\common\events\order\AfterOrderSentEvent;
use app\common\facades\Setting;
use app\common\facades\SiteSetting;
use app\common\models\Order;
use app\common\models\OrderReceivedJob;
use app\common\models\OrderSentJob;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\DB;

class OrderSentEventQueueJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;
    /**
     * @var Order
     */
    protected $orderId;

    /**
     * OrderReceivedEventQueueJob constructor.
     * @param $orderId
     */
    public function __construct($orderId)
    {
        $queueCount = Order::queueCount();
        if($queueCount){
            $this->queue = 'order:' . ($orderId % Order::queueCount());
        }

        $this->orderId = $orderId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        \Log::info('订单'.$this->orderId.'sent任务开始执行');

        DB::transaction(function () {
            $orderId = $this->orderId;
            \YunShop::app()->uniacid = null;
            $order = Order::find($orderId);
            \YunShop::app()->uniacid = $order->uniacid;
            Setting::$uniqueAccountId = $order->uniacid;
            if(!$order->orderSentJob){
                $order->setRelation('orderSentJob',new OrderSentJob(['order_id'=>$order->id]));
                $order->orderSentJob->save();
            }

            if($order->orderSentJob->status == 'finished'){
                \Log::error('订单发货事件触发失败',"{$orderId}orderSentJob已完成");

                return;
            }
            $order->orderSentJob->status = 'finished';
            $order->orderSentJob->save();
            $event = new AfterOrderSentEvent($order);
            app('events')->safeFire($event,$order->id);

        });
        \Log::info('订单'.$this->orderId.'sent任务执行完成');

    }
}