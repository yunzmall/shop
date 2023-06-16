<?php

namespace app\backend\modules\refund\services;

use app\backend\modules\refund\services\operation\OrderCloseAndRefund;
use app\backend\modules\refund\services\operation\RefundBatchResend;
use app\backend\modules\refund\services\operation\RefundChangePrice;
use app\backend\modules\refund\services\operation\RefundClose;
use app\backend\modules\refund\services\operation\RefundComplete;
use app\backend\modules\refund\services\operation\RefundConsensus;
use app\backend\modules\refund\services\operation\RefundExchangeComplete;
use app\backend\modules\refund\services\operation\RefundPass;
use app\backend\modules\refund\services\operation\RefundReject;
use app\backend\modules\refund\services\operation\RefundResend;
use app\backend\modules\refund\services\operation\RefundSendBack;
use app\backend\modules\refund\services\operation\RefundCancel;
use app\common\models\Order;
use Illuminate\Support\Facades\DB;
use app\backend\modules\refund\models\RefundApply;
use app\common\events\order\AfterOrderRefundedEvent;
use app\common\exceptions\AdminException;
use app\common\exceptions\AppException;
use app\common\events\order\AfterOrderRefundRejectEvent;
use app\common\events\order\AfterOrderRefundSuccessEvent;

/**
 * Created by PhpStorm.
 * Author:
 * Date: 2017/4/13
 * Time: 下午2:21
 */
class RefundOperationService
{

    //驳回
    public static function refundReject($params)
    {
        $refundApply = RefundReject::find($params['refund_id']);
        $result = DB::transaction(function () use ($refundApply) {
            return $refundApply->execute();
        });

        return $result;
    }

    /**
     * 申请通过
     * @param $params
     * @return bool
     * @throws AppException
     */
    public static function refundPass($params)
    {
        $refundApply = RefundPass::find($params['refund_id']);

        $result = DB::transaction(function () use ($refundApply) {
            return $refundApply->execute();
        });

        return $result;
    }


    /**
     * 同意退款
     * @param $params
     * @return array|bool|mixed|string|void
     * @throws AppException
     */
    public static function refundPay($params)
    {
        try {
            $payStatus = (new \app\common\modules\refund\services\RefundService())->pay($params['refund_id']);
        } catch (\Exception $e) {
            \Log::debug("<----{$params['refund_id']}--售后退款支付失败------:".$e->getMessage());
            throw new AppException($e->getMessage());
        }

        return $payStatus;
    }



    /**
     * 订单关闭并退款
     * @param Order $order
     * @return array|bool|mixed|string|void
     * @throws AppException
     */
    public static function orderCloseAndRefund(Order $order)
    {
        try {

            if ($order->status == Order::CLOSE) {
                throw new AppException('订单已关闭，无需重复操作');
            }

            $refundApply = new OrderCloseAndRefund();

            $refundApply->setRelation('order',$order);

             DB::transaction(function () use ($refundApply) {
                return $refundApply->execute();
            });

            if (bccomp($refundApply->price, 0, 2) !== 1) {
                RefundOperationService::refundConsensus(['refund_id'=> $refundApply->id]);
            } else {
                $payStatus = (new \app\common\modules\refund\services\RefundService())->pay($refundApply->id);
            }
            return $payStatus;
        } catch (\Exception $e) {
            \Log::debug("<----{$order->order_sn}--订单关闭并退款支付失败------:".$e->getMessage(),[$e,$refundApply]);
            throw new AppException($e->getMessage());
        }
    }

    /**
     * @param $params
     * @return bool
     * @throws AppException
     */
    public static function refundBatchResend($params)
    {
        $refundApply = RefundBatchResend::find($params['refund_id']);

        $result = DB::transaction(function () use ($refundApply) {
            return $refundApply->execute();
        });

        return $result;
    }

    /**
     * @param $params
     * @return bool
     * @throws AppException
     */
    public static function refundResend($params)
    {
        $refundApply = RefundResend::find($params['refund_id']);

        $result = DB::transaction(function () use ($refundApply) {
            return $refundApply->execute();
        });

        return $result;
    }

    /**
     * @param $params
     * @return mixed
     */
    public static function refundClose($params)
    {
        $refundApply = RefundClose::find($params['refund_id']);

        $result = DB::transaction(function () use ($refundApply) {
            return $refundApply->execute();
        });

        return $result;
    }

    /**
     * todo 前端接口 迁移到一起管理
     * @return mixed
     * @throws AppException
     */
    public static function refundSendBack($params)
    {
        $refundSend = RefundSendBack::find($params['refund_id']);

        return $refundSend->execute();
    }

    /**
     * todo 前端接口  迁移到一起管理
     * @return mixed
     * @throws AppException
     */
    public static function refundCancel($params)
    {
        $refundCancel = RefundCancel::find($params['refund_id']);

        return $refundCancel->execute();

    }

    /**
     *  todo 前端接口 迁移到一起管理
     * @return mixed
     * @throws AppException
     */
    public static function refundExchangeComplete($params)
    {
        $refundComplete = RefundExchangeComplete::find($params['refund_id']);

        $result = DB::transaction(function () use ($refundComplete) {
            return $refundComplete->execute();
        });
        return $result;
    }


    public static function refundConsensus($params)
    {
        $refundApply = RefundConsensus::find($params['refund_id']);

        $result = DB::transaction(function () use ($refundApply) {
            return $refundApply->execute();
        });

        return $result;
    }

    public static function refundComplete($params)
    {
        $id = $params['refund_id']?:$params['id'];
        $refundApply = RefundComplete::find($id);

        if (!isset($refundApply)) {
            throw new AdminException('(ID:'.$id.')退款申请不存在');
        }

        $result = DB::transaction(function () use ($refundApply) {
            return $refundApply->execute();
        });

        return $result;
    }

    public static function refundChangePrice($params)
    {

        $refundApply = RefundChangePrice::find($params['refund_id']);

        $result = DB::transaction(function () use ($refundApply) {
            return $refundApply->execute();
        });

        return $result;

    }
}