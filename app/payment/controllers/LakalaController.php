<?php
/**
 * Created by PhpStorm.
 * 
 *
 *
 * Date: 2022/2/17
 * Time: 17:24
 */
namespace app\payment\controllers;

use app\common\helpers\Url;
use app\common\models\PayOrder;
use app\common\models\PayType;
use app\payment\PaymentController;
use Yunshop\LaKaLaPay\services\LaKaLaPay;

class LakalaController extends PaymentController
{
    const LAKALA_WECHAT = 83;
    const LAKALA_ALIPAY = 84;


    // 支付宝同步回调
    public function redirectUrlAlipay()
    {
        $this->redirectOrder(self::LAKALA_ALIPAY);
    }

    // 微信同步回调
    public function redirectUrlWecaht()
    {
        $this->redirectOrder(self::LAKALA_WECHAT);
    }

    public function notifyUrlAlipay()
    {
        return $this->processOrder(self::LAKALA_WECHAT);
    }

    public function notifyUrlWechat()
    {
        return $this->processOrder(self::LAKALA_WECHAT);
    }

    /**
     * 支付回调参数
     *
     * @param $order_no
     * @param $parameter
     * @return array
     */
    public function setData($order_no, $trade_no, $total_fee, $pay_type_id, $pay_type)
    {
        return [
            'total_fee' => $total_fee,
            'out_trade_no' => $order_no,
            'trade_no' => $trade_no,
            'unit' => 'fen',
            'pay_type' => $pay_type,
            'pay_type_id' => $pay_type_id,
        ];
    }

    protected function processOrder($type)
    {
        $request = request()->all();
        $payOrder = PayOrder::where('out_order_no', $request['partner_order_id'])->first();

        if (!$payOrder) {
            \Log::debug(self::class. '--: 未找到支付订单');
            return false;
        }

        \YunShop::app()->uniacid = $payOrder->uniacid;
        \Setting::$uniqueAccountId = $payOrder->uniacid;

        $lakala = new LaKaLaPay;

        $sign = $lakala->generateSign($request['time'], $request['nonce_str']);
        if ($sign != $request['sign']) {
            \Log::debug(self::class. '订单校验失败, 非法订单');
            return false;
        }
        $currentPayType = $this->currentPayType($type);

        $data = $this->setData($request['partner_order_id'], $request['channel_order_id'], $request['real_fee'], $currentPayType['id'], $currentPayType['name']);
        $this->payResutl($data);
        \Log::debug(self::class . '订单支付成功--订单号: ' . $request['order_no']);

        echo 'success';
    }

    private function redirectOrder($type)
    {
        $request = request()->all();
        $order_info = unserialize($request['afford']);
        \YunShop::app()->uniacid = $order_info['i'];

        $currentPayType = $this->currentPayType($type);
        $payOrder = PayOrder::getPayOrderInfo($order_info['order_no'])->first();

        if ($payOrder && $payOrder->status == 1) {
            $data = $this->setData($order_info['order_no'], $request['trade_no'], ($request['rmb_fee'] * 100), $currentPayType['id'], $currentPayType['name']);
            $this->payResutl($data);
            \Log::debug(self::class . '订单支付成功--订单号: ' . $order_info['order_no']);

            redirect(Url::absoluteApp('member/payYes', ['i' => $order_info['i']]))->send();
        }

        if ($payOrder && $payOrder->status == 2) {
            redirect(Url::absoluteApp('member/payYes', ['i' => $order_info['i']]))->send();
        }

        if (!$payOrder) {
            redirect(Url::absoluteApp('member/payErr', ['i' => $order_info['i']]))->send();
        }
    }

    public function currentPayType($payId)
    {
        return PayType::find($payId);
    }


}