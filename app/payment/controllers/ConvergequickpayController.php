<?php
/**
 * Created by PhpStorm.
 * User: blank
 * Date: 2020/4/8
 * Time: 14:14
 */

namespace app\payment\controllers;

use app\common\models\AccountWechats;
use app\common\models\OrderPay;
use app\common\services\Pay;
use app\common\services\PayFactory;
use app\payment\PaymentController;
use app\backend\modules\refund\services\RefundOperationService;
use app\backend\modules\refund\services\RefundMessageService;
use Yunshop\ConvergePay\common\quick\EncryptUtil;
use Yunshop\ConvergePay\models\QuickPayOrder;
use Yunshop\ConvergePay\models\QuickPayRefundOrder;

class ConvergequickpayController extends PaymentController
{

    protected $parameters;

    public $responseData;

    public function __construct()
    {
        parent::__construct();

        $this->setResponseData();

        $this->head();
    }
    protected function setResponseData()
    {
        $data =  $this->getResponseResult();

        //保存原始的data json字符串
        $this->responseData = $data['data'];

        if (!empty($data['data']) &&  is_string($data['data'])) {
            $data['data'] = json_decode($data['data'], true);
        }

        $this->parameters = $data;
    }

    protected function getResponseResult()
    {
        $post = file_get_contents('php://input');
        if (empty($post)) {
            $post = $_POST;
        }

        \Log::debug('---汇聚快捷支付回调-----', $post);

        if (!is_array($post)) {
            $post = json_decode($post, true);
        }
        
        return $post;
    }

    protected function head()
    {
        if (empty(\YunShop::app()->uniacid)) {
            $script_info = pathinfo($_SERVER['SCRIPT_NAME']);
            \Log::debug($script_info);
            if (!empty($script_info)) {
                switch ($script_info['filename']) {
                    case 'payNotify':
                        \YunShop::app()->uniacid = $this->getDataParameter('callback_param');
                        break;
                    case 'refundNotify':
                        $i = explode('NO',$this->getDataParameter('refund_order_no'));
                        \YunShop::app()->uniacid = $i[0];
                        break;
                    default:
                        break;
                }
            }
            \Setting::$uniqueAccountId = \YunShop::app()->uniacid;
            AccountWechats::setConfig(AccountWechats::getAccountByUniacid(\YunShop::app()->uniacid));
        }

        if (empty(\YunShop::app()->uniacid)) {
            \Log::debug('---------汇聚快捷支付回调无法获取公众号------------');
            echo '无法获取公众号'; exit();
        }
    }

    //支付
    public function payNotify()
    {
        $this->log($this->getDataParameter('mch_order_no'), $this->parameters, '汇聚快捷支付--支付');


        if ($this->verifySign() && $this->isSuccess()) {

            $payOrder = QuickPayOrder::uniacid()->where('pay_no',  $this->getDataParameter('mch_order_no'))->first();

            if (!$payOrder) {
                \Log::debug('---------汇聚快捷支付回调--支付记录不存在--'. $this->getDataParameter('mch_order_no'));
                echo '支付记录不存在'; exit();
            }

            $this->updatePayOrder($payOrder);

            if ($this->getDataParameter('order_status') == 'P1000') {
                $data = [
                    'total_fee'    => floatval($this->getDataParameter('order_amount')),
                    'out_trade_no' => $payOrder->mch_order_no,
                    'trade_no'     => $payOrder->pay_no,
                    'unit'         => 'yuan',
                    'pay_type'     => '汇聚快捷支付',
                    'pay_type_id'  => PayFactory::CONVERGE_QUICK_PAY,
                ];
                $this->payResutl($data);
                echo 'success'; exit();
            }
            \Log::debug('<---------汇聚快捷支付回调--交易未成功--->'. $this->getDataParameter('mch_order_no'), $this->parameters);

            if ($this->getDataParameter('order_status') == 'P3000') {
                echo 'fail';exit();
            }

            echo 'success'; exit();
        }

        \Log::debug('---------汇聚快捷支付回调-支付失败---'. $this->getDataParameter('mch_order_no'), $this->parameters);
        echo 'fail';exit();
    }

