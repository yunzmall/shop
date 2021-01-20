<?php
/**
 * Created by PhpStorm.
 * User: shenyang
 * Date: 2017/9/18
 * Time: 下午3:46
 */

namespace app\Jobs;

use app\common\events\order\AfterOrderReceivedEvent;
use app\common\facades\Setting;
use app\common\facades\SiteSetting;
use app\common\models\Order;
use app\common\models\OrderPaidJob;
use app\common\models\OrderReceivedJob;
use app\common\modules\shop\ShopConfig;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\DB;

class OrderReceivedEventQueueJob implements ShouldQueue
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
        \Log::info('订单'.$this->orderId.'received任务开始执行');

        DB::transaction(function () {
            $orderId = $this->orderId;
            \YunShop::app()->uniacid = null;
            $order = Order::find($orderId);
            \YunShop::app()->uniacid = $order->uniacid;
            Setting::$uniqueAccountId = $order->uniacid;
            if(!$order->orderReceivedJob){
                $order->setRelation('orderReceivedJob',new OrderReceivedJob(['order_id'=>$order->id]));
                $order->orderReceivedJob->save();
            }

            if($order->orderReceivedJob->status == 'finished'){
                \Log::error('订单收货事件触发失败',"{$orderId}orderReceivedJob记录已");
                return;
            }
            $order->orderReceivedJob->status = 'finished';
            $order->orderReceivedJob->save();
            $event = new AfterOrderReceivedEvent($order);
            app('events')->safeFire($event,$order->id);

        });
        \Log::info('订单'.$this->orderId.'received任务执行完成');

    }
}