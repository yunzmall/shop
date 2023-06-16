<?php

namespace app\frontend\modules\refund\controllers;

use app\common\components\ApiController;
use app\common\events\order\OrderRefundApplyDataEvent;
use app\common\events\order\OrderRefundApplyEvent;
use app\common\exceptions\AppException;
use app\common\models\refund\RefundApply;
use app\common\services\SystemMsgService;
use app\framework\Http\Request;
use app\frontend\models\Order;
use app\frontend\modules\refund\services\RefundService;
use app\frontend\modules\refund\services\RefundMessageService;
use app\frontend\modules\order\services\MiniMessageService;
use Illuminate\Support\Facades\DB;

/**
 * Created by PhpStorm.
 * Author:
 * Date: 2017/4/12
 * Time: 下午4:24
 */
class ApplyController extends ApiController
{

    protected function getOrder()
    {
        return Order::select(['id', 'status', 'plugin_id', 'goods_price', 'order_goods_price', 'price', 'refund_id', 'dispatch_price', 'fee_amount', 'service_fee_amount', 'pay_time'])
            ->with(['orderGoods']);
    }


    public function index(Request $request)
    {

        $this->validate([
            'order_id' => 'required|integer'
        ]);
        $order = $this->getOrder()->find($request->query('order_id'));
        if (!isset($order)) {
            throw new AppException('订单不存在');
        }

        if ($order->refund_id) {
            throw new AppException('已存在售后申请，处理中');
        }

        $data = RefundService::refundApplyData($order);


        event(($event = new OrderRefundApplyDataEvent($data)));

        return $this->successJson('成功', $event->getData());

        //预约订单限制
//        if (!is_null(\app\common\modules\shop\ShopConfig::current()->get('store_reserve_refund'))) {
//            $class = array_get(\app\common\modules\shop\ShopConfig::current()->get('store_reserve_refund'), 'class');
//            $function = array_get(\app\common\modules\shop\ShopConfig::current()->get('store_reserve_refund'), 'function');
//            $plugin_res = $class::$function($request->query('order_id'));
//            if(!$plugin_res['res']) {
//                throw new AppException($plugin_res['msg']);
//            }
//        }
//
//
//        //处理订单可退款商品数量
//        $order->orderGoods->map(function ($orderGoods) {
//            $orderGoods->refundable_total = $orderGoods->total - $orderGoods->after_sales['refunded_total'];
//            $orderGoods->unit_price = bankerRounding($orderGoods->payment_amount / $orderGoods->total);
//        });
//
//
//        $refundTypes = RefundService::getOptionalType($order);
//
//        $data = compact('order','refundTypes');
//
//        $refundedPrice = \app\common\models\refund\RefundApply::getAfterSales($order->id)->get();
//
//
//        $orderOtherPrice = $this->getOrderOtherPrice($order);
//
//        //这里减去运费和其他费用是因为前端直接拿这个字段当订单金额，但是售后现在把运费分离出来了。
//        $data['order']['price'] = max($order->price - $order->dispatch_price - $orderOtherPrice,0);
//
//        //可退运费
//        $data['refundable_freight'] = max(bcsub($order->dispatch_price, $refundedPrice->sum('freight_price'),2),0);
//        //订单可退其他费用
//        $data['refundable_other'] = max(bcsub($orderOtherPrice, $refundedPrice->sum('other_price'),2),0);
//
//        //支持部分退款的订单类型，平台订单，供应商订单，中台供应链
//        $data['support_batch'] = in_array($order->plugin_id, [0,92,120]);
//
//        $data['send_back_way'] = RefundService::getSendBackWay($order);
//
//        event(($event = new OrderRefundApplyDataEvent($data)));
//
//        return $this->successJson('成功', $event->getData());
    }

    //订单其他费用退款
    protected function getOrderOtherPrice($order)
    {
        //预约商品服务费不退
        if (!is_null(\app\common\modules\shop\ShopConfig::current()->get('store_reserve_refund_price')) && $order->status == Order::COMPLETE) {
            $class = array_get(\app\common\modules\shop\ShopConfig::current()->get('store_reserve_refund_price'), 'class');
            $function = array_get(\app\common\modules\shop\ShopConfig::current()->get('store_reserve_refund_price'), 'function');
            $plugin_res = $class::$function($order);
            if($plugin_res['res']) {
                return $order->fee_amount;
            }
        }

        return $order->fee_amount + $order->service_fee_amount;
    }


    public function store(Request $request)
    {
        $this->validate([
//            'reason' => 'required|string',
            'content' => 'sometimes|string',
            'refund_type' => 'required|integer',
            'order_id' => 'required|integer'
        ], $request,[
            'reason.required'=>'退款原因未选择',
            'refund_type.required'=>'退款方式未选择',
        ]);

        //预约订单限制
        if (!is_null(\app\common\modules\shop\ShopConfig::current()->get('store_reserve_refund'))) {
            $class = array_get(\app\common\modules\shop\ShopConfig::current()->get('store_reserve_refund'), 'class');
            $function = array_get(\app\common\modules\shop\ShopConfig::current()->get('store_reserve_refund'), 'function');
            $plugin_res = $class::$function($request->input('order_id'));
            if(!$plugin_res['res'])
            {
                throw new AppException($plugin_res['msg']);
            }
        }

        $order = Order::find($request->input('order_id'));
        if (!isset($order)) {
            throw new AppException('订单不存在');
        }
        if ($order->uid != \YunShop::app()->getMemberId()) {
            throw new AppException('无效申请,该订单属于其他用户');
        }
        if ($order->status < Order::WAIT_SEND) {
            throw new AppException('订单未付款,无法退款');
        }

//        if ($order->hasOneRefundApply && $order->hasOneRefundApply->isRefunding()) {
//            throw new AppException('申请已提交,处理中');
//        }

        $existRefund = RefundApply::uniacid()
            ->where('order_id', $order->id)
            ->where('status', '>=',RefundApply::WAIT_CHECK)
            ->where('status', '<', RefundApply::COMPLETE)->count();

        if ($existRefund) {
            throw new AppException('申请已提交,处理中');
        }

        $refundApply = new \app\frontend\modules\refund\services\operation\RefundApply();
        $refundApply->setRelation('order',$order);

        DB::transaction(function()use($refundApply){
            $refundApply->execute();
        });

        return $this->successJson('成功', $refundApply->toArray());

    }
}