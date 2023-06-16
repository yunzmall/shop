<?php
/**
 * Created by PhpStorm.
 * Author:
 * Date: 2017/3/17
 * Time: 下午12:01
 */

namespace app\common\services\alipay;

use app\common\exceptions\AppException;
use app\common\helpers\Url;
use app\common\models\OrderPay;
use app\common\models\PayOrder;
use app\common\models\PayType;
use app\common\services\Pay;
use app\common\services\Utils;
use Yunshop\StoreCashier\store\common\service\RefreshToken;
use Yunshop\StoreCashier\store\models\StoreAlipaySetting;

class AlipayJsapiPayService extends Pay
{

    /**
     * 订单支付
     * @param $data
     * @param $payType
     * @return mixed
     * @throws \Exception
     */
    public function doPay($data = [], $payType = 49)
    {
        $op = "支付宝订单支付 订单号：" . $data['order_no'];
        $pay_type_name = PayType::get_pay_type_name($payType);
        $this->log($data['extra']['type'], $pay_type_name, $data['amount'], $op, $data['order_no'], Pay::ORDER_STATUS_NON, \YunShop::app()->getMemberId());
        $alipay_set = \Setting::get('shop.alipay_set');
        $config = [
            'app_id' => $alipay_set['app_id'],
            'ali_public_key' => $alipay_set['alipay_public_key'],
            'private_key' => $alipay_set['merchant_private_key'],
            'notify_url' => Url::shopSchemeUrl('payment/alipay/jsapiNotifyUrl.php'),
            'return_url' => Url::shopSchemeUrl('payment/alipay/returnUrl.php'),
            'app_auth_token' => ''
        ];
        $order = [
            'body' => \YunShop::app()->uniacid,
            'subject' => mb_substr($data['subject'], 0, 256),
            'out_trade_no' => $data['order_no'] . '_' . \YunShop::app()->uniacid . '_' . $this->getRoyalty($alipay_set),
            'total_amount' => $data['amount'],
            'http_method' => 'GET'
        ];
        if ($this->getRoyalty($alipay_set)) {
            $order['extend_params'] = ['royalty_freeze' => true];
        }
        if (!$alipay_set['app_type']) {
            //第三方应用授权令牌,商户授权系统商开发模式下使用
            $config['app_auth_token'] = $this->getAuthToken($alipay_set);
            $order['sys_service_provider_id'] = $alipay_set['pid'];
        }
        return \Yansongda\Pay\Pay::alipay($config)->wap($order)->getTargetUrl();
    }

    public function doRefund($out_trade_no, $totalmoney, $refundmoney = '0')
    {
        if ($refundmoney <= 0) {
            throw new AppException('退款金额不能小于等于0');
        }


        if (app('plugins')->isEnabled('store-cashier')) {
            $orderPay = OrderPay::where('pay_sn', $out_trade_no)->first();
            $storeOrder = \Yunshop\StoreCashier\common\models\StoreOrder::where('order_id', $orderPay->orders->first()->id)->first();
            if (!$storeOrder) {
                throw new AppException('请确认订单是否为门店订单');
            }
        } else {
            throw new AppException('未开启门店收银');
        }

        $out_refund_no = $this->setUniacidNo(\YunShop::app()->uniacid);
        $op = '支付宝退款 订单号：' . $out_trade_no . '退款单号：' . $out_refund_no . '退款总金额：' . $totalmoney;
        if (empty($out_trade_no)) {
            throw new AppException('参数错误');
        }
        $pay_type_id = OrderPay::get_paysn_by_pay_type_id($out_trade_no);
        $pay_type_name = PayType::get_pay_type_name($pay_type_id);
        $refund_order = $this->refundlog(Pay::PAY_TYPE_REFUND, $pay_type_name, $totalmoney, $op, $out_trade_no, Pay::ORDER_STATUS_NON, 0);
        //支付宝交易单号
        $pay_order_model = PayOrder::getPayOrderInfo($out_trade_no)->first();
        if (empty($pay_order_model)) {
            return false;
        }

        $refund_data = array(
            'out_trade_no' => $pay_order_model->out_order_no,
            'trade_no' => $pay_order_model->trade_no,
            'refund_amount' => $refundmoney,
            'refund_reason' => '正常退款',
            'out_request_no' => $out_refund_no
        );

        // 获取人脸支付插件设置
        $alipaySet = \Setting::get('shop.alipay_set');
        if (!$alipaySet) {
            throw new AppException('请检查人脸支付设置');
        }

        $alipayStoreSet = StoreAlipaySetting::uniacid()->where('store_id', $storeOrder->store_id)->first();
        if (!$alipayStoreSet) {
            throw new AppException('请检查门店人脸支付设置');
        }

        Utils::dataDecrypt($set);
        $config = [
            'app_id' => $alipaySet['app_id'],
            'ali_public_key' => $alipaySet['alipay_public_key'],
            'private_key' => $alipaySet['merchant_private_key'],
            'app_auth_token' => $alipayStoreSet['app_auth_token'],
        ];
        $result = \Yansongda\Pay\Pay::alipay($config)->refund($refund_data);
        if (!empty($result) && $result['code'] == '10000') {
            $refund_order->status = Pay::ORDER_STATUS_COMPLETE;
            $refund_order->trade_no = $result['trade_no'];
            $refund_order->save();
            $this->payResponseDataLog($out_trade_no, '支付宝(服务商)wap退款', json_encode($result));
            return true;
        }
        \Log::debug('---alipay-app---', [$refund_data, $result]);
        throw new AppException($result['msg'] . '-' . $result['sub_msg']);
    }

    public function doWithdraw($member_id, $out_trade_no, $money, $desc = '', $type = 1)
    {
        return false;
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

    /**
     * @return mixed
     * @throws \Exception
     */
    private function getAuthToken($set)
    {
        $app_auth_token = '';
        if (!$set['app_type']) {
            $storeAlipaySetting = StoreAlipaySetting::uniacid()->where('store_id', request()->store_id)->first();
            if (!$storeAlipaySetting) {
                throw new AppException('门店未授权支付宝');
            }
            if ($storeAlipaySetting->expires_in < time()) {
                $storeAlipaySetting = RefreshToken::refreshToken();
            }
            $app_auth_token = $storeAlipaySetting->app_auth_token;
        }
        return $app_auth_token;
    }

    public function buildRequestSign()
    {
        // TODO: Implement buildRequestSign() method.
    }
}