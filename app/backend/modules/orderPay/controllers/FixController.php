<?php
/**
 * Created by PhpStorm.
 * User: shenyang
 * Date: 2018/8/15
 * Time: 下午3:44
 */

namespace app\backend\modules\orderPay\controllers;

use app\backend\modules\orderPay\fix\DoublePaymentRepair;
use app\common\components\BaseController;
use app\common\exceptions\AppException;
use app\common\models\OrderPay;

class FixController extends BaseController
{
    /**
     * @throws \app\common\exceptions\AppException
     */
    public function refund()
    {
        /**
         * @var OrderPay $orderPay
         */
        $orderPay = OrderPay::find(request('order_pay_id'));
        if(!$orderPay){
            throw new AppException('未找到支付记录'.request('order_pay_id'));
        }
        $a = (new DoublePaymentRepair($orderPay))->handle();


        if ($a !== false) {
            $orderPay->orders->each(function ($order) {
                //原路退款操作成功关闭该支付记录下的所以订单
                if ($order->status > \app\common\models\Order::WAIT_PAY) {
                    \app\frontend\modules\order\services\OrderService::orderForceClose(['order_id' => $order->id]);
                }
            });
        }

        foreach ($a as $msg) {
            echo "<span>{$msg}</span><br>";
        }

//        echo '<button onclick="history.back()">返回</button>';
        exit();
    }
}