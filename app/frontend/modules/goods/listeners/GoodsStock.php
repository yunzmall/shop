<?php

namespace app\frontend\modules\goods\listeners;

use app\common\events\order\AfterOrderCanceledEvent;
use app\common\events\order\AfterOrderCreatedImmediatelyEvent;
use app\common\events\order\AfterOrderPaidImmediatelyEvent;
use app\common\events\order\BeforeOrderCreateEvent;
use app\common\facades\Setting;
use app\common\facades\SiteSetting;
use app\common\models\OrderGoods;
use Illuminate\Support\Facades\Redis;

/**
 * Created by PhpStorm.
 * Author: 芸众商城 www.yunzshop.com
 * Date: 2017/4/14
 * Time: 下午10:34
 */
class GoodsStock
{
    //todo 2020/10/16 blank 兼容云仓订单不扣除库存
    //1、商品在商城正常的订单下单时按正常的商品库存加减。
    //2、使用云仓配送的购买的商品，会有记录单独的商品库存，例：商品1件 = 插件商品挂件3件。
    //3、这时通过云仓插件下单的商品走的是插件商品库存，而不是商城商品的库存数。
    public function cloudWarehouse($order)
    {
        return $order->plugin_id == 56;
    }

    public function onOrderCreated(AfterOrderCreatedImmediatelyEvent $event)
    {

        if($this->cloudWarehouse($event->getOrderModel())) return;

        $order = $event->getOrderModel();
        $order->orderGoods->map(function (OrderGoods $orderGoods) {
            // 预扣
            $orderGoods->goodsStock()->withholdRecord();
        });
    }
    public function onOrderCreating(BeforeOrderCreateEvent $event)
    {
        if($this->cloudWarehouse($event->getOrder())) return;

        $order = $event->getOrder();
        $order->orderGoods->map(function (OrderGoods $orderGoods) {
            // 预扣
            $orderGoods->goodsStock()->withhold();
        });
    }

    public function onOrderPaid(AfterOrderPaidImmediatelyEvent $event)
    {

        if($this->cloudWarehouse($event->getOrderModel())) return;

        $order = $event->getOrderModel();
        $order->hasManyOrderGoods->map(function (OrderGoods $orderGoods) {
            // 实扣
            $orderGoods->goodsStock()->reduce();
            $orderGoods->goods->addSales($orderGoods->total);
        });
    }

    public function onOrderCanceled(AfterOrderCanceledEvent $event)
    {
        if($this->cloudWarehouse($event->getOrderModel())) return;

        $order = $event->getOrderModel();
        $order->hasManyOrderGoods->map(function (OrderGoods $orderGoods) {
            // 返还预扣库存
            $orderGoods->goodsStock()->rollback();
        });
    }

    public function subscribe($events)
    {
        $events->listen(
            BeforeOrderCreateEvent::class,
            self::class . '@onOrderCreating'
        );
        $events->listen(
            AfterOrderCreatedImmediatelyEvent::class,
            self::class . '@onOrderCreated'
        );
        $events->listen(
            AfterOrderPaidImmediatelyEvent::class,
            self::class . '@onOrderPaid'
        );
        $events->listen(
            AfterOrderCanceledEvent::class,
            self::class . '@onOrderCanceled'
        );
        // 每分钟清除超时的预扣库存记录
        $events->listen('cron.collectJobs', function () {
            \Cron::add("clearWithholdStock", '*/1 * * * *', function () {
                $withholdZsetKeys = Redis::smembers('withhold_order_goods_id_keys');
                foreach ($withholdZsetKeys as $withholdZsetKey) {
                    $orderGoodsIds = Redis::zrangeByScore($withholdZsetKey, 0, time() - 60);
                    Redis::zrem($withholdZsetKey, 0, time() - 60);
                    if ($orderGoodsIds) {
                        $orderGoods = OrderGoods::whereIn('id', $orderGoodsIds)->get();
                        foreach ($orderGoods as $aOrderGoods) {
                            /**
                             * @var OrderGoods $aOrderGoods
                             */
                            Setting::$uniqueAccountId = \YunShop::app()->uniacid = $aOrderGoods->uniacid;

                            $aOrderGoods->goodsStock()->rollback();
                        }
                    }
                }
            });
        });
    }

}