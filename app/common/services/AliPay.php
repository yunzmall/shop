<?php
/**
 * Created by PhpStorm.
 * Author:
 * Date: 2017/3/17
 * Time: 下午12:01
 */

namespace app\common\services;

use app\common\exceptions\AppException;
use app\common\helpers\Url;
use app\common\models\MemberShopInfo;
use app\common\models\OrderPay;
use app\common\models\PayOrder;
use app\common\models\PayType;
use app\common\services\alipay\MobileAlipay;
use app\common\services\alipay\WebAlipay;
use app\common\services\alipay\WapAlipay;
use app\common\services\alipay\AlipayTradeRefundRequest;
use Yansongda\Pay\Gateways\Alipay\Support;

class AliPay extends Pay
{

    protected $payService;
    private $pay_type;

    public function __construct()
    {
        $this->pay_type = config('app.pay_type');
    }

    public function setPayService($is_withdraw = 0)
    {
        $pay = \Setting::get('shop.pay');
        Utils::dataDecrypt($pay);
        if ($is_withdraw) {
            $config = [
                'app_id' => $pay['alipay_transfer_app_id'],
                'notify_url' => Url::shopSchemeUrl('payment/alipay/preNotifyUrl.php'),
                'return_url' => Url::shopSchemeUrl('payment/alipay/returnUrl.php'),
                'private_key' => $pay['alipay_transfer_private'],
                // 应用公钥证书路径
                'app_cert_public_key' => $pay['alipay_app_public_cert'],
                // 支付宝根证书路径
                'alipay_root_cert'    => $pay['alipay_root_cert'],
                // 公钥证书
                'ali_public_key'      => $pay['alipay_public_cert'],
            ];
            $this->payService = \Yansongda\Pay\Pay::alipay($config);
        } else {
            $config = [
                'app_id' => $pay['alipay_app_id'],
                'ali_public_key' => $pay['rsa_public_key'],
                'private_key' => $pay['rsa_private_key'],
                'notify_url' => Url::shopSchemeUrl('payment/alipay/preNotifyUrl.php'),
                'return_url' => Url::shopSchemeUrl('payment/alipay/returnUrl.php'),
            ];
            $this->payService = \Yansongda\Pay\Pay::alipay($config);
        }
    }

    /**
     * @param $data
     * @param $payType
     * @return bool|mixed
     */
    public function doPay($data, $payType = 2)
    {
        $this->setPayService();
        $op = "支付宝订单支付 订单号：" . $data['order_no'];
        $pay_type_name = PayType::get_pay_type_name($payType);
        $this->log($data['extra']['type'], $pay_type_name, $data['amount'], $op, $data['order_no'], Pay::ORDER_STATUS_NON, \YunShop::app()->getMemberId());

        if ($payType == PayFactory::PAY_APP_ALIPAY) {
            \Log::info('云打包支付宝APP支付,支付订单号：' . $data['order_no']);
            return true;
        }
        $order = [
            'body' => \YunShop::app()->uniacid,
            'subject' => $data['subject'],
            'out_trade_no' => \YunShop::app()->uniacid . '_' . $data['order_no'],
            'total_amount' => $data['amount'],
            'http_method' => 'GET'
        ];
        if (request()->pc == 1) {
            return $this->payService->web($order)->getTargetUrl();
        }
        return $this->payService->wap($order)->getTargetUrl();    }

    public function doRefund($out_trade_no, $totalmoney, $refundmoney = '0')
    {
        $this->setPayService();
        $out_refund_no = $this->setUniacidNo(\YunShop::app()->uniacid);
        $op = '支付宝退款 订单号：' . $out_trade_no . '退款单号：' . $out_refund_no . '退款金额：' . $refundmoney;
        if (empty($out_trade_no)) {
            throw new AppException('参数错误');
        }
        $pay_type_id = OrderPay::get_paysn_by_pay_type_id($out_trade_no);
        $pay_type_name = PayType::get_pay_type_name($pay_type_id);
        $refund_order = $this->refundlog(Pay::PAY_TYPE_REFUND, $pay_type_name, $refundmoney, $op, $out_trade_no, Pay::ORDER_STATUS_NON, 0);
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
        $third_type = '商城支付宝2.0新接口退款';
        if ($pay_type_id == 10) {
            // app支付的修改配置
            $pay = \Setting::get('shop_app.pay');
            $config = [
                'app_id' => $pay['alipay_appid'],
                'ali_public_key' => $pay['refund_alipay_sign_public'] ?: $pay['alipay_sign_public'],
                'private_key' => $pay['refund_alipay_sign_private'] ?: $pay['alipay_sign_private'],
            ];
            Support::getInstance()->clear();
            $this->payService = \Yansongda\Pay\Pay::alipay($config);
            $third_type = '支付宝APP退款';

        }
        $res = $this->payService->refund($refund_data);
        if (!empty($res) && $res['code'] == '10000') {
            $refund_order->status = Pay::ORDER_STATUS_COMPLETE;
            $refund_order->trade_no = $res['trade_no'];
            $refund_order->save();
            $this->payResponseDataLog($out_trade_no, $third_type, json_encode($res));
            return true;
        }
        \Log::debug('---alipay-app---', [$refund_data, $res]);
        throw new AppException($res['msg'] . '-' . $res['sub_msg']);
    }

    public function doWithdraw($member_id, $out_trade_no, $money, $desc = '', $type = 1)
    {
        $this->setPayService(1);
        $op = '支付宝提现 批次号：' . $out_trade_no . '提现金额：' . $money;
        $pay_order_model = $this->withdrawlog(Pay::PAY_TYPE_REFUND, $this->pay_type[Pay::PAY_MODE_ALIPAY], $money, $op, $out_trade_no, Pay::ORDER_STATUS_NON, $member_id);

        $member_info = MemberShopInfo::select(['alipay', 'alipayname'])->where('member_id', $member_id)->first();
        $account = $member_info['alipay'];
        $name = $member_info['alipayname'];
        if (empty($account) || empty($name)) {
            throw new AppException('没有设定支付宝账号');
        }
        //请求数据日志
        $pay_data = [
            'out_biz_no' => $out_trade_no,
            'payee_account' => $account,
            'amount' => $money,
            'payee_real_name' => $name,
        ];
        $this->payRequestDataLog($pay_order_model->id, $pay_order_model->type, $pay_order_model->type, json_encode($pay_data));
        $data = [
            'out_biz_no' => $out_trade_no,
            'trans_amount' => $money,
            'product_code' => 'TRANS_ACCOUNT_NO_PWD',
            'biz_scene' => 'DIRECT_TRANSFER',
            'order_title' => '佣金提现',
            'payee_info' => [
                'identity' => $account,
                'identity_type' => 'ALIPAY_LOGON_ID',
                'name' => $name,
            ]
        ];
        $result = $this->payService->transfer($data);
        if ($result['code'] != '10000') {
            \Log::debug('-----支付宝转账失败-----', $result['data']);
            return ['errno' => 1, 'message' => $result['msg']];
        }
        $pay_refund_model = \app\common\models\PayWithdrawOrder::getOrderInfo($out_trade_no);
        if ($pay_refund_model) {
            $pay_refund_model->status = 2;
            $pay_refund_model->trade_no = $out_trade_no;
            $pay_refund_model->save();
        }
        event(new \app\common\events\finance\AlipayWithdrawEvent($out_trade_no));
        return ['errno' => 0, 'message' => $result['msg']];
    }


    public function buildRequestSign()
    {
        // TODO: Implement buildRequestSign() method.
    }

}
