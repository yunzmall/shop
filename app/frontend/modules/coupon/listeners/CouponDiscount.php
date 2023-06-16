<?php
/**
 * Created by PhpStorm.
 * Author:  
 * Date: 2017/3/11
 * Time: 上午10:00
 */

namespace app\frontend\modules\coupon\listeners;

use app\common\events\order\AfterOrderCanceledEvent;
use app\common\events\order\AfterOrderCloseEvent;
use app\common\events\order\AfterOrderCreatedEvent;
use app\common\events\order\AfterOrderReceivedEvent;
use app\common\events\order\AfterOrderRefundSuccessEvent;
use app\common\facades\Setting;
use app\common\models\coupon\OrderGoodsCoupon;
use app\common\services\finance\PointService;
use app\frontend\modules\coupon\services\CouponService;

class CouponDiscount
{
    private $event;


    public function deductionAwardPoint(AfterOrderReceivedEvent $event)
    {
        if (!Setting::get('coupon.award_point')) {
            return null;
        }
        $orderModel = $event->getOrderModel();

        $orderDiscount = $orderModel->orderDiscount;

        $point = 0;
        if ($orderDiscount) {
            foreach ($orderDiscount as $key => $deduction) {

                if ($deduction['discount_code'] == 'coupon') {
                    $point = $deduction['amount'];
                    break;
                }
            }
        }
        if ($point <= 0) {
            return null;
        }
        $data = [
            'point_income_type' => PointService::POINT_INCOME_GET,
            'point_mode'        => PointService::POINT_MODE_COUPON_DEDUCTION_AWARD,
            'member_id'         => $orderModel->uid,
            'point'             => $point,
            'remark'            => '订单：'.$orderModel->order_sn.'优惠券抵扣奖励积分'.$point,
        ];
        return (new PointService($data))->changePoint();

    }

    /*
     * 监听订单完成事件
     */
//    public function onOrderReceived(AfterOrderReceivedEvent $event)
//    {
//        $this->event = $event;
//        $orderModel = $this->event->getOrderModel();
//        $orderGoods = $orderModel->hasManyOrderGoods;//订单商品
//        $couponService = new CouponService($orderModel, null, $orderGoods);
//        $couponService->sendCoupon();
//    }

    public function onOrderCreated(AfterOrderCreatedEvent $event)
    {
        $this->event = $event;
        $orderModel = $this->event->getOrderModel();
        $orderGoods = $orderModel->hasManyOrderGoods;//订单商品
        $couponService = new CouponService($orderModel, null, $orderGoods);
        $couponService->sendCouponLog();
    }

    public function onOrderCanceled(AfterOrderCanceledEvent $event)
    {
        $this->event = $event;
        $orderModel = $this->event->getOrderModel();
        $orderGoods = $orderModel->hasManyOrderGoods;//订单商品
        foreach ($orderGoods as $good) {
            OrderGoodsCoupon::uniacid()
                ->where(['order_goods_id'=>$good->id,'status'=>OrderGoodsCoupon::WAIT_STATUS])
                ->update(['status'=>OrderGoodsCoupon::CLOSE_STATUS]);
        }
    }

    //退款成功监听
    public function onOrderRefund(AfterOrderRefundSuccessEvent $event)
    {
        $refund = $event->getModel();

        $order_goods_ids = $refund->refundOrderGoods->pluck('order_goods_id')->all();

        if ($order_goods_ids) {
            OrderGoodsCoupon::uniacid()
                ->whereIn('order_goods_id', $order_goods_ids)
                ->where('status', OrderGoodsCoupon::WAIT_STATUS)
                ->update(['status'=>OrderGoodsCoupon::CLOSE_STATUS]);
        }
    }

    /**
     * @param $events
     * 监听多个事件
     */
    public function subscribe($events)
    {
        $events->listen(
            AfterOrderRefundSuccessEvent::class,
            CouponDiscount::class . '@onOrderRefund'
        );

        $events->listen(
            AfterOrderCreatedEvent::class,
            CouponDiscount::class . '@onOrderCreated'
        );
//        $events->listen(
//            AfterOrderReceivedEvent::class,
//            CouponDiscount::class . '@onOrderReceived'
//        );
        $events->listen(
            AfterOrderReceivedEvent::class,
            CouponDiscount::class . '@deductionAwardPoint'
        );
        $events->listen(
            AfterOrderCanceledEvent::class,
            CouponDiscount::class . '@onOrderCanceled'
        );
    }
}