<?php

namespace app\common\services\finance;

use app\common\events\order\AfterOrderCanceledEvent;
use app\common\events\order\AfterOrderRefundSuccessEvent;
use app\common\exceptions\ShopException;
use app\common\facades\Setting;
use app\common\models\finance\PointLog;
use app\common\models\Order;
use app\common\models\OrderGoods;
use app\common\models\point\ParentRewardLog;
use app\common\services\finance\PointService;
use app\common\services\point\ParentReward;
use Illuminate\Support\Facades\DB;

/**
 * 处理退货时需要扣除赠送的积分
 */
class PointRefund
{
    /**
     * @var Order
     */
    protected $orderModel;


    //退款完成，扣除已退款订单商品赠送积分
    public function refundReturn(AfterOrderRefundSuccessEvent $event)
    {

        if (!Setting::get('point.set.point_refund')) {
            \Log::info(self::class . '检测到当前后台积分设置->没有开启积分扣除');
            return;
        };

        $refund = $event->getModel();

        $this->orderModel = $event->getOrderModel();
        $where = [
            'order_id' => $this->orderModel->id,
            'uniacid' => $this->orderModel->uniacid,
            'member_id' => $this->orderModel->uid,
        ];

        $order_goods_ids = $refund->refundOrderGoods->pluck('order_goods_id')->all();

        //只获得首次退款的订单商品ID
        $canRollbackGoodsIds = OrderGoods::whereIn('id', $order_goods_ids)
            ->where('is_refund', '<=', OrderGoods::FIRST_REFUND)
            ->pluck('id')->toArray();

        if ($canRollbackGoodsIds) { //上级赠送积分回退
            (new ParentReward())->refund(0, $canRollbackGoodsIds);
        }

        $where['point_mode'] = PointService::POINT_MODE_GOODS_REFUND;
        $hasPointRefund = PointLog::where($where)->whereIn('order_goods_id', $canRollbackGoodsIds)->first();




        if ($hasPointRefund) {
            \Log::info($refund->id.',当前售后已有退款积分操作, 不再执行积分扣除');
            return;
        }

        $where['point_mode'] = PointService::POINT_MODE_GOODS;
        $pointLogs = PointLog::where($where)->whereIn('order_goods_id', $canRollbackGoodsIds)->get();

        if ($pointLogs->isEmpty()) {
            return;
        }

        foreach ($pointLogs as $pointLog) {
            $point_data = [
                'point_income_type' => PointService::POINT_INCOME_LOSE,
                'point_mode'        => PointService::POINT_MODE_GOODS_REFUND,
                'member_id'         => $pointLog->member_id,
                'order_id'          => $this->orderModel->id,
                'point'             => bcsub(0, $pointLog->point, 2),
                'remark'            => '订单('.$refund->order->order_sn.')商品退款,退回赠送积分',
            ];
            $point_service = new PointService($point_data);
            $point_service->deductPoint();
        }

        \Log::info('退款ID' . $refund->id . ' ，赠送的积分扣除成功');
    }

    public function exec(AfterOrderCanceledEvent $event)
    {
        if (!Setting::get('point.set.point_refund')) {
            \Log::info(self::class . '检测到当前后台积分设置->没有开启积分扣除');
            return;
        };

        $this->orderModel = $event->getOrderModel();

        \Log::info( '执行积分扣除');
        $this->deductOrder();
        $this->deductOrderGoods();
        (new ParentReward())->refund($this->orderModel->id);

        \Log::info('订单ID' . $this->orderModel->order_sn . ' 退货的积分扣除成功');
    }

    //先扣除订单赠送的
    public function deductOrder()
    {
        $where = [
            'order_id' => $this->orderModel->id,
            'uniacid' => $this->orderModel->uniacid,
            'member_id' => $this->orderModel->uid,
        ];


        $hasPointRefund = PointLog::where($where)->where('point_mode',PointService::POINT_MODE_ORDER_REFUND)->first();

        if ($hasPointRefund) {
            \Log::info(self::class . '当前订单已有退款积分操作, 不再执行积分扣除');
            return;
        }

        $orderPointLogs = PointLog::where($where)->where('point_mode',PointService::POINT_MODE_ORDER)->get();
        if ($orderPointLogs->isNotEmpty()) {
            $orderPointLogs->each(function ($point_log) {
                $point_data = [
                    'point_income_type' => PointService::POINT_INCOME_LOSE,
                    'point_mode'        => PointService::POINT_MODE_ORDER_REFUND,
                    'order_id'          => $this->orderModel->id,
                    'member_id'         => $point_log->member_id,
                    'point'             => bcsub(0, $point_log->point, 2),
                    'remark'            => '订单[' . $this->orderModel->order_sn . '关闭,退回订单赠送积分',
                ];
                $point_service = new PointService($point_data);
                $point_service->deductPoint();
            });
        }
    }

    //扣除商品赠送的积分
    public function deductOrderGoods()
    {
        $where = [
            'order_id' => $this->orderModel->id,
            'uniacid' => $this->orderModel->uniacid,
            'member_id' => $this->orderModel->uid,
        ];

        $orderPointLogs = PointLog::where($where)->where('point_mode',PointService::POINT_MODE_GOODS)->get();

        if ($orderPointLogs->isNotEmpty()) {

            foreach ($orderPointLogs as $pointLogs) {

                //已退款商品订单关闭不需要扣除，退款完成监听会扣除
                if ($pointLogs->order_goods_id) {
                    $orderGoods = $this->orderModel->orderGoods->where('id',$pointLogs->order_goods_id)->first();
                    if ($orderGoods && $orderGoods->isRefund()) {
                        continue;
                    }
                }

                $point_data = [
                    'point_income_type' => PointService::POINT_INCOME_LOSE,
                    'point_mode'        => PointService::POINT_MODE_GOODS_REFUND,
                    'order_id'          => $this->orderModel->id,
                    'member_id'         => $pointLogs->member_id,
                    'point'             => bcsub(0, $pointLogs->point, 2),
                    'remark'            => '订单('.$this->orderModel->order_sn.')关闭,退回商品赠送积分',
                ];
                $point_service = new PointService($point_data);
                $point_service->deductPoint();
            }
        }
    }

    protected function getPointMode($point_mode)
    {

        if ($point_mode == 1) {
            return PointService::POINT_MODE_GOODS_REFUND;
        }
        return PointService::POINT_MODE_ORDER_REFUND;
    }

    protected function getRemark($point_mode)
    {

        if ($point_mode == 1) {
            return '商品退货退回赠送积分';
        }
        return '订单[' . $this->orderModel->order_sn . ']退货退回赠送积分';
    }
}
