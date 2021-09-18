<?php
/**
 * Created by PhpStorm.
 * Author: 芸众商城 www.yunzshop.com
 * Date: 2021/2/2
 * Time: 17:52
 */

namespace app\common\modules\sms\factory;


use app\common\modules\sms\Sms;
use app\common\services\txyunsms\SmsSingleSender;

class TxYunSms extends Sms
{
    public function sendCode($mobile, $state)
    {
        if ($this->smsSendLimit($mobile)) {
            $response = $this->_sendCode($mobile, $state, 'tx_templateCode', [$this->getCode($mobile)]);
            if ($response->result == 0 && $response->errmsg == 'OK') {
                $this->updateSmsSendTotal($mobile);
                return $this->show_json(1);
            } else {
                return $this->show_json(0, $response->errmsg);
            }
        } else {
            return $this->show_json(0, '发送短信数量达到今日上限');
        }
    }

    public function sendPwd($mobile, $state)
    {
        $response = $this->_sendCode($mobile, $state, 'tx_templateCodeForget', [$this->getCode($mobile)]);

        if ($response->result == 0 && $response->errmsg == 'OK') {
            return $this->show_json(1);
        } else {
            return $this->show_json(0, $response->errmsg);
        }
    }

    public function sendLog($mobile, $state)
    {
        if(empty($this->sms['tx_templateCodeLogin'])){
            $response = $this->_sendCode($mobile, $state, 'tx_templateCode', [$this->getCode($mobile)]);
        }else{
            $response = $this->_sendCode($mobile, $state, 'tx_templateCodeLogin', [$this->getCode($mobile)]);
        }

        if ($response->result == 0 && $response->errmsg == 'OK') {
            return $this->show_json(1);
        } else {
            return $this->show_json(0, $response->errmsg);
        }

    }

    public function sendMemberRecharge($mobile, $ext)
    {
        $ext = array_values($ext);
        $response = $this->_sendCode($mobile, '86', 'tx_templatereChargeCode', $ext);

        if ($response->result == 0 && $response->errmsg == 'OK') {
            return $this->show_json(1);
        } else {
            return $this->show_json(0, $response->errmsg);
        }

    }

    public function sendGoods($mobile, $ext)
    {
        $ext = array_values($ext);
        $response = $this->_sendCode($mobile, '86', 'tx_templateSendMessageCode', $ext);

        if ($response->result == 0 && $response->errmsg == 'OK') {
            return $this->show_json(1);
        } else {
            return $this->show_json(0, $response->errmsg);
        }

    }

    public function sendBalance($mobile, $ext)
    {
        $ext = array_values($ext);
        $response = $this->_sendCode($mobile, '86', 'tx_templateBalanceCode', $ext);

        if ($response->result == 0 && $response->errmsg == 'OK') {
            return $this->show_json(1);
        } else {
            return $this->show_json(0, $response->errmsg);
        }
    }

    public function sendWithdrawSet($mobile, $state,$key='')
    {
        $response = $this->_sendCode($mobile, $state, 'tx_templateCode', [$this->getCode($mobile,$key)]);

        if ($response->result == 0 && $response->errmsg == 'OK') {
            return $this->show_json(1);
        } else {
            return $this->show_json(0, $response->errmsg);
        }
    }

    private function _sendCode($mobile, $state, $template, $ext = [])
    {
        if(empty($this->sms[$template])){
            return $this->show_json(0, '发送失败，请检查短信配置!');
        }
        $sender = new SmsSingleSender(trim($this->sms['tx_sdkappid']), trim($this->sms['tx_appkey']));
        $response = $sender->sendWithParam($state, $mobile, $this->sms[$template], $ext, $this->sms['tx_signname'], "", "");  // 签名参数不能为空串
        return json_decode($response);

    }
}