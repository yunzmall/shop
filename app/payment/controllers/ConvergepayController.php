<?php
/**
 * Author:
 * Date: 2019/4/24
 * Time: 下午3:10
 */

namespace app\payment\controllers;

use app\common\helpers\Url;
use app\common\models\AccountWechats;
use app\common\services\finance\Withdraw;
use app\common\services\Pay;
use app\common\services\PayFactory;
use app\payment\PaymentController;
use Yunshop\ConvergePay\models\WithdrawLog;
use Yunshop\ConvergePay\services\NotifyService;
use app\common\events\withdraw\WithdrawSuccessEvent;

class ConvergepayController extends PaymentController
{
    private $parameter = [];


    public function __construct()
    {
        parent::__construct();

        $this->parameter = $_REQUEST;
    }

    public function notifyUrl()
    {
        if (empty(\YunShop::app()->uniacid)) {

            if (!$this->getResponse('r5_Mp')) {
                \Log::debug('汇聚支付回调公众号为空--->', $this->parameter);
                echo 'No official account exists.';exit();
            }

            \Setting::$uniqueAccountId = \YunShop::app()->uniacid =$this->getResponse('r5_Mp');

            AccountWechats::setConfig(AccountWechats::getAccountByUniacid(\YunShop::app()->uniacid));
        }


        $payType = $this->getConvergePayType()[$this->getResponse('rc_BankCode')];

        if (!$payType) {
            \Log::debug('未知汇聚支付类型---'.$this->getResponse('rc_BankCode'));
            echo 'Unknown payment type';exit();
        }

        $this->log($this->parameter, $payType['name']);


        if ($this->getSignResult()) {
            if ($this->getResponse('r6_Status') == '100') {
                \Log::debug("<------{$payType['name']} 业务处理----");

                $data = $this->data($payType['name'], $payType['pay_type_id']);
                $this->payResutl($data);

                \Log::debug("----{$payType['name']} 处理结束---->");
                echo 'success';
            } else {
                //其他错误
                \Log::debug("------{$payType['name']} 支付失败-----");
                echo 'fail';
            }
        } else {
            //签名验证失败
            \Log::debug("------{$payType['name']} 签名验证失败-----");
            echo 'fail1';
        }

    }

    public function getConvergePayType()
    {
        return [
            'ALIPAY_H5' => [
                'name' => '汇聚-支付宝H5',
                'pay_type_id' => PayFactory::CONVERGE_ALIPAY_H5_PAY,
            ],
            'UNIONPAY_H5' => [
                'name' => '汇聚-云闪付',
                'pay_type_id' => PayFactory::CONVERGE_UNION_PAY,
            ],
            'WEIXIN_CARD' => [
                'name' => '汇聚-微信付款码',
                'pay_type_id' => PayFactory::CONVERGE_WECHAT_CARD_PAY,
            ],
            'ALIPAY_CARD' => [
                'name' => '汇聚-支付宝付款码',
                'pay_type_id' => PayFactory::CONVERGE_ALIPAY_CARD_PAY,
            ],

        ];
    }


    public function notifyUrlWechat()
    {
        if (empty(\YunShop::app()->uniacid)) {

            if (!$this->getResponse('r5_Mp')) {
                \Log::debug('汇聚支付回调公众号为空--->', $this->parameter);
                echo 'No official account exists.';exit();
            }

            \Setting::$uniqueAccountId = \YunShop::app()->uniacid =$this->getResponse('r5_Mp');

            AccountWechats::setConfig(AccountWechats::getAccountByUniacid(\YunShop::app()->uniacid));
        }

        $this->log($this->parameter, '微信支付-HJ');

        if ($this->getSignResult()) {
            if ($_GET['r6_Status'] == '100') {
                \Log::debug('------微信支付-HJ 验证成功-----');

                if ($_GET['rc_BankCode'] == 'WEIXIN_CARD'){
                    \Log::debug('------汇聚支付-微信付款码支付');
                    $data = $this->data('汇聚支付-微信付款码', PayFactory::CONVERGE_WECHAT_CARD_PAY);
                }else{
                    $data = $this->data('微信支付-HJ', '28');
                }

                $this->payResutl($data);
                \Log::debug('----微信支付-HJ 结束----');

                echo 'success';
            } else {
                //其他错误
                \Log::debug('------微信支付-HJ 其他错误-----');
                echo 'fail';
            }
        } else {
            //签名验证失败
            \Log::debug('------微信支付-HJ 签名验证失败-----');
            echo 'fail1';
        }
    }

