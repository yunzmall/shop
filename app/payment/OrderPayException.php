<?php
/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 2023/2/20
 * Time: 15:53
 */

namespace app\payment;


use app\common\exceptions\AppException;
use app\common\models\Order;
use app\common\models\OrderGoods;
use app\common\models\OrderPay;
use app\common\models\PayCallbackException;

class OrderPayException
{
    protected $resultData;

    public function __construct(array $resultData)
    {
        $this->resultData = $resultData;
    }

    /**
     * @param OrderPay $orderPay
     * @return bool true 出现异常 false 无异常
     */
    public function handle(OrderPay $orderPay)
    {

        foreach ($orderPay->orders as $order) {
            //if ($order->status > Order::WAIT_PAY) {
                //throw new AppException('(ID:' . $order->id . ')订单已付款,请勿重复付款');
                //return true;
            //}
            if ($order->status == Order::CLOSE) {
                //throw new AppException('(ID:' . $order->id . ')订单已关闭,无法付款');
                $orderPay->updatePayStatus($this->resultData['pay_type_id']);
                $this->saveErrorException($orderPay->pay_sn, PayCallbackException::ORDER_CLOSE, '(ID:' . $order->id . ')订单已关闭,无法付款');
                return true;
            }
        }

        return false;
    }

    public function saveErrorException($pay_sn,$code,$msg = '')
    {
        $payError = PayCallbackException::uniacid()->where('pay_sn', $pay_sn)->first();

        if (is_null($payError)) {
            $payError = new PayCallbackException(['uniacid' => \YunShop::app()->uniacid,'frequency' => 0]);

        }

        $logData = [
            'frequency' => $payError->frequency + 1,
            'pay_sn' => $pay_sn,
            'pay_type_id' => $this->resultData['pay_type_id'],
            'error_code' => $code,
            'error_msg' => $msg,
            'result' => $this->resultData,
            'response' => request()->input(),
            'record_at' => time(),
        ];

        $payError->fill($logData);

        $bool = $payError->save();

        if ($bool) {
            return $payError;
        }

        return $bool;
    }
}