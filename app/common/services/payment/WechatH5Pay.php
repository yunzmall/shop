<?php
/**
 * Created by PhpStorm.
 * User: blank
 * Date: 2020/4/16
 * Time: 13:55
 */

namespace app\common\services\payment;

use app\common\exceptions\AppException;
use app\common\facades\EasyWeChat;
use app\common\helpers\Url;
use app\common\services\Pay;

class WechatH5Pay extends Pay
{
    protected $notify_url;


    protected $paySet;

    /**
     * WechatH5Pay constructor.
     * @throws AppException
     */
    public function __construct()
    {
        $this->notify_url = Url::shopSchemeUrl('payment/wechat/notifyH5.php');

        $this->paySet = \Setting::get('shop.pay');

        if (empty($this->paySet['weixin_mchid']) || empty($this->paySet['weixin_apisecret']) || empty($this->paySet['weixin_appid']) || empty($this->paySet['weixin_secret'])) {

            throw new AppException('没有设定支付参数');
        }
    }


    /**
     * 创建支付对象
     *
     * @param $pay
     * @return \EasyWeChat\Payment\Payment
     */
    public function getEasyWeChatApp($pay, $notify_url)
    {
        $options = [
            'app_id'  => $pay['weixin_appid'],
            'secret'  => $pay['weixin_secret'],
            // payment
            'payment' => [
                'merchant_id'        => $pay['weixin_mchid'],
                'key'                => $pay['weixin_apisecret'],
                'cert_path'          => $pay['weixin_cert'],
                'key_path'           => $pay['weixin_key'],
                'notify_url'         => $notify_url
            ]
        ];
        $app = EasyWeChat::payment($options);
        return $app;
    }

    /**
     * @param $data
     * @return mixed|void
     */
    public function doPay($data)
    {
        $op = '微信h5支付-订单号：' . $data['order_no'];
        $pay_order_model = $this->log($data['extra']['type'], '微信h5支付', $data['amount'], $op, $data['order_no'], Pay::ORDER_STATUS_NON, \YunShop::app()->getMemberId());

        if (empty(\YunShop::app()->getMemberId())) {
            throw new AppException('无法获取用户ID');
        }


        //$this->setParameter('appid', $this->paySet['weixin_appid']);
        //$this->setParameter('mch_id', $this->paySet['weixin_mchid']);
        $this->setParameter('sign_type', 'MD5');
        $this->setParameter('trade_type',  'MWEB'); //H5支付的交易类型为MWEB
        $this->setParameter('device_info', 'WEB');
        $this->setParameter('nonce_str', str_random(16));
        $this->setParameter('body', mb_substr($data['subject'], 0, 120));
        $this->setParameter('attach', \YunShop::app()->uniacid);
        $this->setParameter('out_trade_no',  $data['order_no']);
        $this->setParameter('total_fee',  $data['amount'] * 100);  // 单位：分
        $this->setParameter('spbill_create_ip',  self::getClientIP());
        $this->setParameter('notify_url',  $this->notify_url);

        $this->setParameter('scene_info',  json_encode($this->getSceneInfo(), JSON_UNESCAPED_UNICODE));



        //请求数据日志
        self::payRequestDataLog($data['order_no'], $pay_order_model->type,
            $pay_order_model->third_type, $this->getAllParameters());



        /**
         * @var $app Application
         */
        $payment = $this->getEasyWeChatApp($this->paySet, $this->notify_url);


        $result = $payment->order->unify($this->getAllParameters());
        \Log::debug('预下单', $result);

        if ($result['return_code'] == 'SUCCESS' && $result['result_code'] == 'SUCCESS'){

            $mweb_url = $result['mweb_url'];

            if ($mweb_url) {
                $trade = \Setting::get('shop.trade');
                if (!is_null($trade) && isset($trade['redirect_url']) && !empty($trade['redirect_url'])) {
                    $mweb_url .= '&redirect_url='.urlencode($trade['redirect_url']);
                } else {
                    // 实名认证支付
                    if (substr($data['order_no'], 0, 2) == 'RI') {
                        $url = Url::absoluteApp('personBank',['from' => 'bindRealName']);
                        $mweb_url .= '&redirect_url='.urlencode($url);
                    } else {
                        $url = Url::absoluteApp('member');
                        $mweb_url .= '&redirect_url='.urlencode($url);
                    }
                }
            }

            //mweb_url为拉起微信支付收银台的中间页面，可通过访问该url来拉起微信客户端，完成支付,mweb_url的有效期为5分钟。
            return ['mweb_url'=>$mweb_url];

        } elseif ($result['return_code'] == 'SUCCESS') {
            throw new AppException($result['err_code_des']);
        } else {
            throw new AppException($result['return_msg']);
        }

        return false;
    }

