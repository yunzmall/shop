<?php
/**
 * Created by PhpStorm.
 * User: shenyang
 * Date: 2017/9/18
 * Time: 下午3:46
 */

namespace app\Jobs;


use app\common\events\order\AfterOrderCreatedEvent;
use app\common\facades\Setting;
use app\common\facades\SiteSetting;
use app\common\models\Order;
use app\common\models\OrderCreatedJob;
use app\frontend\modules\order\models\PreOrder;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\DB;

class OrderCreatedEventQueueJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var PreOrder
     */
    protected $orderId;

    /**
     * OrderCreatedEventQueueJob constructor.
     * @param $orderId
     */
    public function __construct($orderId)
    {
        $queueCount = Order::queueCount();
        if ($queueCount) {
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
        \Log::info('订单'.$this->orderId.'created任务开始执行');
        DB::transaction(function () {
            try {
                \YunShop::app()->uniacid = null;
                $orderId = $this->orderId;
                $order = Order::find($orderId);
                \YunShop::app()->uniacid = $order->uniacid;
                Setting::$uniqueAccountId = $order->uniacid;
                if(!$order->orderCreatedJob){
                    $order->setRelation('orderCreatedJob',new OrderCreatedJob(['order_id'=>$order->id]));
                    $order->orderCreatedJob->save();
                }

                if($order->orderCreatedJob->status == 'finished'){
                    \Log::error('订单完成事件触发失败',"{$orderId}orderCreatedJob记录已");
                    return;
                }
                $order->orderCreatedJob->status = 'finished';
                $order->orderCreatedJob->save();
                $event = new AfterOrderCreatedEvent($order);
                app('events')->safeFire($event,$order->id);
            }catch (\Exception $exception){
                \Log::error('订单'.$this->orderId.'created任务异常',$exception);
                throw $exception;
            }

        });
        \Log::info('订单'.$this->orderId.'created任务执行完成');
    }

}