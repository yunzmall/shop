<?php
/**
 * Author:
 * Date: 2019/6/3
 * Time: 下午3:10
 */

namespace app\common\services\wechat;


use app\common\exceptions\AppException;
use app\common\helpers\Url;
use app\common\models\McMappingFans;
use app\common\models\PayOrder;
use app\common\services\Pay;
use app\common\models\OrderPay;
use app\common\models\PayType;
use app\common\services\wechat\lib\WxPayApi;
use app\common\services\wechat\lib\WxPayConfig;
use app\common\services\wechat\lib\WxPayMicroPay;
use app\common\services\wechat\lib\WxPayOrderQuery;
use app\common\services\wechat\lib\WxPayRefund;

class WechatScanPayService extends Pay
{
    public $set = null;
    public $config = null;

    public function __construct()
    {
        $this->config = new WxPayConfig();
        $this->set = $set = \Setting::get('shop.wechat_set');
    }

    /**
     * 支付
     * @param array $data
     * @return mixed|string
     * @throws AppException
     * @throws \app\common\services\wechat\lib\WxPayException
     */

    public function doPay($data = [])
    {
//        if (\YunShop::request()->type != 9) {
//            throw new AppException('不是商家APP 微信扫码支付不可用');
//        }

        $pay_name = $data['pay_type'] == 'wechat_scan' ? '微信扫码支付' : '微信人脸支付';
        $op = $pay_name.' 订单号：' . $data['order_no'];
        $pay_order_model = $this->log(1, $pay_name, $data['amount'] / 100, $op, $data['order_no'], Pay::ORDER_STATUS_NON, $this->getMemberId());

        /* 支付请求对象 */
        $wxPay = new WxPayMicroPay();
        //设置商品或支付单简要描述
        $wxPay->SetBody($data['body']);
        //设置商家数据包，在查询API和支付通知中原样返回，该字段主要用于商户携带订单的自定义数据
        $wxPay->SetAttach($data['extra']);
        //设置商户系统内部的订单号
        $wxPay->SetOut_trade_no($data['pay_sn']);
        //设置订单总金额
        $wxPay->SetTotal_fee($data['amount']);
        //设置扫码支付授权码
        $wxPay->SetAuth_code($data['auth_code']);

        $response = WxPayApi::micropay(new WxPayConfig(), $wxPay);

        if ($response['result_code'] != 'SUCCESS') {
            // todo 订单取消
            throw new AppException('微信支付失败：'.$response['err_code_des']);
        }

        //更新openid
        $response = $this->setOpenId($response);
        $response['profit_sharing'] = (new WxPayConfig())->GetProfitSharing() == 'Y' ?1:0;
        //请求数据日志
        self::payRequestDataLog($data['order_no'], $pay_order_model->type,
            $pay_order_model->third_type, json_encode($response));

        return $response;
    }

    /**
     * 退款
     */
    public function doRefund($out_trade_no, $totalmoney, $refundmoney)
    {
        $totalmoney = intval($totalmoney * 100);
        $refundmoney = intval($refundmoney * 100);
        $this->getSubMchId($out_trade_no);

        $wxPayApi = new WxPayApi;
        $config = new WxPayConfig;
        $wxPayRefund = new WxPayRefund;

        $wxPayRefund->SetOut_trade_no($out_trade_no);
        $wxPayRefund->SetTotal_fee($totalmoney);
        $wxPayRefund->SetRefund_fee($refundmoney);
        $out_refund_no = $this->setUniacidNo(\YunShop::app()->uniacid);
        $wxPayRefund->SetOut_refund_no($out_refund_no);

        if (!$config->GetSubMerchantId()) {
            throw new AppException('请先配置门店子商户参数');
        }

        $wxPayRefund->SetSubMchId($config->GetSubMerchantId());

        $pay_type_id = OrderPay::get_paysn_by_pay_type_id($out_trade_no);
        $pay_type_name = PayType::get_pay_type_name($pay_type_id);
        $op = '微信退款 订单号：' . $out_trade_no . '退款单号：' . $out_refund_no . '退款总金额：' . $totalmoney;
        $payOrderModel = $this->refundlog(Pay::PAY_TYPE_REFUND, $pay_type_name, $refundmoney, $op, $out_trade_no, Pay::ORDER_STATUS_NON, 0);

        $result = $wxPayApi::refund($config, $wxPayRefund);

        \Log::debug('微信扫码退款记录', $result);

        if ($result['return_code'] == 'SUCCESS' && $result['result_code'] == 'SUCCESS') {
            $payOrderModel->status = Pay::ORDER_STATUS_COMPLETE;
            $payOrderModel->trade_no = $result['transaction_id'];
            $payOrderModel->save();
            return true;
        } elseif ($result['return_code'] == 'SUCCESS') {
            throw new AppException($result['err_code_des']);
        } else {
            throw new AppException($result['return_msg']);
        }
    }


    /**
     * 提现
     */
    public function doWithdraw($member_id, $out_trade_no, $money, $desc, $type)
    {

    }

    /**
     * 支付回调操作
     *
     * @param $data
     */
    public function payResult($data)
    {

    }

    public function getMemberId()
    {
        return \YunShop::app()->getMemberId() ? : 0;
    }

    /**
     * 构造签名
     *
     * @return mixed
     */
    function buildRequestSign()
    {
        // TODO: Implement buildRequestSign() method.
    }

    /**
     *获取带参数的请求URL
     */
    function getRequestURL() {

        $this->buildRequestSign();

        $reqPar =json_encode($this->parameters);
        \Log::debug('-----请求参数----', $reqPar);

        $requestURL = $this->getGateURL() . "?data=".base64_encode($reqPar);

        return $requestURL;
    }

    function setOpenId($data)
    {
        if (!$this->set['is_independent'] && $this->set['sub_appid'] && $this->set['sub_mchid']) {
            $data['openid'] = $data['sub_openid'];
        }
        return $data;
    }

    // 获取门店子商户号
    private function getSubMchId($outTradeNo)
    {
        if (app('plugins')->isEnabled('store-cashier')) {
            $orderPay = OrderPay::where('pay_sn', $outTradeNo)->first();
            $storeId = \Yunshop\StoreCashier\common\models\StoreOrder::where('order_id', $orderPay->orders->first()->id)->value('store_id');
            if ($storeId) {
                request()->offsetSet('store_id', $storeId);
            }
        }

        if (!isset($storeId)) {
            throw new AppException('请确认订单是否属于门店订单');
        }
    }
}