    public function returnUrlWechat()
    {
        $trade = \Setting::get('shop.trade');

        if (!is_null($trade) && isset($trade['redirect_url']) && !empty($trade['redirect_url'])) {
            return redirect($trade['redirect_url'])->send();
        }

        if (0 == $_GET['state'] && $_GET['errorDetail'] == '成功') {
            return redirect(Url::absoluteApp('member/payYes', ['i' => $this->getResponse('r5_Mp')]))->send();
        } else {
            return redirect(Url::absoluteApp('member/payErr', ['i' => $this->getResponse('r5_Mp')]))->send();
        }
    }

    public function notifyUrlAlipay()
    {
        if (empty(\YunShop::app()->uniacid)) {
            if (!$this->getResponse('r5_Mp')) {
                \Log::debug('汇聚支付回调公众号为空--->', $this->parameter);
                echo 'No official account exists.';exit();
            }

            \Setting::$uniqueAccountId = \YunShop::app()->uniacid = $this->getResponse('r5_Mp');

            AccountWechats::setConfig(AccountWechats::getAccountByUniacid(\YunShop::app()->uniacid));
        }

        $this->log($this->parameter, '支付宝支付-HJ');

        if ($this->getSignResult()) {
            if ($_GET['r6_Status'] == '100') {
                \Log::debug('------支付宝支付-HJ 验证成功-----');

                if ($_GET['rc_BankCode'] == 'ALIPAY_CARD'){
                    \Log::debug('------汇聚支付-支付宝付款码支付');
                    $data = $this->data('汇聚支付-支付宝付款码', PayFactory::CONVERGE_ALIPAY_CARD_PAY);
                }else{
                    $data = $this->data('支付宝-HJ支付', '29');
                }
                $this->payResutl($data);

                \Log::debug('----支付宝支付-HJ 结束----');
                echo 'success';
            } else {
                //其他错误
                \Log::debug('------支付宝支付-HJ 其他错误-----');
                echo 'fail';
            }
        } else {
            //签名验证失败
            \Log::debug('------支付宝支付-HJ 签名验证失败-----');
            echo 'fail1';
        }
    }

    public function returnUrlAlipay()
    {
        $trade = \Setting::get('shop.trade');

        if (!is_null($trade) && isset($trade['redirect_url']) && !empty($trade['redirect_url'])) {
            return redirect($trade['redirect_url'])->send();
        }

        if (0 == $_GET['state'] && $_GET['errorDetail'] == '成功') {
            return redirect(Url::absoluteApp('member/payYes', ['i' => $this->getResponse('r5_Mp')]))->send();
        } else {
            return redirect(Url::absoluteApp('member/payErr', ['i' => $this->getResponse('r5_Mp')]))->send();
        }
    }

    //银联支付回调
    public function notifyUrlUnionPay()
    {
        if (empty(\YunShop::app()->uniacid)) {
            if (!$this->getResponse('r5_Mp')) {
                \Log::debug('汇聚支付回调公众号为空--->', $this->parameter);
                echo 'No official account exists.';exit();
            }

            \Setting::$uniqueAccountId = \YunShop::app()->uniacid = $this->getResponse('r5_Mp');

            AccountWechats::setConfig(AccountWechats::getAccountByUniacid(\YunShop::app()->uniacid));
        }

        $this->log($this->parameter, '汇聚云闪付支付');

        if ($this->getSignResult()) {
            if ($_GET['r6_Status'] == '100') {
                \Log::debug('<------汇聚云闪付支付 业务处理----');

                $data = $this->data('汇聚云闪付支付', PayFactory::CONVERGE_UNION_PAY);
                $this->payResutl($data);

                \Log::debug('----汇聚云闪付支付 处理结束---->');
                echo 'success';
            } else {
                //其他错误
                \Log::debug('------汇聚云闪付支付 支付失败-----');
                echo 'fail';
            }
        } else {
            //签名验证失败
            \Log::debug('------汇聚云闪付支付 签名验证失败-----');
            echo 'fail1';
        }
    }

