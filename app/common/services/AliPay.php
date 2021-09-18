<?php
/**
 * Created by PhpStorm.
 * Author: 芸众商城 www.yunzshop.com
 * Date: 2017/3/17
 * Time: 下午12:01
 */

namespace app\common\services;

use app\common\exceptions\AppException;
use app\common\helpers\Client;
use app\common\models\Order;
use app\common\models\OrderPay;
use app\common\models\PayOrder;
use app\common\models\PayType;
use app\common\services\alipay\MobileAlipay;
use app\common\services\alipay\sdk\AopCertClient;
use app\common\services\alipay\WebAlipay;
use app\common\services\alipay\WapAlipay;
use app\common\models\Member;
use app\common\services\alipay\AopClient;
use app\common\services\alipay\AlipayTradeRefundRequest;

class AliPay extends Pay
{
    private $_pay = null;
    private $pay_type;

    public function __construct()
    {
        $this->_pay = $this->createFactory();
        $this->pay_type = config('app.pay_type');
    }

    private function createFactory()
    {
        $type = $this->getClientType();
        switch ($type) {
            case 'web':
                $pay = new WebAlipay();
                break;
            case 'mobile':
                $pay = new MobileAlipay();
                break;
            case 'wap':
                $pay = new WapAlipay();
                break;
            default:
                $pay = null;
        }

        return $pay;
    }

    /**
     * 获取客户端类型
     *
     * @return string
     */
    private function getClientType()
    {
        if (Client::isMobile()) {
            return 'wap';
        } elseif (Client::is_app()) {
            return 'mobile';
        } else {
            return 'web';
        }
    }

    /**
     * 订单支付/充值
     *
     * @param $subject 名称
     * @param $body 详情
     * @param $amount 金额
     * @param $order_no 订单号
     * @param $extra 附加数据
     * @return strin5
     */
    public function doPay($data, $payType = 2)
    {
        $op = "支付宝订单支付 订单号：" . $data['order_no'];
        $pay_type_name = PayType::get_pay_type_name($payType);
        $this->log($data['extra']['type'], $pay_type_name, $data['amount'], $op, $data['order_no'], Pay::ORDER_STATUS_NON, \YunShop::app()->getMemberId());

        if ($payType == PayFactory::PAY_APP_ALIPAY) {
            \Log::info('云打包支付宝APP支付,支付订单号：'. $data['order_no']);
            return true;
        }

        return $this->_pay->doPay($data);
    }

    public function doRefund($out_trade_no, $totalmoney, $refundmoney='0')
    {
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
        if ($pay_order_model) {

            $refund_data = array(
                'out_trade_no' => $pay_order_model ->out_order_no,
                'trade_no' => $pay_order_model ->trade_no,
                'refund_amount' => $refundmoney,
                'refund_reason' => '正常退款',
                'out_request_no' => $out_refund_no
            );

            if ($pay_type_id == 10) {
                $result = $this->apprefund($refund_data);
                if ($result) {
                    $this->changeOrderStatus($refund_order, Pay::ORDER_STATUS_COMPLETE, $result['trade_no']);
                    $this->payResponseDataLog($out_trade_no, '支付宝APP退款', json_encode($result));
                    return true;
                } else {
                    return false;
                }
            } else {

                $set = \Setting::get('shop.pay');
                if (isset($set['alipay_pay_api']) && $set['alipay_pay_api'] == 1) {
                    $result =  $this->alipayRefund2($refund_data, $set);
                    if ($result) {
                        $this->changeOrderStatus($refund_order, Pay::ORDER_STATUS_COMPLETE, $result['trade_no']);
                        $this->payResponseDataLog($out_trade_no, '商城支付宝2.0新接口退款', json_encode($result));
                        return true;
                    } else {
                        return false;
                    }
                } else {
                    $alipay = app('alipay.web');
                    $alipay->setOutTradeNo($pay_order_model->trade_no);
                    $alipay->setTotalFee($refundmoney);

                    return $alipay->refund($out_refund_no);
                }
            }
        } else {
            return false;
        }
    }

    private function changeOrderStatus($model, $status, $trade_no)
    {
        $model->status = $status;
        $model->trade_no = $trade_no;
        $model->save();
    }

    public function alipayRefund2($refund_data, $set)
    {
        $aop = new AopClient();
        $request = new AlipayTradeRefundRequest();
        $aop->gatewayUrl = 'https://openapi.alipay.com/gateway.do';
        $aop->appId = decrypt($set['alipay_app_id']);
        $aop->alipayrsaPublicKey = decrypt($set['rsa_public_key']);
        $aop->rsaPrivateKey = decrypt($set['rsa_private_key']);
        $aop->apiVersion = '1.0';
        $aop->signType = 'RSA2';
        $aop->postCharset='UTF-8';
        $aop->format='json';
        $json = json_encode($refund_data);
        $request->setBizContent($json);
        $result = $aop->execute($request);
        $res = json_decode($result, 1);
        if(!empty($res)&&$res['alipay_trade_refund_response']['code'] == '10000'){
            return $res['alipay_trade_refund_response'];
        } else {
            throw new AppException($res['alipay_trade_refund_response']['msg'] . '-' . $res['alipay_trade_refund_response']['sub_msg']);
        }
    }

