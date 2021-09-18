<?php


namespace app\common\listeners;

use app\common\facades\Setting;
use app\common\models\UniAccount;
use app\backend\modules\member\models\Member;
use app\common\services\txyunsms\SmsSingleSender;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Contracts\Events\Dispatcher;


class SmsBalanceListener
{
    use DispatchesJobs;

    public function subscribe(Dispatcher $events)
    {
        //todo 梳理逻辑临时更改，可以提出余额定时短信提醒模型验证

        $events->listen('cron.collectJobs', function () {

            \Log::debug('-------------IN_IA-----------', defined('IN_IA'));

            if (defined('IN_IA')) {
                foreach ($this->enableAccount() as $uniAccount) {

                    \Setting::$uniqueAccountId = \YunShop::app()->uniacid = $uniAccount->uniacid;

                    if (!$this->smsIsEnable()) continue;
                    
                    if (!$this->aliSmsIsEnable() && !$this->txSmsIsEnable()) continue;

                    \Cron::add('smsMessageToMemberMobile' . $uniAccount->uniacid, $this->cronTime(), function () use ($uniAccount) {
                        $this->handle($uniAccount->uniacid);
                    });
                }
            }
        });
    }

    /**
     * 余额定时提醒是否开启（余额设置：开启余额定时提醒）
     *
     * @return bool
     */
    private function smsIsEnable()
    {
        return (bool)Setting::get('finance.balance.sms_send');
    }

    /**
     * 阿里短信余额定时提醒
     *
     * @return bool
     */
    private function aliSmsIsEnable()
    {
        return $this->smsSetType() == 3 && $this->aliSmsCode();
    }

    /**
     * 腾讯云短信余额定时提醒
     *
     * @return bool
     */
    private function txSmsIsEnable()
    {
        return $this->smsSetType() == 5 && $this->txSmsCode();
    }

    /**
     * 阿里短信余额定时提醒模版
     *
     * @return string
     */
    private function aliSmsCode()
    {
        return (string)Setting::get('shop.sms.aly_templateBalanceCode');
    }

    /**
     * 腾讯云短信余额定时提醒模版
     *
     * @return string
     */
    private function txSmsCode()
    {
        return (string)Setting::get('shop.sms.tx_templateBalanceCode');
    }

    /**
     * 短信设置类型，阿里--3，腾讯--5
     *
     * @return string
     */
    private function smsSetType()
    {
        return (string)Setting::get('shop.sms.type');
    }

    private function cronTime()
    {
        return '0 ' . $this->setTime() . ' * * *';
    }

    private function setTime()
    {
        return (string)Setting::get('finance.balance.sms_hour');
    }

    private function enableAccount()
    {
        return UniAccount::getEnable();
    }

    /**
     * 定时发送短信
     * @return bool
     */
    public function handle($uniacid)
    {
        \Log::debug('----------定时短信发送----------');

        \YunShop::app()->uniacid = $uniacid;
        \Setting::$uniqueAccountId = $uniacid;
        $balanceSet = \Setting::get('finance.balance');
        //sms_send 是否开启
        if ($balanceSet['sms_send'] == 0) {
            \Log::debug($uniacid . '未开启');
            return true;
        }
        $this->sendSms($balanceSet ,$uniacid);
        return true;

    }

    private function sendSms($balanceSet, $uniacid)
    {
        //查询余额,获取余额超过该值的用户，并把没有手机号的筛选掉
        $mobile = Member::uniacid()
            ->select('uid', 'mobile', 'credit2')
            ->whereNotNull('mobile')
            ->where('credit2', '>', $balanceSet['sms_hour_amount'])
            ->get();
        if (empty($mobile)) {
            \Log::debug('未找到满足条件会员');
            return true;
        } else {
            $mobile = $mobile->toArray();
        }
        $u = UniAccount::where('uniacid', $uniacid)->first();
        foreach ($mobile as $key => $value) {
            if (!$value['mobile']) {
                continue;
            }
            //todo 发送短信

            $data = Array(  // 短信模板中字段的值
                'preshop' => $u->name,
                'amount' => $value['credit2'],
                'endshop' => $u->name,
            );
            app('sms')->sendBalance($value['mobile'], $data);
            return true;
        }
        return true;
    }

}