    /**
     * 签名验证
     *
     * @return bool
     */
    public function getSignResult()
    {
        $pay = \Setting::get('plugin.convergePay_set');

        $notify = new NotifyService();
        $notify->setKey($pay['hmacVal']);

        return $notify->verifySign();
    }

    /**
     * 支付日志
     *
     * @param $data
     * @param $sign
     */
    public function log($data, $sign)
    {
        //访问记录
        Pay::payAccessLog();
        //保存响应数据
        Pay::payResponseDataLog($this->getResponse('r2_OrderNo'), $sign, json_encode($data));
    }

    /**
     * 支付回调参数
     *
     * @param $pay_type_id
     * @return array
     */
    public function data($pay_type, $pay_type_id)
    {
        $data = [
            'total_fee' => floatval($this->parameter['r3_Amount']),
            'out_trade_no' => $this->getResponse('r2_OrderNo'),
            'trade_no' => $this->parameter['r7_TrxNo'],
            'unit' => 'yuan',
            'pay_type' => $pay_type,
            'pay_type_id' => $pay_type_id
        ];

        return $data;
    }

    /**
     * 提现回调
     *
     */
    public function notifyUrlWithdraw()
    {
        $parameter = request();
        \Log::debug('汇聚提现回调参数--', $parameter->input());


        //查询提现记录
        $withdrawLog = WithdrawLog::where('merchantOrderNo',$parameter->merchantOrderNo)->first();

        if (!$withdrawLog) {
            echo json_encode([
                'statusCode' => 2002,
                'message' => "汇聚代付记录不存在,单号：{$parameter->merchantOrderNo}",
                'errorCode' => '',
                'errorDesc' => ''
            ]);exit();
        }

        //已提现成功的记录无需再处理
        if ($withdrawLog->status == 1) {
            echo json_encode([
                'statusCode' => 2001,
                'message' => "成功"
            ]);exit();
        }

        //设置公众号i
        if (empty(\YunShop::app()->uniacid)) {

            \Setting::$uniqueAccountId = \YunShop::app()->uniacid = $withdrawLog->uniacid;

            AccountWechats::setConfig(AccountWechats::getAccountByUniacid(\YunShop::app()->uniacid));
        }

        //访问记录
        Pay::payAccessLog();
        //保存响应数据
        Pay::payResponseDataLog($withdrawLog->withdraw_sn, '汇聚提现回调', $parameter->input());

        if ($this->checkWithdrawHmac($parameter)) {
            if ($parameter->status == '205') {
                \Log::debug('------汇聚打款 开始-----'.$withdrawLog->withdraw_type);

                if ($withdrawLog->withdraw_type == 1) {
                    //兼容供应商提现
                    if ( app('plugins')->isEnabled('supplier')) {
                        $supplierWithdraw = \Yunshop\Supplier\common\models\SupplierWithdraw::where('apply_sn', $withdrawLog->withdraw_sn)->where('status', 4)->first();
                        if ($supplierWithdraw) {
                            $supplierWithdraw->status = 3;
                            $supplierWithdraw->pay_time = time();
                            $supplierWithdraw->save();
                        }
                    }

                } else {
                    event(new WithdrawSuccessEvent($withdrawLog->withdraw_sn));
                }

                \Log::debug('----汇聚打款 结束----');

                $withdrawLog->status = 1;
                $withdrawLog->response_data = $parameter->input();
                $withdrawLog->save();

                echo json_encode([
                    'statusCode' => 2001,
                    'message' => "成功"
                ]);exit();
            }
            \Log::debug('------汇聚打款失败---- ', $parameter->input());
            if ( in_array($parameter->input('status'), ['204', '208','214'])) {
                $withdrawLog->status = -1;
                $withdrawLog->desc = $parameter->input('errorCodeDesc');
                $withdrawLog->response_data = $parameter->input();
                $withdrawLog->save();

                Withdraw::payFail($withdrawLog->withdraw_sn);

                echo json_encode([
                    'statusCode' => 2002,
                    'message' => "受理失败",
                    'errorCode' => $parameter->errorCode,
                    'errorDesc' => $parameter->errorCodeDesc
                ]);exit();
            }


        } else {
            //签名验证失败
            \Log::debug('------汇聚打款 签名验签失败-----');
            echo json_encode([
                'statusCode' => 2002,
                'message' => "签名验签失败",
                'errorCode' => '300002017',
                'errorDesc' => '签名验签失败'
            ]);exit();
        }
    }

