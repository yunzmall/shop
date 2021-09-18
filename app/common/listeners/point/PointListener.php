<?php

/**
 * Created by PhpStorm.
 * Author: 芸众商城 www.yunzshop.com
 * Date: 2017/4/11
 * Time: 下午3:57
 */

namespace app\common\listeners\point;

use app\common\events\member\MemberBindMobile;
use app\common\events\member\RegisterByMobile;
use app\common\events\order\AfterOrderCanceledEvent;
use app\common\events\order\AfterOrderReceivedEvent;
use app\common\events\order\AfterOrderPaidEvent;
use app\common\events\withdraw\WithdrawPayedEvent;
use app\common\models\finance\PointQueue;
use app\common\models\Order;
use app\common\services\finance\CalculationPointService;
use app\common\services\finance\PointQueueService;
use app\common\services\finance\PointRollbackService;
use app\common\services\finance\PointService;
use app\common\services\point\BindMobileAward;
use app\common\services\point\IncomeWithdrawAward;
use app\common\services\point\PointToLoveQueue;
use app\Jobs\OrderBonusJob;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Yunshop\SpecialSettlement\common\LoveRecalculate;
use Yunshop\SpecialSettlement\common\PointRecalculate;
use app\common\models\finance\PointLog;

class PointListener
{
    use DispatchesJobs;

    /**
     * @var
     */
    private $pointSet;

    /**
     * @var
     */
    private $orderModel;


    public function subscribe($events)
    {
        /**
         * 收货之后 根据商品和订单赠送积分
         */
        $events->listen(
            AfterOrderReceivedEvent::class,
            PointListener::class . '@changePoint'
        );

        /**
         * 订单支付后事件
         */
        $events->listen(
            AfterOrderPaidEvent::class,
            PointListener::class . '@afterChangePoint'
        );

        /**
         * 订单关闭 积分抵扣回滚
         */
        $events->listen(
            AfterOrderCanceledEvent::class,
            PointRollbackService::class . '@orderCancel'
        );

        /**
         * 收入提现奖励积分
         */
        $events->listen(
            WithdrawPayedEvent::class,
            IncomeWithdrawAward::class . '@award'
        );

        /**
         * 收入提现奖励比例积分
         */
        $events->listen(
            WithdrawPayedEvent::class,
            IncomeWithdrawAward::class . '@awardScale'
        );

        /**
         * 绑定手机号奖励积分
         */
        $events->listen(
            MemberBindMobile::class,
            BindMobileAward::class . '@award'
        );

        /**
         * 手机号注册会员奖励积分
         */
        $events->listen(
            RegisterByMobile::class,
            BindMobileAward::class . '@award'
        );

        /**
         * 积分每月赠送
         */
        $events->listen('cron.collectJobs', function () {
            \Cron::add('PointQueue', '*/30 * * * *', function () {
                (new PointQueueService())->handle();
            });
        });

        /**
         * 积分自动转入爱心值
         */
        $events->listen('cron.collectJobs', function () {
            \Cron::add('PointToLoveQueue', '*/10 * * * *', function () {
                (new PointToLoveQueue())->handle();
            });
        });
    }

    /**
     * 收货之后 根据商品和订单赠送积分
     *
     * @param AfterOrderReceivedEvent $event
     */
    public function changePoint(AfterOrderReceivedEvent $event)
    {
        \Log::debug('收货完成赠送积分,订单ID' . $event->getOrderModel()->id);
        $this->orderModel = Order::find($event->getOrderModel()->id);
        if ($this->orderModel->uid == 0) {
            return;
        }

        $this->pointSet = $this->orderModel->getSetting('point.set');

        // 订单商品赠送积分[ps:商品单独设置]
//        $this->givingTime($this->orderModel);
        self::byGoodsGivePoint($this->orderModel);

        // 订单金额赠送积分[ps:积分基础设置]
        $this->orderGivePoint($this->orderModel);

        // 订单插件分红记录
        (new OrderBonusJob('yz_point_log', 'point', 'order_id', 'id', 'point', $this->orderModel))->handle();
    }

    /**
     * 支付后 只根据商品赠送积分
     * @param AfterOrderPaidEvent $event
     */
    public function afterChangePoint(AfterOrderPaidEvent $event)
    {
        \Log::debug('支付完成赠送积分,订单ID' . $event->getOrderModel()->id);
        $this->orderModel = Order::find($event->getOrderModel()->id);
        if ($this->orderModel->uid == 0) {
            return;
        }
        $this->pointSet = $this->orderModel->getSetting('point.set');
        // 订单商品赠送积分[ps:商品单独设置]
        self::afterByGoodsGivePoint($this->orderModel);
    }

//    private function givingTime($orderModel)
//    {
//        $data = self::byGoodsGivePoint($orderModel);
////      每月赠送
//        if ($data['goodsSale']['point_type'] && $data['goodsSale']['max_once_point'] > 0) {
//                PointQueue::handle($this->orderModel, $data['goodsSale'], $data['point_data']['point']);
//        } else {
//        // 订单完成立即赠送[ps:原业务逻辑]
//            $this->addPointLog($data['point_data']);
//        }
//    }

    public function getPointDataByGoods($order_goods_model)
    {
        $pointData = [
            'point_income_type' => 1,
            'member_id'         => $this->orderModel->uid,
            'order_id'          => $this->orderModel->id,
            'point_mode'        => 1
        ];
        $pointData += CalculationPointService::calculationPointByGoods($order_goods_model);
        return $pointData;
    }

