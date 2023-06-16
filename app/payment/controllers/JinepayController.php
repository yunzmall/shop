<?php
/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 2022/12/1
 * Time: 14:13
 */

namespace app\payment\controllers;

use app\common\helpers\Url;
use app\common\models\PayOrder;
use app\common\models\PayType;
use app\payment\PaymentController;
use Yunshop\Jinepay\models\NotifyLog;
use Yunshop\Jinepay\models\XmlToArray;
use Yunshop\Jinepay\services\JinePay;

class JinepayController extends PaymentController
{
    const PAY_ID = 104;

    public function notifyUrl()
    {
        $strData = request()->getContent();
        \Log::debug(self::class . '--: 锦银E付回调通知: ' . $strData);

        $strData = explode('&', $strData);
        // 遍历data 把值用 = 号切割转成一维关联数组
        $data = [];
        foreach ($strData as $value) {
            $explode = explode('=', $value);
            $data[$explode[0]] = $explode[1];
        }

        $payOrder = PayOrder::where('out_order_no', $data['out_trade_no'])->first();

        if (!$payOrder) {
            \Log::debug(self::class . '--: 未找到支付订单');
            echo 'fail';
            return;
        }
        \YunShop::app()->uniacid = $payOrder->uniacid;
        \Setting::$uniqueAccountId = $payOrder->uniacid;

        $jinePay = new JinePay;
        try {
            $res = $jinePay->verifySign($data);
        } catch (\Exception $e) {
            \Log::debug(self::class . '--验签失败 失败原因: ' . $e->getMessage());
            $res = false;
        }

        if (!$res) {
            // 出发自主查询
            $jinePay->setReqData([
                'service' => 'query_order_service',
                'out_trade_no' => $data['out_trade_no'],
                'partner' => $jinePay->set['merchant'],
            ]);
            $queryData = XmlToArray::convert($jinePay->queryOrder());
            $verify = $jinePay->verifySign($queryData);

            // 自主查询付款成功后
            if ($queryData['trade_state'] === '0' && $verify) {
                $res = true;
            }
        }

        if (!$res) {
            \Log::debug(self::class . '--: 验签失败');
            echo 'fail';
            return;
        }

        if ($data['trade_state'] === '0') {
            NotifyLog::create([
                'uniacid' => $payOrder->uniacid,
                'out_trade_no' => $data['out_trade_no'],
                'params' => json_encode($data, 320),
            ]);
            $currentPayType = $this->currentPayType(self::PAY_ID);
            $payResultData = $this->setData($data['out_trade_no'], $data['out_trade_no'], ($data['total_fee'] * 100), $currentPayType['id'], $currentPayType['name']);
            $this->payResutl($payResultData);

            \Log::debug(self::class . '订单支付成功--订单号: ' . $data['out_trade_no']);

            echo 'success';
            exit;
        } else {
            \Log::debug(self::class . '订单支付失败--订单号: ' . json_encode($data, 320));
            echo 'fail';
            exit;
        }

    }

    public function returnUrl()
    {
        $request = request()->all();
        $order_info = unserialize(base64_decode($request['afford']));

        \YunShop::app()->uniacid = $order_info['i'];
        \Setting::$uniqueAccountId = $order_info['i'];

        $currentPayType = $this->currentPayType(self::PAY_ID);
        $payOrder = PayOrder::getPayOrderInfo($order_info['order_no'])->first();

        $jinePay = new JinePay;
        $jinePay->setReqData([
            'service' => 'query_order_service',
            'out_trade_no' => $order_info['order_no'],
            'partner' => $jinePay->set['merchant'],
        ]);

        $res = $jinePay->queryOrder();

        \Log::debug(self::class . '-锦银E付同步通知-: ' . $res);

        $data = XmlToArray::convert($res);
        $verify = $jinePay->verifySign($data);

        if ($payOrder && $data['trade_state'] === '0' && $verify) {
            $data = $this->setData($order_info['out_trade_no'], $order_info['out_trade_no'], ($request['total_fee'] * 100), $currentPayType['id'], $currentPayType['name']);
            $this->payResutl($data);
            \Log::debug(self::class . '订单支付成功--订单号: ' . $order_info['order_no']);
            redirect(Url::absoluteApp('member/payYes', ['i' => $order_info['i']]))->send();
        }

        if (!$payOrder) {
            \Log::debug(self::class . '订单支付失败--订单号: ' . $order_info['order_no']);
            \Log::debug(self::class . '订单支付失败原因: ' . json_encode($data, 320));
            redirect(Url::absoluteApp('member/payErr', ['i' => $order_info['i']]))->send();
        }
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

    public function currentPayType($payId)
    {
        return PayType::find($payId);
    }
}