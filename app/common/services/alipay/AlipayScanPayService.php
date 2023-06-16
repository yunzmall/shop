<?php
/**
 * Created by PhpStorm.
 * User: yunzhong
 * Date: 2019/10/11
 * Time: 15:14
 */

namespace app\common\services\alipay;


use app\common\exceptions\AppException;
use app\common\helpers\Url;
use app\common\models\OrderPay;
use app\common\models\PayOrder;
use app\common\models\PayType;
use app\common\services\Pay;
use app\common\services\Utils;
use Yansongda\Pay\Exceptions\GatewayException;
use Yansongda\Pay\Gateways\Alipay\Support;
use Yunshop\StoreCashier\store\common\service\RefreshToken;
use Yunshop\StoreCashier\store\models\StoreAlipaySetting;

class AlipayScanPayService extends Pay
{
    /**
     * 订单支付/充值
     * @param array $data
     * @return mixed
     * @throws AppException
     * @throws \Exception
     */
    public function doPay($data = [])
    {
        if ($data['pay_type'] == 'alipay') {
            $third_type = '支付宝扫码支付';
        } else {
            $third_type = '支付宝人脸支付';
        }
        $op = '微信扫码支付 订单号：' . $data['order_no'];
        $pay_order_model = $this->log(1, $third_type, $data['amount'], $op, $data['order_no'], Pay::ORDER_STATUS_NON, \YunShop::app()->getMemberId());

        $alipay_set = \Setting::get('shop.alipay_set');
        $config = [
            'app_id' => $alipay_set['app_id'],
            'ali_public_key' => $alipay_set['alipay_public_key'],
            'private_key' => $alipay_set['merchant_private_key'],
            //当面付不用异步回调，订单已经支付成功了
//            'notify_url' => Url::shopSchemeUrl('payment/alipay/jsapiNotifyUrl.php'),
            'return_url' => Url::shopSchemeUrl('payment/alipay/returnUrl.php'),
            'app_auth_token' => ''
        ];
        $order = [
            'body' => \YunShop::app()->uniacid,
            'subject' => $data['subject'], 0, 256,
            'out_trade_no' => $data['pay_sn'],
            'total_amount' => $data['amount'],
            'auth_code' => $data['auth_code'],
            'http_method' => 'GET',
        ];
        if ($this->getRoyalty($alipay_set)) {
            $order['extend_params'] = ['royalty_freeze' => true];
        }
        if (!$alipay_set['app_type']) {
            //第三方应用授权令牌,商户授权系统商开发模式下使用
            $config['app_auth_token'] = $this->getAuthToken();
            $order['sys_service_provider_id'] = $alipay_set['pid'];
        }
        try {
            $res = \Yansongda\Pay\Pay::alipay($config)->pos($order);
        } catch (GatewayException $e) {
            $raw = $e->raw;
            $method = str_replace('.', '_', 'alipay.trade.pay') . '_response';
            if ($raw[$method]['code'] == '10000' || $raw[$method]['code'] == '10003') {
                $res = $raw[$method];
            } else {
                throw $e;
            }
        }
        $pay_success = 0;
        if ($res['code'] == '10000') {
            //支付成功
            $pay_success = 1;
        } else if ($res['code'] == '10003') {
            //轮询
            for ($i = 1; $i < 12; $i++) {
                sleep(5);
                //查询交易状态
                try {
                    $loop = \Yansongda\Pay\Pay::alipay($config)->find(['out_trade_no' => $data['pay_sn']]);
                } catch (GatewayException $e) {
                    \Log::debug('支付宝扫码付查询支付结果异常:' . $e->getMessage());
                    $loop = [];
                }
                if ($loop['code'] == '10000' && isset($loop['trade_status']) && in_array($loop['trade_status'], ["TRADE_FINISHED", "TRADE_SUCCESS"])) {
                    //支付成功，结束轮询
                    $pay_success = 1;
                    break;
                } elseif ($loop['code'] == '10000' && isset($loop['trade_status']) && $loop['trade_status'] == 'TRADE_CLOSED') {
                    //交易失败，结束轮询
                    \Log::error('pos支付宝支付失败', [$res, $loop]);
                    throw new AppException('支付宝支付失败!!!' . $loop['msg'] . '--' . $loop['sub_msg']);
                }
            }
        }
        if ($pay_success == 1) {
            self::payRequestDataLog($data['order_no'], $pay_order_model->type, $pay_order_model->third_type, json_encode($res));
            $res['royalty'] = $this->getRoyalty($alipay_set);
            return $res;
        } else {
            \Log::error('pos支付宝支付失败', [$res]);
            throw new AppException('支付宝支付失败!!!' . $res['msg'] . '--' . $res['sub_msg']);
        }
    }

