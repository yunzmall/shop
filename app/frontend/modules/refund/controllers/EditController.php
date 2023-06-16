<?php
/**
 * Created by PhpStorm.
 * Author:  
 * Date: 2017/4/14
 * Time: 上午11:59
 */

namespace app\frontend\modules\refund\controllers;

use app\common\components\ApiController;
use app\common\exceptions\AppException;
use app\common\models\Order;
use app\frontend\modules\refund\models\RefundApply;
use app\frontend\modules\refund\services\operation\RefundEditApply;
use app\frontend\modules\refund\services\RefundService;

class EditController extends ApiController
{
    public function index(\Illuminate\Http\Request $request){
        $this->validate([
            'refund_id' => 'required|integer',
        ]);


        $refundApply = RefundApply::detail()->find($request->query('refund_id'));
        if(!isset($refundApply)){
            throw new AppException('未找到该退款申请');
        }

        $order = Order::find($refundApply->order_id);
        if (!isset($order)) {
            throw new AppException('订单不存在');
        }

        $refundTypes = RefundService::getOptionalType($order);

        $send_back_way = RefundService::getSendBackWay($order);

        $send_back_way_data = RefundService::getSendBackWayData($refundApply);

        $data = compact('refundApply','refundTypes','send_back_way','send_back_way_data');


        $refundedPrice =  \app\common\models\refund\RefundApply::uniacid()
            ->select('order_id','price','apply_price', 'freight_price', 'other_price')
            ->where('order_id', $order->id)
            ->where('status', '>=', RefundApply::COMPLETE)
            ->get();

        $orderOtherPrice = $this->getOrderOtherPrice($order);

        //可退运费
        $data['refundable_freight'] = max(bcsub($order->dispatch_price, $refundedPrice->sum('freight_price'),2),0);
        //订单可退其他费用
        $data['refundable_other'] = max(bcsub($orderOtherPrice, $refundedPrice->sum('other_price'),2),0);

        return $this->successJson('成功',$data);
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
                return $order->service_fee_amount;
            }
        }

        return $order->fee_amount + $order->service_fee_amount;
    }

    public function store(\Illuminate\Http\Request $request)
    {

        $this->validate([
//            'reason' => 'required|string',
            'content' => 'sometimes|string',
            'refund_type' => 'required|integer',
            'refund_id' => 'required|integer'
        ],request(), []);


        $refundApply = RefundEditApply::find($request->input('refund_id'));


        if ($refundApply->uid != \YunShop::app()->getMemberId()) {
            throw new AppException('无效申请,该订单属于其他用户');
        }

        if (!isset($refundApply)) {
            throw new AppException('退款申请不存在');
        }

        $refundApply->execute();


        return $this->successJson('成功', $refundApply->toArray());
    }
}