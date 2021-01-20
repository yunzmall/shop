<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/10/27
 * Time: 15:21
 */

namespace app\payment\controllers;

use app\common\helpers\Url;
use app\common\models\AccountWechats;
use app\common\models\OrderPay;
use app\common\services\Pay;
use app\common\services\PayFactory;
use app\payment\PaymentController;
use Illuminate\Support\Facades\DB;
use Yunshop\StoreAggregatePay\models\AggregatePayAccount;
use Yunshop\StoreAggregatePay\models\StoreAggregatePayLog;
use Yunshop\StoreAggregatePay\services\Client;

class StoreaggregateController extends PaymentController
{
    protected $responseParameters;


    protected $payLog;


    protected $account;

    public function __construct()
    {
        parent::__construct();

        $this->head();
    }

    protected function head()
    {
        $result = $this->setParameters();

        $this->payLog = DB::table('yz_store_aggregate_pay_log')->where('pay_sn', $result['out_no'])->first();

        if (is_null($this->payLog)) {
            \Log::debug('---门店聚合支付记录不存在---', $result);
            echo 'not pay log'; exit();
        }


        if (empty(\YunShop::app()->uniacid)) {

            \YunShop::app()->uniacid = $this->payLog['uniacid'];
            \Setting::$uniqueAccountId = \YunShop::app()->uniacid;
            AccountWechats::setConfig(AccountWechats::getAccountByUniacid(\YunShop::app()->uniacid));
        }

        if (empty(\YunShop::app()->uniacid)) {
            \Log::debug('---------聚合支付回调无法获取公众号------------');
            echo '无法获取公众号'; exit();
        }
    }

    protected function setParameters()
    {
        \Log::debug('-------聚合支付------', $_GET);
        //获取返回参数
       return  $this->responseParameters = $_GET;
    }

    //微信支付同步通知，支付宝服务窗支付也可使用
    public function jumpUrl()
    {

        $trade = \Setting::get('shop.trade');

        if ($this->getParameter('status') != 1) {
            redirect(Url::absoluteApp('member/payErr', ['i' => \YunShop::app()->uniacid]))->send();
            exit();
        }

        if (!is_null($trade) && isset($trade['redirect_url']) && !empty($trade['redirect_url'])) {
            redirect($trade['redirect_url'].'&outtradeno='. $this->getParameter('out_no'))->send();
            exit();
        }

        redirect(Url::absoluteApp('member/payYes'))->send();
    }

    //支付
    public function notifyUrl()
    {
        $this->log($this->getParameter('out_no'), $this->getAllParameter(), '微信支付--聚合支付');


        if ($this->checkSign($this->getAllParameter())) {

            $result = $this->thirdPartyOrder();

            if ($result['status'] !== 200) {
                \Log::debug('<----------微信支付--聚合支付回调查询订单状态失败--->', $result);
                echo '查询第三方订单失败:'.$result['msg']; exit();
            }

            $selectData = $result['data']['data'];

            if ($selectData['status'] == '1') {

                $data = [
                    'total_fee'    =>$selectData['trade_amount'],
                    'out_trade_no' => $this->getParameter('out_no'),
                    'trade_no'     => $this->getParameter('ord_no'),
                    'unit'         => 'fen',
                    'pay_type'     => '微信支付--聚合支付',
                    'pay_type_id'  => PayFactory::STORE_AGGREGATE_WECHAT,
                ];
                $this->payResutl($data);
                echo 'notify_success'; exit();
            }
            \Log::debug('<----------微信支付--聚合支付回调--交易未成功--->'. $this->getParameter('out_no'), $this->getAllParameter());

            echo 'fail';exit();
        }

        \Log::debug('---------微信支付--聚合支付回调--支付失败---'. $this->getParameter('out_no'), $this->getAllParameter());
        echo 'fail';exit();
    }

    //支付宝
    public function alipayUrl()
    {
        $this->log($this->getParameter('out_no'), $this->getAllParameter(), '支付宝支付--聚合支付');


        if ($this->checkSign($this->getAllParameter())) {

            $result = $this->thirdPartyOrder();

            if ($result['status'] !== 200) {
                \Log::debug('<----------支付宝支付--聚合支付回调查询订单状态失败--->', $result);
                echo '查询第三方订单失败:'.$result['msg']; exit();
            }

            $selectData = $result['data']['data'];

            if ($selectData['status'] == '1') {

                $data = [
                    'total_fee'    =>$selectData['trade_amount'],
                    'out_trade_no' => $this->getParameter('out_no'),
                    'trade_no'     => $this->getParameter('ord_no'),
                    'unit'         => 'fen',
                    'pay_type'     => '支付宝支付--聚合支付',
                    'pay_type_id'  => PayFactory::STORE_AGGREGATE_ALIPAY,
                ];
                $this->payResutl($data);
                echo 'notify_success'; exit();
            }
            \Log::debug('<----------支付宝支付--聚合支付回调--交易未成功--->'. $this->getParameter('out_no'), $this->getAllParameter());

            echo 'fail';exit();
        }

        \Log::debug('---------支付宝支付--聚合支付回调--支付失败---'. $this->getParameter('out_no'), $this->getAllParameter());
        echo 'fail';exit();
    }

    protected function thirdPartyOrder()
    {
        $account = $this->getStoreAccount();

        $requestData = [
            'open_id' => $account['open_id'],
            'timestamp' => time(),
            'data' => ['out_no' => $this->getParameter('out_no'),]
        ];

        return (new Client($account))->post('paystatus', $requestData);
    }

    /**
     * 获取参数值
     * @param string $key
     * @return string
     */
    public function getParameter($key)
    {
        return array_get($this->responseParameters, $key, '');
    }

    public function getAllParameter()
    {
        return $this->responseParameters;
    }

    protected function getStoreAccount()
    {
        if (isset($this->account)) {
            return $this->account;
        }
        $this->account =  AggregatePayAccount::select('store_id', 'open_id', 'open_key')->where('store_id', $this->payLog['store_id'])->first();

        return $this->account;
    }


    /**
     * @param $array
     * @return bool
     */
    public function checkSign($array)
    {

        $sign = $array['sign'];#得到返回签名字符串
        unset($array['sign']);#去掉sign节点
        $array['open_key'] = $this->getStoreAccount()['open_key'];#加上open_key节点
        ksort($array);#排序

        $arr_temp = array();
        foreach ($array as $key => $val) {
            $arr_temp[] = $key . '=' . $val;
        }

        $sign_str = implode('&', $arr_temp);
        $sign_str = md5(sha1($sign_str));

        if ($sign != $sign_str) {
            return false;
        } else {
            return true;
        }

    }

    /**
     * 支付日志
     * @param $out_trade_no
     * @param $data
     * @param string $msg
     */
    public function log($out_trade_no, $data, $msg = '聚合支付')
    {
        //访问记录
        Pay::payAccessLog();
        //保存响应数据
        Pay::payResponseDataLog($out_trade_no, $msg, json_encode($data));
    }
}