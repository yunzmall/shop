<?php
/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 2021/12/21
 * Time: 10:50
 */

namespace app\payment\controllers;

use app\common\helpers\Url;
use app\common\models\PayOrder;
use app\payment\PaymentController;

class SandpayController extends PaymentController
{
    const SANDPAY_ALIPAY = 81;
    const SANDPAY_WECHAT = 82;

    // 微信同步回调
    public function returnUrlWechat()
    {
        $this->returnResponse();
    }

    // 支付宝同步回调
    public function returnUrlAlipay()
    {
        $this->returnResponse();
    }

    // 支付宝支付成功回调
    public function notifyUrlAlipay()
    {
        $data = $this->verifyHandle();
        $total_fee = $this->handlerFee($data->body->totalAmount);
        $data = $this->setData($data->body->orderCode, $data->body->tradeNo, $total_fee, self::SANDPAY_ALIPAY, '杉德微信支付');

        \Log::debug('-------- 杉德支付宝支付: 验证数据正常->更新订单状态 start --------');
        $this->payResutl($data);
        \Log::debug('-------- 杉德支付宝支付: 验证数据正常->更新订单状态 end --------');

        echo $this->success();
        exit;
    }

    // 微信退款成功回调
    public function refundNotifyUrlAlipay()
    {
        $this->verifyHandle();
        \Log::debug('-------- 杉德支付宝: 退款通过 --------');
        echo $this->success();
        exit;
    }

    // 微信支付成功回调
    public function notifyUrlWechat()
    {
        $data = $this->verifyHandle();
        $total_fee = $this->handlerFee($data->body->totalAmount);
        $data = $this->setData($data->body->orderCode, $data->body->tradeNo, $total_fee, self::SANDPAY_WECHAT, '杉德微信支付');

        \Log::debug('-------- 杉德微信支付: 验证数据正常->更新订单状态 start --------');
        $this->payResutl($data);
        \Log::debug('-------- 杉德微信支付: 验证数据正常->更新订单状态 end --------');

        echo $this->success();
        exit;
    }

    // 微信退款成功回调
    public function refundNotifyUrlWechat()
    {
        $this->verifyHandle();
        \Log::debug('-------- 杉德微信: 退款通过 --------');
        echo $this->success();
        exit;
    }


    public function returnResponse()
    {
        if ($_SERVER['REDIRECT_STATUS'] == 200) {
            redirect(Url::absoluteApp('member/payYes', ['i' => request()->input('i')]))->send();
        } else {
            redirect(Url::absoluteApp('member/payErr', ['i' => request()->input('i')]))->send();
        }
    }

    protected function validator($param)
    {
        if (is_null($_POST[$param])) {
            \Log::debug("-------- 获取杉德支付回调失败 未检测到第三方参数: $param--------");
            exit;
        }
        return $_POST[$param];
    }

    // 获取公众号
    protected function getUniacid($orderCode)
    {
        $payOrder = PayOrder::select('uniacid')->where('out_order_no', $orderCode)->first();
        if ($payOrder) {
            return $payOrder->uniacid;
        }
        \Log::debug('商城订单号未找到: ' . json_encode($orderCode));
        exit;
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
            'total_fee' => $total_fee * 100,
            'out_trade_no' => $order_no,
            'trade_no' => $trade_no,
            'unit' => 'fen',
            'pay_type' => $pay_type,
            'pay_type_id' => $pay_type_id,
        ];
    }

    // 杉德获取回来的金额是12位数的. 需要做处理 '000000000001' = '0.01'
    public function handlerFee($amount)
    {
        return bcdiv($amount, 100,2);
    }

    public function success()
    {
        return 'respCode=000000';
    }

    /**
     * @return mixed|void
     * @throws \app\common\exceptions\ShopException
     */
    public function verifyHandle()
    {
        list($dataJson, $data, $sign) = $this->verifyParams();
        $uniacid = $this->getUniacid($data->body->orderCode);
        \YunShop::app()->uniacid = $uniacid;

        $payConfig = new \Yunshop\Sandpay\services\CommonPayConfig;
        $verifyRes = $payConfig->verify($dataJson, $sign);

        if (!$verifyRes) {
            \Log::debug('杉德支付----验证回调签名未通过');
            exit;
        }
        return $data;
    }

    /**
     * @return array|void
     */
    public function verifyParams()
    {
        $dataJson = $this->validator('data');
        $data = json_decode($dataJson);
        $sign = $this->validator('sign');

        if ($data->head->respCode !== '000000') {
            \Log::debug(self::class . ' 杉德错误代码: ' . $data->head->respCode);
            \Log::debug(self::class . ' 杉德错误代码: ' . $data->head->respMsg);
            exit;
        }
        return array($dataJson, $data, $sign);
    }

}