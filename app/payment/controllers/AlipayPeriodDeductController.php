<?php
/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 2023/4/6
 * Time: 9:31
 */

namespace app\payment\controllers;

use app\common\facades\Setting;
use app\common\services\Pay;
use app\payment\PaymentController;
use Yansongda\Pay\Exceptions\InvalidSignException;
use Yunshop\AlipayPeriodDeduct\services\AlipayService;

class AlipayPeriodDeductController extends PaymentController
{
    /**
     * @var AlipayService
     */
    private $aliPayService;

    private $params;

    private $pay_type_id;

    public function __construct()
    {
        parent::__construct();
        $this->aliPayService = new AlipayService();
        $this->params = request()->all();
        if (request()->i) {
            \YunShop::app()->uniacid = Setting::$uniqueAccountId = request()->i;
        } else {
            die('fail');
        }
        if (!app('plugins')->isEnabled('alipay-period-deduct')) {
            die('fail');
        }
    }

    /**
     * 响应日志
     *
     * @param $post
     */
    public function log($post, $desc)
    {
        //访问记录
        Pay::payAccessLog();
        //保存响应数据
        Pay::payResponseDataLog($post['out_trade_no'], $desc, json_encode($post));
    }

    public function notifyUrl()
    {
        try {
            $this->verifySign($this->params);
            $this->log($this->params, '支付宝周期扣款支付');
            $this->pay_type_id = 109;
            if ($this->params['trade_status'] == 'TRADE_SUCCESS') {
                if (strpos($this->params['out_trade_no'], '_') !== false) {
                    $out_trade_no = substr($this->params['out_trade_no'], strpos($this->params['out_trade_no'], '_') + 1);
                } else {
                    $out_trade_no = $this->params['out_trade_no'];
                }
                $data = [
                    'total_fee' => $this->params['total_amount'],
                    'out_trade_no' => $out_trade_no,
                    'trade_no' => $this->params['trade_no'],
                    'unit' => 'yuan',
                    'pay_type' => '支付宝APP2.0',
                    'pay_type_id' => $this->pay_type_id
                ];
                $this->payResutl($data);
            }
            die('success');
        } catch (\Exception $e) {
            \Log::debug('---支付宝周期扣款支付回调--'.$e->getMessage(),[$this->params]);
            die('fail');
        }
    }

    public function tradeNotifyUrl()
    {
        try {
            $this->verifySign($this->params);
            $this->log($this->params, '支付宝周期扣款免密支付');
            $this->pay_type_id = 110;
            if ($this->params['trade_status'] == 'TRADE_SUCCESS') {
                if (strpos($this->params['out_trade_no'], '_') !== false) {
                    $out_trade_no = substr($this->params['out_trade_no'], strpos($this->params['out_trade_no'], '_') + 1);
                } else {
                    $out_trade_no = $this->params['out_trade_no'];
                }
                $data = [
                    'total_fee' => $this->params['total_amount'],
                    'out_trade_no' => $out_trade_no,
                    'trade_no' => $this->params['trade_no'],
                    'unit' => 'yuan',
                    'pay_type' => '支付宝统一收单交易(周期免密扣款)',
                    'pay_type_id' => $this->pay_type_id
                ];
                $this->payResutl($data);
            }
            die('success');
        } catch (\Exception $e) {
            \Log::debug('---支付宝周期扣款免密支付回调--'.$e->getMessage(),[$this->params]);
            die('fail');
        }
    }

    public function signNotifyUrl()
    {
        try {
            $this->verifySign($this->params);
            \Log::debug('-------支付宝签约回调进入------');
            \Yunshop\AlipayPeriodDeduct\services\PayLogService::signCallBack($this->params);
            die('success');
        } catch (\Exception $e) {
            \Log::debug('---支付宝签约回调--' . $e->getMessage(), [$this->params]);
            die('fail');
        }
//         catch (InvalidSignException $e) {
////            \Log::debug('-----支付宝周期扣款签约回调---签名验证错误',[$params]);
//        }
    }

    /**
     * @param $params
     * @return void
     * @throws \Yansongda\Pay\Exceptions\InvalidConfigException
     * @throws \Yansongda\Pay\Exceptions\InvalidSignException
     * @throws \app\common\exceptions\AppException
     */
    public function verifySign($params)
    {
        unset($params['i']);
        $this->aliPayService->payService()->verify($params);//失败会抛异常
    }
}