    /**
     * 退款
     *
     * @param $out_trade_no 订单号
     * @param $totalmoney 订单总金额
     * @param $refundmoney 退款金额
     * @return mixed
     */
    public function doRefund($out_trade_no, $totalmoney, $refundmoney)
    {

        if (app('plugins')->isEnabled('store-cashier')) {
            $orderPay = OrderPay::where('pay_sn', $out_trade_no)->first();
            $storeOrder = \Yunshop\StoreCashier\common\models\StoreOrder::where('order_id', $orderPay->orders->first()->id)->first();
            if ($storeOrder) {
                request()->offsetSet('store_id', $storeOrder->store_id);
            } else {
                throw new AppException('请确认订单是否为门店订单');
            }
        } else {
            throw new AppException('未开启门店-收银台插件');
        }

        $this->setPayService();
        $out_refund_no = $this->setUniacidNo(\YunShop::app()->uniacid);
        $op = '支付宝扫码退款 订单号：' . $out_trade_no . '退款单号：' . $out_refund_no . '退款金额：' . $refundmoney;

        if (empty($out_trade_no)) {
            throw new AppException('支付单号不存在');
        }

        $pay_type_id = OrderPay::get_paysn_by_pay_type_id($out_trade_no);
        $pay_type_name = PayType::get_pay_type_name($pay_type_id);
        $refund_order = $this->refundlog(Pay::PAY_TYPE_REFUND, $pay_type_name, $refundmoney, $op, $out_trade_no, Pay::ORDER_STATUS_NON, 0);
        //支付宝交易单号
        $pay_order_model = PayOrder::getPayOrderInfo($out_trade_no)->first();
        if (!$pay_order_model) {
            return false;
        }

        $refund_data = array(
            'out_trade_no' => $pay_order_model->out_order_no,
            'trade_no' => $pay_order_model->trade_no,
            'refund_amount' => $refundmoney,
            'refund_reason' => '正常退款',
            'out_request_no' => $out_refund_no
        );

        $res = $this->payService->refund($refund_data);

        if ($res['code'] == '10000') {
            $refund_order->status = Pay::ORDER_STATUS_COMPLETE;
            $refund_order->trade_no = $res['trade_no'];
            $refund_order->save();
            $this->payResponseDataLog($out_trade_no, '支付宝扫码支付', json_encode($res));
            return true;
        }

        \Log::debug('---alipay-扫码/人脸---', [$refund_data, $res]);
        throw new AppException($res['msg'] . '-' . $res['sub_msg']);
    }

    /**
     * 提现
     *
     * @param $member_id 提现者用户ID
     * @param $out_trade_no 提现批次单号
     * @param $money 提现金额
     * @param $desc 提现说明
     * @param $type 只针对微信 1-企业支付(钱包) 2-红包
     * @return mixed
     */
    public function doWithdraw($member_id, $out_trade_no, $money, $desc, $type)
    {
        // TODO: Implement doWithdraw() method.
    }

    /**
     * 构造签名
     *
     * @return mixed
     */
    public function buildRequestSign()
    {
        // TODO: Implement buildRequestSign() method.
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function getAuthToken()
    {
        $storeAlipaySetting = StoreAlipaySetting::uniacid()->where('store_id', request()->store_id)->first();
        if (!$storeAlipaySetting) {
            throw new AppException('门店未授权支付宝');
        }
        $app_auth_token = $storeAlipaySetting->app_auth_token;
        return $app_auth_token;
    }

    private function getRoyalty($set)
    {
        $result = 0;
        if ($set['royalty']) {
            $sub_set = StoreAlipaySetting::where('store_id', request()->store_id)->first();
            if ($sub_set->royalty && !$sub_set->no_authorized_royalty) {
                $result = 1;
            }
        }
        return $result;
    }

    private function setPayService()
    {
        $alipay_set = \Setting::get('shop.alipay_set');
        $config = [
            'app_id' => $alipay_set['app_id'],
            'ali_public_key' => $alipay_set['alipay_public_key'],
            'private_key' => $alipay_set['merchant_private_key'],
            'notify_url' => Url::shopSchemeUrl('payment/alipay/jsapiNotifyUrl.php'),
            'return_url' => Url::shopSchemeUrl('payment/alipay/returnUrl.php'),
            'app_auth_token' => ''
        ];

        if (!$alipay_set['app_type']) {
            //第三方应用授权令牌,商户授权系统商开发模式下使用
            $config['app_auth_token'] = $this->getAuthToken();
        }

        $this->payService = \Yansongda\Pay\Pay::alipay($config);
    }
}