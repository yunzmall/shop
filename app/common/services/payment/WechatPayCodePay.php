<?php
/**
 * Created by PhpStorm.
 * 
 * 
 *
 * Date: 2022/6/28
 * Time: 13:53
 */

namespace app\common\services\payment;


use app\common\exceptions\AppException;
use app\common\facades\EasyWeChat;
use app\common\helpers\Client;
use app\common\helpers\Url;
use app\common\services\Pay;
use app\common\services\PayFactory;
use app\common\services\WechatPay;
use app\frontend\modules\order\services\OrderPaySuccessService;

class WechatPayCodePay extends Pay
{

    /**
     * 创建支付对象
     */
    public function getEasyWeChatApp($pay, $notify_url)
    {
        $options = [
            'app_id'             => $pay['weixin_appid'],
            'secret'             => $pay['weixin_secret'],
            'mch_id'             => $pay['weixin_mchid'],
            'key'                => $pay['weixin_apisecret'],
            'cert_path'          => $pay['weixin_cert'],
            'key_path'           => $pay['weixin_key'],
            'notify_url'         => $notify_url
        ];
        $app = EasyWeChat::payment($options);

        return $app;
    }


    public function doPay($data)
    {


        $pay = \Setting::get('shop.pay');


        if (empty($pay['weixin_mchid']) || empty($pay['weixin_apisecret'])
            || empty($pay['weixin_appid']) || empty($pay['weixin_secret'])) {

            throw new AppException('没有设定支付参数');
        }


        $data['auth_code'] = request()->auth_code;

        if (empty($data['auth_code'])) {
            throw new AppException('无法获取用户条码或者二维码信息');
        }



        $op = '微信付款码支付-订单号：' . $data['order_no'];
        $pay_order_model = $this->log($data['extra']['type'], '微信付款码支付', $data['amount'], $op, $data['order_no'], Pay::ORDER_STATUS_NON, \YunShop::app()->getMemberId());



        $this->setParameter('auth_code', $data['auth_code']); //设备读取用户微信中的条码或者二维码信息
        $this->setParameter('sign_type', 'MD5');
        $this->setParameter('trade_type',  'MICROPAY'); //微信付款码支付的交易类型为MICROPAY
        $this->setParameter('nonce_str', str_random(16));
        $this->setParameter('body', mb_substr($data['subject'], 0, 120));
        $this->setParameter('attach', \YunShop::app()->uniacid);
        $this->setParameter('out_trade_no',  $data['order_no']);
        $this->setParameter('total_fee',  $data['amount'] * 100);  // 单位：分
        $this->setParameter('spbill_create_ip',  self::getClientIP());


        //请求数据日志
        self::payRequestDataLog($data['order_no'], $pay_order_model->type,
            $pay_order_model->third_type, $this->getAllParameters());



        $notify_url = Url::shopSchemeUrl('payment/wechat/notifyUrl.php');
        $payment     = $this->getEasyWeChatApp($pay, $notify_url);



        $result = $payment->order->microPay($this->getAllParameters());

        \Log::debug('--微信付款码支付--',$result);

        if(!empty($result) && ("SUCCESS" == $result['return_code']) && ("USERPAYING" == $result['err_code'])) {
            $max_time = time() + 30;
            $is_success = false;
            $return_msg = '';

            while (time() < $max_time && !$is_success){
                sleep(3);
                $check_result = $payment->order->queryByOutTradeNumber($this->getParameter('out_trade_no'));
                if(in_array($check_result['trade_state'],['REFUND','CLOSED','REVOKED','PAYERROR'])){
                    $msg = '支付失败';
                    if ($result['return_code']) $msg .= ",code:{$result['return_code']}";
                    if ($result['return_msg']) $msg .= ",msg:{$result['return_msg']}";
                    throw new AppException($msg);
                }
                if ($check_result['return_code'] == 'SUCCESS' && $check_result['result_code'] == 'SUCCESS'
                    && $check_result['trade_state'] == 'SUCCESS' && $check_result['trade_type'] == 'MICROPAY'){
                    $result = $check_result;
                    $is_success = true;
                }else{
                    $return_msg = $check_result['$return_msg'] ? : '';
                }
            }
            if (!$is_success){
                throw new AppException($return_msg ? : '支付失败');
            }
        }

        $this->payResponseDataLog($result['out_trade_no'], '微信付款码支付', json_encode($result));

        if ($result['return_code'] == 'SUCCESS' && $result['result_code'] == 'SUCCESS' && $result['trade_type'] == 'MICROPAY') {
            $data = [
                'total_fee'    => $result['total_fee'] ,
                'out_trade_no' => $result['out_trade_no'],
                'trade_no'     => $result['transaction_id'],
                'unit'         => 'fen',
                'pay_type'     => '微信支付(付款码)',
                'pay_type_id'     => PayFactory::WECHAT_MICRO_PAY,
            ];
            $res = (new OrderPaySuccessService())->payResutl($data);
            if (!$res['result']){
                throw new AppException($res['msg']);
            }
            return true;
        } else{
            $msg = '支付失败';
            if ($result['return_code']) $msg .= ",code:{$result['return_code']}";
            if ($result['return_msg']) $msg .= ",msg:{$result['return_msg']}";
            throw new AppException($msg);
        }

    }

    /**
     * @param \app\common\services\订单号 $out_trade_no
     * @param \app\common\services\订单总金额 $totalmoney
     * @param \app\common\services\退款金额 $refundmoney
     * @return array|mixed
     * @throws AppException
     */
    public function doRefund($out_trade_no, $totalmoney, $refundmoney)
    {
        return (new WechatPay)->doRefund($out_trade_no, $totalmoney, $refundmoney);
    }

    public function doWithdraw($member_id, $out_trade_no, $money, $desc, $type)
    {
        // TODO: Implement doWithdraw() method.
    }

    public function buildRequestSign()
    {
        // TODO: Implement buildRequestSign() method.
    }
}