    public function getSceneInfo()
    {
        $a = [
            'h5_info' => [
                'type' => 'Wap',//场景类型
                'wap_url' => request()->getSchemeAndHttpHost(), //WAP网站URL地
                'wap_name' => '商城', //WAP网站名
            ],
        ];

        return $a;
    }

    /**
     * @param 订单号 $out_trade_no
     * @param 订单总金额 $totalmoney
     * @param 退款金额 $refundmoney
     * @return mixed|void
     */
    public function doRefund($out_trade_no, $totalmoney, $refundmoney)
    {

        $out_refund_no = $this->setUniacidNo(\YunShop::app()->uniacid);
        $op = '微信退款 订单号：' . $out_trade_no . '退款单号：' . $out_refund_no . '退款金额：' . $refundmoney;
        if (empty($out_trade_no)) {
            throw new AppException('参数错误');
        }

        $pay_order_model = $this->refundlog(Pay::PAY_TYPE_REFUND, '微信h5退款', $refundmoney, $op, $out_trade_no, Pay::ORDER_STATUS_NON, 0);

        /**
         * @var $app Application
         * @var $payment \EasyWeChat\Payment\Payment
         */
        $payment = $this->getEasyWeChatApp($this->paySet, '');

        try {

            $totalmoney = bcmul($totalmoney, 100,0);
            $refundmoney = bcmul($refundmoney, 100,0);

            $result = $payment->refund->byOutTradeNumber($out_trade_no, $out_refund_no, $totalmoney, $refundmoney);
        } catch (\Exception $e) {
            throw new AppException('微信接口错误:' . $e->getMessage());
        }

        //微信申请退款失败
        if (isset($result['result_code']) && strtoupper($result['result_code']) == 'FAIL') {
            \Log::debug('---微信退款申请错误---', $result);
            throw new AppException('微信退款申请错误:'.$result['err_code'] . '-' . $result['err_code_des']);
        }


        $this->payResponseDataLog($out_trade_no, '微信h5退款', json_encode($result));

        $status = $this->queryRefund($payment, $out_trade_no);
        \Log::debug('---微信h5退款状态---'.$status, $result);

        if ($status == 'PROCESSING' || $status == 'SUCCESS') {
            $this->changeOrderStatus($pay_order_model, Pay::ORDER_STATUS_COMPLETE, $result['transaction_id']);
            return true;
        } else {
            throw new AppException('微信接口错误:'.$result['return_msg'] . '-' . $result['err_code_des'] . '/' . $status);
        }
    }

    private function changeOrderStatus($model, $status, $trade_no)
    {
        $model->status = $status;
        $model->trade_no = $trade_no;
        $model->save();
    }

    /**
     * 订单退款查询
     * @param $payment
     * @param $out_trade_no
     * @return mixed
     */
    public function queryRefund($payment, $out_trade_no)
    {
        $result = $payment->refund->queryByOutTradeNumber($out_trade_no);

        foreach ($result as $key => $value) {
            if (preg_match('/refund_status_\d+/', $key)) {
                return $value;
            }
        }

        return 'fail';
    }

    /**
     * @param 提现者用户ID $member_id
     * @param 提现批次单号 $out_trade_no
     * @param 提现金额 $money
     * @param 提现说明 $desc
     * @param 只针对微信 $type
     * @return mixed|void
     */
    public function doWithdraw($member_id, $out_trade_no, $money, $desc, $type)
    {

    }

    public function buildRequestSign()
    {

    }
}