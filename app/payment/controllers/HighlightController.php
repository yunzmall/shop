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
use app\payment\PaymentController;
use Yunshop\HighLight\models\HighLightWithdrawModel;
use Yunshop\HighLight\services\ApiService;
use app\common\services\Pay;
use Yunshop\HighLight\services\WithdrawService;

class HighlightController extends PaymentController
{
    protected $parameters;
    /**
     * @var HighLightWithdrawModel
     */
    protected $withdraw;

    public function __construct()
    {
        parent::__construct();
        $this->setParameter();
        //访问记录
        Pay::payAccessLog();
        //保存响应数据
        Pay::payResponseDataLog('', '高灯提现回调', json_encode($this->parameters));

        if (!app('plugins')->isEnabled('high-light')) {
            \Log::debug('高灯提现回调通知:高灯插件未开启',$this->parameters);
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
        $this->withdraw = HighLightWithdrawModel::where('order_sn',$trade_number)->first();
        if (!$this->withdraw) {
            \Log::debug('高灯提现结算单回调通知：提现订单信息未找到',$trade_number);
            exit('success');
        }
        \Setting::$uniqueAccountId = \YunShop::app()->uniacid = $this->withdraw->uniacid;
    }

    //异步创建高灯结算单通知
    public function notifyUrl()
    {
        try {
            if ($this->parameters['code'] <> 0) {
                throw new \Exception($this->parameters['msg']);
            }
            $this->parameters['data'] = $this->api()->decrypt($this->parameters['data']);
            if (!$this->parameters['data']) {
                throw new \Exception('解密失败');
            }
            $this->parameters['data'] = json_decode($this->parameters['data'],true);
            foreach ($this->parameters['data'] as $item) {
                $this->setWithdraw($item['order_random_code']);
                $this->verifyStatus($item);
            }
        } catch (\Exception $e) {
            \Log::debug('高灯创建结算单回调失败：'.$e->getMessage(),$this->parameters);
        }
        exit('success');   //确保一次回调就返回，流转状态交由定时任务走
    }

    public function verifyStatus($data)
    {
        switch ($data['status']) {
            case 100:
            case 200:
                if ($data['status'] == 100  && !$data['is_verification']) {
                    WithdrawService::setWithdraw($this->withdraw,-1,'结算单创建失败:'.($data['check_error_info'] ? : $data['fail_reason']),null,null,$data['status']);
                } elseif ($data['status'] == 100 && $data['is_verification']) {
                    WithdrawService::setWithdraw($this->withdraw,1,'结算单校验中',null,null,$data['status']);
                } elseif ($data['status'] == 200) {
                    WithdrawService::setWithdraw($this->withdraw,-1,'结算单创建失败已删除:'.$data['fail_reason'],null,null,$data['status']);
                }
                break;
            case 300:
                WithdrawService::setWithdraw($this->withdraw,1,'商户余额不足,充值后状态会自动流转！如需停止支付，需在商户结算后台【财务管理】找到对应结算单-【删除】',null,null,$data['status']);
                break;
            case 600:
                if ($data['hangup_flag']) {
                    $failText = '该用户在多家商户累计本月结算超14900,次月可自动支付，也可在【结算后台-财务人员-挂起订单】中操作退款';
                } else {
                    $failText = '高灯侧挂单中，可在【结算后台-财务人员-挂起订单】中操作退款';
                }
                WithdrawService::setWithdraw($this->withdraw,1,$failText,null,null,$data['status']);
                break;
            case 700:
            case 800:
            case 850:
            case 900:
            case 860:
            case 1006:
            case 910:
            case 920:
            case 930:
                WithdrawService::setWithdraw($this->withdraw,1,'高灯侧正在结算处理中',null,null,$data['status']);
                break;
            //----以下状态需进行退款处理----
            case 610:
                WithdrawService::setWithdraw($this->withdraw,1,'待用户确认结算单',null,null,$data['status']);
                break;
            case 1004:
                $failText = $data['status'] == 610?'打款失败：创建结算单时用户未签约或未认证':'打款失败';
                try {
                    $this->api()->refundBalance(['order_random_code' => [$this->withdraw->order_sn]]);
                    $refundStatus = 1;
                    $refundFailText = null;
                } catch (\Exception $e) {
                    $refundStatus = -1;
                    $refundFailText = '退款失败：'.$e->getMessage().',请到高灯后台进行退款！';
                }
                WithdrawService::setWithdraw($this->withdraw,-1,$failText,$refundStatus,$refundFailText,$data['status']);
                break;
            //------------------------------
            case 1000://打款成功
                WithdrawService::setWithdraw($this->withdraw,2,'',null,null,$data['status']);
                WithdrawService::withdrawSuccess($this->withdraw->withdraw_sn);
                break;
            case 750:
            case 5000:
                //退款完成
                WithdrawService::setWithdraw($this->withdraw,-1,'',2,'',$data['status']);
                break;
            default:
                WithdrawService::setWithdraw($this->withdraw,-1,'未知状态码',null,null,$data['status']);
        }
    }

    //异步高灯退款通知
    public function refundNotifyUrl()
    {
        try {
            if ($this->parameters['code'] <> 0) {
                throw new \Exception($this->parameters['msg']);
            }
            $this->parameters['data'] = $this->api()->decrypt($this->parameters['data']);
            if (!$this->parameters['data']) {
                throw new \Exception('解密失败');
            }
            $this->parameters['data'] = json_decode($this->parameters['data'],true);
            $this->setWithdraw($this->parameters['data']['order_random_code']);
            WithdrawService::setWithdraw($this->withdraw,-1,null,2,'');
        } catch (\Exception $e) {
            \Log::debug('高灯结算单退款回调失败：'.$e->getMessage(),$this->parameters);
        }
        exit('success');   //确保一次回调就返回，流转状态交由定时任务走
    }
}