    public function apprefund($refund_data)
    {
        $set = \Setting::get('shop_app.pay');
        $aop = new AopClient();
        $request = new AlipayTradeRefundRequest();
        $aop->gatewayUrl = 'https://openapi.alipay.com/gateway.do';
        $aop->appId = $set['alipay_appid'];
        $aop->alipayrsaPublicKey = $set['refund_alipay_sign_public'] ?: $set['alipay_sign_public'];
        $aop->rsaPrivateKey = $set['refund_alipay_sign_private'] ?: $set['alipay_sign_private'];
        $aop->apiVersion = '1.0';
        $aop->signType = $set['refund_newalipay'] == 1 ? 'RSA2' : 'RSA';
        $aop->postCharset='UTF-8';
        $aop->format='json';
        $json = json_encode($refund_data);
        $request->setBizContent($json);
        $result = $aop->execute($request);
        $res = json_decode($result, 1);
        if(!empty($res)&&$res['alipay_trade_refund_response']['code'] == '10000'){
            return $res['alipay_trade_refund_response'];
        } else {
            throw new AppException($res['alipay_trade_refund_response']['msg'] . '-' . $res['alipay_trade_refund_response']['sub_msg']);
        }
    }

    public function doWithdraw($member_id, $out_trade_no, $money, $desc = '', $type=1)
    {
        $batch_no = $this->setUniacidNo(\YunShop::app()->uniacid);

        $op = '支付宝提现 批次号：' . $out_trade_no . '提现金额：' . $money;
        $pay_order_model = $this->withdrawlog(Pay::PAY_TYPE_REFUND, $this->pay_type[Pay::PAY_MODE_ALIPAY], $money, $op, $out_trade_no, Pay::ORDER_STATUS_NON, $member_id);

        $alipay = app('alipay.web');

        $alipay->setTotalFee($money);

        $member_info = Member::getUserInfos($member_id)->first();

        if ($member_info) {
            $member_info = $member_info->toArray();
        } else {
            throw new AppException('会员不存在');
        }

        if (!empty($member_info['yz_member']['alipay']) && !empty($member_info['yz_member']['alipayname'])) {
            $account = $member_info['yz_member']['alipay'];
            $name = $member_info['yz_member']['alipayname'];
        } else {
            throw new AppException('没有设定支付宝账号');
        }

        //请求数据日志
        $pay_data = [
            'out_biz_no' => $out_trade_no,
            'payee_account' => $account,
            'amount'     => $money,
            'payee_real_name' => $name,
        ];
        $this->payRequestDataLog($pay_order_model->id, $pay_order_model->type, $pay_order_model->type, json_encode($pay_data));

        if (\Setting::get('shop.pay')['alipay_transfer']) {
            return $this->withdrawCert($account, $name, $out_trade_no, $money);
        }

        $result = $alipay->withdraw($account, $name, $out_trade_no, $batch_no);
        //响应数据
        $this->payResponseDataLog($pay_order_model->out_order_no, $pay_order_model->type, json_encode($result));
        return $result;
    }

    public function doBatchWithdraw($withdraws)
    {
        $account = [];
        $name    = [];

        $batch_no = $this->setUniacidNo(\YunShop::app()->uniacid);

        foreach ($withdraws as $withdraw) {
            $op = '支付宝提现 批次号：' . $withdraw->withdraw_sn . '提现金额：' . $withdraw->actual_amounts;


            $this->withdrawlog(Pay::PAY_TYPE_REFUND, $this->pay_type[Pay::PAY_MODE_ALIPAY], $withdraw->actual_amounts, $op, $withdraw->withdraw_sn, Pay::ORDER_STATUS_NON, $withdraw->member_id);
        }

        $alipay = app('alipay.web');

        foreach ($withdraws as $withdraw) {
            $member_info = Member::getUserInfos($withdraw->member_id)->first();

            if ($member_info) {
                $member_info = $member_info->toArray();
            } else {
                throw new AppException('会员不存在');
            }

            if (!empty($member_info['yz_member']['alipay']) && !empty($member_info['yz_member']['alipayname'])) {
                $account[] = $member_info['yz_member']['alipay'];
                $name[]    = $member_info['yz_member']['alipayname'];
            } else {
                throw new AppException('没有设定支付宝账号');
            }
        }

        return $alipay->batchWithdraw($account, $name, $withdraws, $batch_no);
    }

    public function buildRequestSign()
    {
        // TODO: Implement buildRequestSign() method.
    }

    /**
     * 支付宝单笔转账接口 证书签名模式
     * @param $account string 支付宝账号
     * @param $name string 支付宝账号真实姓名
     * @param $out_trade_no string 转账单号
     * @param $money mixed 转账金额
     * @return array
     */
    public function withdrawCert($account, $name, $out_trade_no, $money)
    {
        $data = [
            'out_biz_no' => $out_trade_no,
            'trans_amount'     => $money,
            'product_code' => 'TRANS_ACCOUNT_NO_PWD',
            'biz_scene' => 'DIRECT_TRANSFER',
            'order_title' => '佣金提现',
            'remark' => '测试',
            'payee_info' => [
                'identity'=> $account,
                'identity_type'=>'ALIPAY_LOGON_ID',
                'name' => $name,
            ]

        ];

        $aop = $this->aopCert();

        $result = $aop->execute('alipay.fund.trans.uni.transfer',$data);

        if (!$result['code']) {
            \Log::debug('-----支付宝转账失败-----', $result['data']);
            return ['errno'=> 1, 'message'=> $result['msg']];
        }

        return  ['errno'=> 0, 'message'=> $result['msg']];
    }

    protected function aopCert()
    {

        $pay = \Setting::get('shop.pay');
        //Utils::dataDecrypt($pay);

        $config = [
            'appId' => $pay['alipay_transfer_app_id'],
            'merchantPrivateKey' => $pay['alipay_transfer_private'],
            'merchantCertPath' => $pay['alipay_app_public_cert'],
            'alipayCertPath' => $pay['alipay_public_cert'],
            'alipayRootCertPath' => $pay['alipay_root_cert'],
        ];

        $aop = new AopCertClient();

        $aop->setConfig($config);

//        $aop->setConfigValue('gatewayHost','openapi.alipaydev.com'); //沙箱测试
        return $aop;
    }
}