    /**
     * 验证提现签名
     *
     * @param $parameter
     * @return bool
     */
    public function checkWithdrawHmac($parameter)
    {
        $setting = \Setting::get('plugin.convergePay_set');

        $verify = $parameter->hmac == md5($parameter->status . $parameter->errorCode . $parameter->errorCodeDesc . $parameter->userNo
                . $parameter->merchantOrderNo . $parameter->platformSerialNo . $parameter->receiverAccountNoEnc
                . $parameter->receiverNameEnc . sprintf("%.2f", $parameter->paidAmount) . sprintf("%.2f", $parameter->fee) . $setting['hmacVal']);

        \Log::debug('---汇聚打款签名验证--->', [$verify]);

        return $verify;
    }

    /**
     * 微信或支付宝退款
     */
    public function refundUrlWechat()
    {
        $this->logRefund($this->parameter, '微信或支付宝退款-HJ');

        if ($this->getSignWechatResult()) {
            if ($this->parameter['ra_Status'] == '100') {
                \Log::debug('------微信或支付宝退款-HJ 验证成功-----');

                \Log::debug('----微信或支付宝退款-HJ 结束----');
            } else {
                //其他错误
                \Log::debug('------微信或支付宝退款-HJ 其他错误-----');
            }
        } else {
            //签名验证失败
            \Log::debug('------微信或支付宝退款-HJ 签名验证失败-----');
        }

        echo 'success';
    }

    /**
     * 汇聚-微信或支付宝退款 签名验证
     *
     * @return bool
     */
    public function getSignWechatResult()
    {
        $pay = \Setting::get('plugin.convergePay_set');

        \Log::debug('--汇聚-微信或支付宝退款签名验证参数--' . $this->parameter['r1_MerchantNo'] . $this->parameter['r2_OrderNo']
            . $this->parameter['r3_RefundOrderNo'] . $this->parameter['r4_RefundAmount_str'] . $this->parameter['r5_RefundTrxNo']
            . $this->parameter['ra_Status'] . $pay['hmacVal']);

        return $this->parameter['hmac'] == md5($this->parameter['r1_MerchantNo'] . $this->parameter['r2_OrderNo']
                . $this->parameter['r3_RefundOrderNo'] . $this->parameter['r4_RefundAmount_str'] . $this->parameter['r5_RefundTrxNo']
                . $this->parameter['ra_Status'] . $pay['hmacVal']);
    }

    /**
     * 支付日志
     *
     * @param $data
     * @param $sign
     */
    public function logRefund($data, $sign)
    {
        //访问记录
        Pay::payAccessLog();
        //保存响应数据
        Pay::payResponseDataLog($data['r2_OrderNo'], $sign, json_encode($data));
    }

    /**
     * 获取参数值
     * @param string $key
     * @return string
     */
    public function getResponse($key)
    {

        //todo 兼容以前判断
        if ($key == 'r2_OrderNo' && strpos($this->parameter['r2_OrderNo'], ':') !== false) {
            $attach = explode(':', $_GET['r2_OrderNo']);
            return $attach[0];
        }

        return array_get($this->parameter, $key, '');
    }
}