    public function getPointDateByOrder($orderModel)
    {
        $pointData = [
            'point_income_type' => 1,
            'member_id'         => $this->orderModel->uid,
            'order_id'          => $this->orderModel->id,
            'point_mode'        => 2
        ];

        $pointData += CalculationPointService::calculationPointByOrder($orderModel);
        return $pointData;
    }

    private function addPointLog($pointData)
    {

        if (isset($pointData['point'])) {
            $pointService = new PointService($pointData);
            $pointService->changePoint();
        }
    }

    public function byGoodsGivePoint($orderModel)
    {

        // 验证订单商品是立即赠送还是每月赠送
        foreach ($orderModel->hasManyOrderGoods as $orderGoods) {
            // 商品营销数据
            $goodsSale = $orderGoods->hasOneGoods->hasOneSale;
            // 赠送积分数组[ps:放到这是因为(每月赠送)需要赠送积分总数]
            $is_special_settlement=false;

            /**特殊结算插件**/
            if (app('plugins')->isEnabled('special-settlement') && \Setting::get('plugin.special-settlement.marketing-rule')["point_reward"]==1) {

                if ($orderModel->plugin_id == 31 || $orderModel->plugin_id == 32) {

                    $orderGoods->payment_amount=$orderGoods->goods_price;
                    $is_special_settlement=true;
                }
            }

            $point_data = self::getPointDataByGoods($orderGoods);

            /**特殊结算插件**/
            if ($is_special_settlement) {
                $recalculate = new PointRecalculate();
                $recalculate->setGoodsPrice($point_data["point"]);
                $recalculate->setPluginId($orderModel->plugin_id);
                $recalculate->setOrderId($orderModel->id);
                $point_data['point'] = $recalculate->getAmount();
                $point_data['remark'] = '购买商品赠送['.$point_data['point'].']积分！';


            }



            // 每月赠送 $goodsSale->point_type == 1
            if ($goodsSale->point_type == 1 && $goodsSale->max_once_point > 0) {
                PointQueue::handle($this->orderModel, $goodsSale, $point_data['point']);
            } else {
                //1-每月赠送 2-支付后赠送
                if (!in_array($goodsSale->point_type,[1,2])) {
                    // 订单完成立即赠送[ps:原业务逻辑]
                    self::addPointLog($point_data);
                }
            }
        }
    }

    public function afterByGoodsGivePoint($orderModel)
    {

        // 验证订单商品是立即赠送还是每月赠送
        foreach ($orderModel->hasManyOrderGoods as $orderGoods) {
            // 商品营销数据
            $goodsSale = $orderGoods->hasOneGoods->hasOneSale;
            // 赠送积分数组[ps:放到这是因为(每月赠送)需要赠送积分总数]
            $is_special_settlement=false;

            /**特殊结算插件**/
            if (app('plugins')->isEnabled('special-settlement') && \Setting::get('plugin.special-settlement.marketing-rule')["point_reward"]==1) {

                if ($orderModel->plugin_id == 31 || $orderModel->plugin_id == 32) {

                    $orderGoods->payment_amount=$orderGoods->goods_price;
                    $is_special_settlement=true;
                }
            }

            $point_data = self::getPointDataByGoods($orderGoods);

            /**特殊结算插件**/
            if ($is_special_settlement) {
                $recalculate = new PointRecalculate();
                $recalculate->setGoodsPrice($point_data["point"]);
                $recalculate->setPluginId($orderModel->plugin_id);
                $recalculate->setOrderId($orderModel->id);
                $point_data['point'] = $recalculate->getAmount();
                $point_data['remark'] = '购买商品赠送['.$point_data['point'].']积分！';
            }

            //2-订单支付后赠送
            if ($goodsSale->point_type == 2) {
                self::addPointLog($point_data);
            }

        }
    }


    public function byGoodsGivePointPay($orderModel)
    {
        $point = 0;
        // 验证订单商品是立即赠送还是每月赠送
        foreach ($orderModel->hasManyOrderGoods as $orderGoods) {
            // 赠送积分数组[ps:放到这是因为(每月赠送)需要赠送积分总数]
            $point_data = self::getPointDataByGoods($orderGoods);
            $point += $point_data['point'];
            // 每月赠送
        }
        return $point;
    }


//    private function byGoodsGivePoint()
//    {
//        // 验证订单商品是立即赠送还是每月赠送
//        foreach ($this->orderModel->hasManyOrderGoods as $orderGoods) {
//            // 商品营销数据
//            $goodsSale = $orderGoods->hasOneGoods->hasOneSale;
//            // 赠送积分数组[ps:放到这是因为(每月赠送)需要赠送积分总数]
//            $point_data = $this->getPointDataByGoods($orderGoods);
//            // 每月赠送
//            if ($goodsSale->point_type && $goodsSale->max_once_point > 0) {
//                PointQueue::handle($this->orderModel, $goodsSale, $point_data['point']);
//            } else {
//                // 订单完成立即赠送[ps:原业务逻辑]
//                $this->addPointLog($point_data);
//            }
//        }
//    }

    private function orderGivePoint($orderModel)
    {
        \Log::debug('赠送积分');
        $pointData = $this->getPointDateByOrder($orderModel);
        $this->addPointLog($pointData);
    }


}
