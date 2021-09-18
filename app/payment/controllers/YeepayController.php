<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/22
 * Time: 9:51
 */

namespace app\payment\controllers;

use app\common\events\withdraw\WithdrawSuccessEvent;
use app\payment\PaymentController;
use Yunshop\YeePay\models\YeePayNotifyLogModel;
use Yunshop\YeePay\models\YeePayWithdrawModel;
use Yunshop\YeePay\services\YeePayApiService;
use app\common\services\Pay;

class YeepayController extends PaymentController
{
    protected $parameters;
    /**
     * @var YeePayWithdrawModel
     */
    protected $withdraw;
    /**
     * @var YeePayNotifyLogModel
     */
    protected $notifyLog;

    public function __construct()
    {
        parent::__construct();
        $this->setParameter();
        //访问记录
        Pay::payAccessLog();
        //保存响应数据
        Pay::payResponseDataLog('', '易宝代付提现回调', json_encode($this->parameters));

        if (!app('plugins')->isEnabled('yee-pay')) {
            exit('success');
        }
    }

    /**
     * @return YeePayApiService
     */
    private function api()
    {
        return YeePayApiService::current();
    }

    private function setParameter()
    {
        $this->parameters = request()->input();
    }

    /**
     * 添加回调日志
     */
    private function addLog($type)
    {
        $this->notifyLog = new YeePayNotifyLogModel();
        $this->notifyLog->request_param = request()->input();
        $this->notifyLog->type = $type;
        $this->notifyLog->save();
    }

    private function editLog($uniacid)
    {
        $this->notifyLog->uniacid = $uniacid;
        $this->notifyLog->save();
    }

    /**
     * @param $trade_number
     */
    private function setWithdraw($trade_number)
    {
        $this->withdraw = YeePayWithdrawModel::where('order_sn',$trade_number)->first();
        if (!$this->withdraw) {
            \Log::debug('易宝代付提现订单回调通知：提现订单信息未找到',$trade_number);
            exit('success');
        }
        \Setting::$uniqueAccountId = \YunShop::app()->uniacid = $this->withdraw->uniacid;
        $this->editLog($this->withdraw->uniacid);
    }

    /**
     * @param $withdraw_sn
     */
    private function setWithdrawSn($withdraw_sn)
    {
        $this->withdraw = YeePayWithdrawModel::where('withdraw_sn',$withdraw_sn)->first();
        if (!$this->withdraw) {
            \Log::debug('易宝代付提现订单回调通知：提现订单信息未找到',$withdraw_sn);
            exit('success');
        }
        \Setting::$uniqueAccountId = \YunShop::app()->uniacid = $this->withdraw->uniacid;
        $this->editLog($this->withdraw->uniacid);
    }

    //异步创建易宝代付订单通知
    public function notifyUrl()
    {
        if (empty($this->parameters['body']) && !empty($this->parameters['type']) && $this->parameters['type'] == 'payment_order') {
            //付款回调
            $this->payNotify();
        } elseif (!empty($this->parameters['body'])) {
            //订单落地回调
            $this->parameters = json_decode($this->parameters['body'],true);
            if (empty($this->parameters['type']) || $this->parameters['type'] <> 'create_batch_order') {
                \Log::debug('易宝代付提现回调通知：参数异常',$this->parameters);
                exit('success');
            }
            $this->createOrderNotify();
        } else {
            \Log::debug('易宝代付提现回调通知：参数异常',$this->parameters);
            exit('success');
        }
    }

    private function createOrderNotify()
    {
        $this->addLog(1);
        if (empty($this->parameters['trade_number'])) {
            \Log::debug('----易宝代付订单落地回调----trade_number为空',$this->parameters);
            exit('success');
        }
        $this->setWithdraw($this->parameters['trade_number']);

        try {
            if (empty($this->parameters['return_code']) || $this->parameters['return_code'] <> 'SUCCESS') {
                throw new \Exception($this->parameters['return_msg']?:'return_code不为success:'.$this->parameters['return_msg']);
            }
            //todo 易宝返回的data里是二维数组，我们每次只创建一个订单，所以只取0键位数组数据即可
            if ($this->parameters['data'][0]['result_code'] <> 'SUCCESS') {
                throw new \Exception('result_code不为success:'.$this->parameters['data'][0]['result_code']);
            }
            if (empty($this->parameters['data'][0]['enterprise_order_id'])) {
                throw new \Exception('创建的批次ID为空');
            }
            $order = $this->api()->findOrderFromRequestNo($this->withdraw->withdraw_sn);
            if (!$order) {
                throw new \Exception('易宝接口查询订单未找到订单信息');
            }
            $this->withdraw->enterprise_order_id = $this->parameters['data'][0]['enterprise_order_id'];
            $this->withdraw->status = 1;
            $this->withdraw->save();
            exit('success');
        } catch (\Exception $e) {
            $this->withdraw->fail_text = $e->getMessage();
            $this->withdraw->status = -1;
            $this->withdraw->save();
            exit('success');
        }
    }

    private function payNotify()
    {
        $this->addLog(2);
        if (empty($this->parameters['request_no'])) {
            \Log::debug('----易宝代付提现打款回调----request_no为空',$this->parameters);
            exit('success');
        }
        $this->setWithdrawSn(($this->parameters['request_no']));

        if (!$this->api()->verifySign($this->parameters)) {
            \Log::debug('----易宝代付提现打款回调----验签失败');
            exit('success');
        }
        try {
            if ($this->parameters['withdrawal_status'] == 'TRADE_FINISHED') {
                event(new WithdrawSuccessEvent($this->withdraw->withdraw_sn));
                \Log::debug('----易宝代付提现打款回调----');
                $this->withdraw->pay_status = 3;
                $this->withdraw->save();
                exit('success');
            } else {
                switch ($this->parameters['withdrawal_status']) {
                    case 'TRADE_FAILED':
                        throw new \Exception('提现失败');
                    case 'WITHDRAWAL_SUBMITTED':
                        throw new \Exception('已受理');
                    case 'REFUND_TICKET':
                        throw new \Exception('退票成功');
                    default:
                        throw new \Exception('未知状态');
                }
            }
        } catch (\Exception $e) {
            $this->withdraw->fail_text = $e->getMessage();
            $this->withdraw->pay_status = 2;
            $this->withdraw->save();
            exit('success');
        }
    }

}