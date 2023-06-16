<?php
/**
 * Created by PhpStorm.
 * Author:
 * Date: 2021/2/2
 * Time: 15:33
 */

namespace app\common\modules\sms\factory;


use app\common\modules\sms\Sms;
use iscms\Alisms\SendsmsPusher;


class ElisoftSms extends Sms
{
    public function sendBalance($mobile, $ext)
    {
        return ;
    }

    public function sendGoods($mobile, $ext)
    {
        return;
    }

    public function sendMemberRecharge($mobile, $ext)
    {
        return;
    }

    public function _sendCode($mobile, $state, $ext = null)
    {
        try {
            if (!app('plugins')->isEnabled('elisoftsms')) {
                throw new \Exception('未开启验证码，请联系管理员！');
            }
            $code = $this->getCode($mobile,$this->key);
            $msg = '【' . $this->sms['elisoft_signname'] . '】' . '您的验证码是:' . $code . '。请不要把验证码泄露给其他人。如非本人操作，可不用理会！';
            $account = $this->sms['elisoft_account'];
            $passward = $this->sms['elisoft_password'];
            $param = [
                'c' => urlencode($msg),
                'p' => $mobile,
            ];

            $sms = new \Yunshop\Elisoftsms\services\ElisoftSmsService($account,$passward);
            $res = $sms->setAction('sendbatch')->send($param);
            if ($res['result'] != 1) {
                throw new \Exception($res['errormsg']);
            }
            return true;
        } catch (\Exception $e) {
            \Log::debug('elisoftsms短信'.$e->getMessage(),[isset($res)?$res:'',isset($param)?$param:'']);
            return $e->getMessage();
        }
    }
}