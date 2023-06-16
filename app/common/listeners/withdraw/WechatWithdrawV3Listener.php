<?php

namespace app\common\listeners\withdraw;

use app\common\facades\EasyWeChat;
use app\common\facades\Setting;
use app\common\models\UniAccount;
use app\common\models\WechatWithdrawLog;
use app\common\services\finance\Withdraw;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Yunshop\Supplier\admin\models\SupplierWithdraw;

class WechatWithdrawV3Listener
{
    use DispatchesJobs;

    private $wechat;

    private $min_app;

    private $app;

    public function subscribe()
    {
        \Event::listen('cron.collectJobs', function () {
            \Cron::add('wechat_withdraw_time_task', '*/5 * * * *', function() {
                $this->handle();
                return;
            });
        });
    }

    public function handle()
    {
        $uniAccount = UniAccount::getEnable();
        foreach ($uniAccount as $u) {
            Setting::$uniqueAccountId = \YunShop::app()->uniacid = $u->uniacid;
            $wechatWithdraw = WechatWithdrawLog::uniacid()
                ->search(['status' => 1])
                ->get();
            if ($wechatWithdraw->isEmpty()) {
                continue;
            }
            $this->checkStatus($wechatWithdraw);
        }
    }

    public function checkStatus($wechatWithdraw)
    {
        try {
            $this->setEasyWechat();//设置好easy_wechat
            foreach ($wechatWithdraw as $log) {
                $app = $this->getEasyWechat($log->pay_type);
                $res = $app->transfer_v3->searchBatch([
                    'out_batch_no'=>$log->out_batch_no,
                    'need_query_detail' => true,
                    'detail_status' => 'ALL'
                ]);
                if ($res['code'] == 0) {
                    \Log::debug('微信商家转账到零钱-商家批次单号查询失败',[$log->id,$res]);
                    continue;
                }
                $log->batch_status = $res['data']['transfer_batch']['batch_status'];
                if ($log->batch_status == 'FINISHED') {
                    foreach ($res['data']['transfer_detail_list'] as $detail) {
                        if ($detail['out_detail_no'] != $log->out_detail_no) {
                            continue;
                        }
                        $log->detail_id = $detail['detail_id'];
                        $log->detail_status = $detail['detail_status'];
                        if ($log->detail_status == 'SUCCESS') {
                            $log->status = 2;
                            $this->withdrawSuccess($log);
                        } elseif ($log->detail_status == 'FAIL') {
                            $log->status = -2;
                            $this->withdrawFail($log);
                        }
                        if (!$log->save()) {
                            \Log::debug('微信商家转账到零钱1-定时任务-保存批次状态失败',$detail);
                        }
                    }
                } elseif ($log->batch_status == 'CLOSED') {
                    $log->status = -2;
                    if (!$log->save()) {
                        \Log::debug('微信商家转账到零钱2-定时任务-保存批次状态失败',[$log->id,$res]);
                         continue;
                    }
                    $this->withdrawFail($log);
                } else {
                    if (!$log->save()) {
                        \Log::debug('微信商家转账到零钱3-定时任务-保存批次状态失败',$res);
                    }
                }
            }
        } catch (\Exception $e) {
            \Log::debug('微信商家转账到零钱-定时任务-错误'.$e->getMessage(),$wechatWithdraw);
        }
    }

    public function withdrawFail(WechatWithdrawLog $withdrawLog)
    {
        if ($withdrawLog->type) {//供应商提现
            $withdrawModel = $this->getSupplierWithdraw($withdrawLog->withdraw_sn);
            $withdrawModel->status = 2;
            $withdrawModel->pay_time = null;
            $withdrawModel->save();
        } else {//商城提现
            Withdraw::payFail($withdrawLog->withdraw_sn);
        }
    }

    public function withdrawSuccess(WechatWithdrawLog $withdrawLog)
    {
        if ($withdrawLog->type) {//供应商提现
            $withdrawModel = $this->getSupplierWithdraw($withdrawLog->withdraw_sn);
            $withdrawModel->status = 3;
            $withdrawModel->pay_time = time();
            $withdrawModel->save();
        } else {//商城提现
            Withdraw::paySuccess($withdrawLog->withdraw_sn);
        }
    }

    private function getSupplierWithdraw($apply_sn)
    {
        if (!app('plugins')->isEnabled('supplier')) {
            throw new \Exception('供应商插件未开启');
        }
        $withdrawModel = SupplierWithdraw::where('apply_sn',$apply_sn)->first();
        if (!$withdrawModel) {
            throw new \Exception('供应商提现未找到');
        }
        return $withdrawModel;
    }

    private function setEasyWechat()
    {
        $this->wechat = $this->getEasyWeChatApp($this->payParams(1), '');
        $this->min_app = $this->getEasyWeChatApp($this->payParams(2), '');
        $this->app = $this->getEasyWeChatApp($this->payParams(3), '');
    }

    private function getEasyWechat($pay_type)
    {
        switch ($pay_type) {
            case 1:
                return $this->wechat;
            case 2:
                return $this->min_app;
            case 3:
                return $this->app;
            default:
                throw new \Exception('支付类型错误');
        }
    }

    public function getEasyWeChatApp($pay, $notify_url)
    {
        $options = [
            'app_id'             => $pay['weixin_appid'],
            'secret'             => $pay['weixin_secret'],
            'mch_id'             => $pay['weixin_mchid'],
            'key'                => $pay['weixin_apisecret'],
            'key_v3'             => $pay['weixin_apiv3_secret'] ? : '',
            'cert_path'          => $pay['weixin_cert'],
            'key_path'           => $pay['weixin_key'],
            'notify_url'         => $notify_url
        ];
        $app = EasyWeChat::payment($options);
        return $app;
    }

    private function payParams($pay_type)
    {
        switch ($pay_type) {
            case 1:
                return Setting::get('shop.pay');
            case 2:
                $appletSet = Setting::get('plugin.min_app');
                return [
                    'weixin_appid'     => $appletSet['key'] ? : '',
                    'weixin_secret'    => $appletSet['secret'] ? : '',
                    'weixin_mchid'     => $appletSet['mchid'] ? : '',
                    'weixin_apisecret' => $appletSet['api_secret'] ? : '',
                    'weixin_cert'      => $appletSet['apiclient_cert'] ? : '',
                    'weixin_key'       => $appletSet['apiclient_key'] ? : '',
                    'weixin_apiv3_secret' => $appletSet['api_secret_v3']?:'',
                ];
            case 3:
                $appSet = Setting::get('shop_app.pay');
                return [
                    'weixin_appid'     => $appSet['weixin_appid'] ? : '',
                    'weixin_secret'    => $appSet['weixin_secret'] ? : '',
                    'weixin_mchid'     => $appSet['weixin_mchid'] ? : '',
                    'weixin_apisecret' => $appSet['weixin_apisecret'] ? : '',
                    'weixin_cert'      => $appSet['weixin_cert'] ? : '',
                    'weixin_key'       => $appSet['weixin_key'] ? : '',
                    'weixin_apiv3_secret' => $appSet['weixin_secret_v3'] ? : '',
                ];
        }
    }
}