    public function updatePayOrder($payOrder)
    {
        $data['order_status'] = $this->getDataParameter('order_status');
        $data['jp_order_no'] = $this->getDataParameter('jp_order_no');

        if ($this->getDataParameter('pay_success_time')) {
            $data['pay_at'] = strtotime($this->getDataParameter('pay_success_time'));
        }

        if ($this->getDataParameter('bank_trx_no')) {
            $data['bank_trx_no'] = $this->getDataParameter('bank_trx_no');
        }

        if ($this->getDataParameter('errCode')) {
            $data['err_code'] = $this->getDataParameter('errCode');
        }

        if ($this->getDataParameter('errMsg')) {
            $data['err_msg'] = $this->getDataParameter('errMsg');
        }

        $payOrder->fill($data);

        $payOrder->save();

    }

    //退款
    public function refundNotify()
    {
        $this->log($this->getDataParameter('refund_order_no'), $this->parameters, '汇聚快捷支付-退款');


        if ($this->verifySign() && $this->isSuccess()) {

            $refundOrder = QuickPayRefundOrder::uniacid()->where('refund_no',  $this->getDataParameter('refund_order_no'))->first();

            if (!$refundOrder) {
                \Log::debug('---------汇聚快捷支付回调--退款记录不存在--'. $this->getDataParameter('refund_order_no'));
                echo '退款记录不存在'; exit();
            }

            $data['refund_trx_no'] = $this->getDataParameter('refund_trx_no');
            $data['refund_status'] = $this->getDataParameter('refund_status');

            if ($this->getDataParameter('refund_complete_time')) {
                $data['refund_at'] = strtotime($this->getDataParameter('refund_complete_time'));
            }

            if ($this->getDataParameter('errCode')) {
                $data['err_code'] = $this->getDataParameter('errCode');
            }

            if ($this->getDataParameter('errMsg')) {
                $data['err_msg'] = $this->getDataParameter('errMsg');
            }

            $refundOrder->fill($data);

            $refundOrder->save();

            if ($this->getDataParameter('refund_status') == '100') {
                $orderPay = OrderPay::where('pay_sn', $refundOrder->mch_order_no)->first();
                $refundApply = \app\common\models\refund\RefundApply::whereIn('order_id', $orderPay->order_ids)->get();
                if (!$refundApply->isEmpty()) {
                    foreach ($refundApply as $refund) {
                        if (bccomp($refund->price, $this->getDataParameter('refund_amount'), 2) == 0) {
                            //退款状态设为完成
                            RefundOperationService::refundComplete(['id' => $refund->id]);
                            break;
                        }
                    }
                }
            }


            echo 'success'; exit();
        }

        \Log::debug('---------汇聚快捷支付回调--退款失败--'. $this->getDataParameter('refund_order_no'), $this->parameters);
        echo 'fail';exit();
    }

    //签名验证
    public function verifySign()
    {
        $set = \Setting::get('plugin.convergePay_set');

        $bool = EncryptUtil::verify($this->toQueryString($this->parameters), $this->getParameter('sign'), $set['quick_pay']['platform_public_key']);

        if (!$bool) {
            \Log::debug('<-------汇聚快捷支付回调签名验证失败--------------->');
        }

        return $bool;
    }

    /**
     * 判断本次请求是否成功
     * @return bool
     */
    public function isSuccess()
    {
        return "JS000000" === $this->getParameter('biz_code');
    }

    /**
     * 获取参数值
     * @param string $key
     * @return string
     */
    public function getParameter($key)
    {
        return array_get($this->parameters, $key, '');
    }

    public function getDataParameter($key)
    {
        return array_get($this->parameters['data'], $key, '');
    }

    public function getResponseData()
    {
        return $this->responseData?$this->responseData:'';
    }

    /**
     * 将参数转换成k=v拼接的形式
     */
    public function toQueryString($parameter)
    {

        //按key的字典序升序排序，并保留key值
        ksort($parameter);

        $strQuery="";
        foreach ($parameter as $k=>$v){

            //不参与签名、验签
            if($k == "sign" || $k == "sec_key") {
                continue;
            }

            if($v === null) {$v = '';}

//            if (is_array($v)) {
//                $v = json_encode($v, JSON_UNESCAPED_UNICODE);
//            }
            //获取原始的data json字符串
            if ($k == 'data') {
                $v = $this->getResponseData();
            }

            $strQuery .= strlen($strQuery) == 0 ? "" : "&";
            $strQuery.=$k."=".$v;
        }

        return $strQuery;
    }

    /**
     * 支付日志
     * @param $out_trade_no
     * @param $data
     * @param string $msg
     */
    public function log($out_trade_no, $data, $msg = '汇聚快捷支付')
    {
        //访问记录
        Pay::payAccessLog();
        //保存响应数据
        Pay::payResponseDataLog($out_trade_no, $msg, json_encode($data));
    }
}