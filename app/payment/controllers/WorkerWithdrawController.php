<?php
/**
 * Created by PhpStorm.
 *
 * 
 *
 * Date: 2021/5/26
 * Time: 17:13
 */

namespace app\payment\controllers;

use app\common\events\withdraw\WithdrawSuccessEvent;
use app\common\exceptions\ShopException;
use app\common\models\Withdraw;
use app\payment\PaymentController;
use app\common\services\Pay;
use Yunshop\Love\Common\Models\LoveWithdrawRecords;
use Yunshop\WorkerWithdraw\services\RequestService;
use Exception;
use Yunshop\WorkerWithdraw\services\WorkWithdrawService;

class WorkerWithdrawController extends PaymentController
{
    protected $parameters;
    protected $withdraw;

    public function __construct()
    {
        parent::__construct();
        date_default_timezone_set('PRC');
        $this->setParameter();
        //访问记录
        Pay::payAccessLog();
        //保存响应数据
        Pay::payResponseDataLog('', '好灵工提现回调', json_encode($this->parameters));

        if (!app('plugins')->isEnabled('worker-withdraw')) {
            \Log::debug('好灵工提现回调通知:插件未开启', $this->parameters);
            exit('success');
        }
    }

    /**
     * @return ApiService
     */
    private function api()
    {
        return ApiService::current($this->parameters['appkey']);
    }

    private function setParameter()
    {
        $this->parameters = request()->input();
    }

    /**
     * @param $trade_number
     */
    private function setWithdraw($trade_number)
    {
        $this->withdraw = HighLightWithdrawModel::where('order_sn', $trade_number)->first();
        if (!$this->withdraw) {
            \Log::debug('高灯提现结算单回调通知：提现订单信息未找到', $trade_number);
            exit('success');
        }
        \Setting::$uniqueAccountId = \YunShop::app()->uniacid = $this->withdraw->uniacid;
    }

    /**
     * @return void
     * 好灵工打款结果异步回调
     */
    public function notifyUrl()
    {
        $callback_data = request()->all();
        \Log::debug('好灵工打款回调', $callback_data);
//        $data = '{"biz_content":"{\"recvType\":\"ALIPAY\",\"custBatchNo\":\"WS20220510115332681420\",\"totalDeduction\":0.99,\"batchTotalAgentFeeAmt\":0.00,\"batchServFeeAmt\":0.09,\"batchAmt\":0.90,\"batchStatus\":1,\"platBatchNo\":\"110202205103900126721085480\"}","method":"callBack","sign":"V965hv59aCKzKdHlR4j6sF611zuxSEelrV8RKjLndTLdL94K6rU6BlQ6TsyC6DqvKxWvIusMxFU44MNXZWSWDJgjCrAPx8Mgz4CFraoUx/8RVgn1CAt6Gnsg/wxdToWnzO3WSvchZNyDcsWvuWjrfI5FLVKGTm13lPXQaOEfHQZqVZ46rh//4gpVphJHgPl0ktfwnHfWQDVzA84VApDtODCcQ4fHgytSfD9Ri/tF0rB9lJ6uRsIeAvBE0lI7y0lIBxVbOYSJ4xilMFakk9Ton9UhIp9TFaF6oFHhxn9Bn2NiNHbULR33exqh8K23hIczo9qNJ0t9QBSxR+ByIj8tiw==","merchant_request_no":"0ef47d209f1d4bb1867008821d3721d8","sign_type":"RSA2","version":"1.0","timestamp":"2022-05-10"}';
//        $callback_data = json_decode($data,true);
        $callback = json_decode($callback_data['biz_content'], true);

        $tag = substr($callback['custBatchNo'], 0, 2);
        if ($tag == 'WS') {
            if (!$withdraw_log = Withdraw::where('withdraw_sn', $callback['custBatchNo'])->first()) {
                \Log::debug('好灵工打款回调异常:不存在的打款单号');
            }
        } elseif ($tag == 'LW') {
            if (app('plugins')->isEnabled('love')) {
                if (!$withdraw_log = LoveWithdrawRecords::where('order_sn', $callback['custBatchNo'])->first()) {
                    \Log::debug('好灵工打款回调异常:不存在的打款单号');
                }
            } else {
                \Log::debug('好灵工打款回调异常:爱心值插件关闭');
                return;
            }

        } else {
            \Log::debug('好灵工打款回调异常:不存在提现方式');
            return;
        }

        \YunShop::app()->uniacid = $withdraw_log->uniacid;
        \app\common\facades\Setting::$uniqueAccountId = $withdraw_log->uniacid;
        $res = (new WorkWithdrawService())->refreshWithdraw($callback['custBatchNo']);
        if (!$res['result']) {
            \Log::debug('好灵工打款回调异常', $res['msg']);
            return;
        }
        \Log::debug('好灵工打款回调结果', $res['data']['status']);
        if ($res['result'] && $res['data']['status'] === 1) {
            exit('success');
        }
    }


    public function refundNotifyUrl()
    {

    }
}