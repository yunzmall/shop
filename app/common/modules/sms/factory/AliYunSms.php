<?php
/**
 * Created by PhpStorm.
 * Author: 芸众商城 www.yunzshop.com
 * Date: 2021/2/2
 * Time: 16:05
 */

namespace app\common\modules\sms\factory;


use app\common\modules\sms\Sms;

class AliYunSms extends Sms
{
    public function sendCode($mobile, $state)
    {

        if ($this->smsSendLimit($mobile)) {

            $response = $this->_sendCode($mobile,'aly_templateCode',  ["number" => $this->getCode($mobile)]);

            if ($response->Code == 'OK' && $response->Message == 'OK') {
                $this->updateSmsSendTotal($mobile);
                return $this->show_json(1);
            } else {
                return $this->show_json(0,  $response->Message);
            }
        } else {
            return $this->show_json(0,  '发送短信数量达到今日上限');
        }

    }

    public function sendPwd($mobile, $state)
    {
        $response = $this->_sendCode($mobile,'aly_templateCodeForget',  ["number" => $this->getCode($mobile)]);

        if ($response->Code == 'OK' && $response->Message == 'OK') {
            return $this->show_json(1);
        } else {
            return $this->show_json(0,  $response->Message);
        }
    }

    public function sendLog($mobile, $state)
    {
        if(empty($this->sms['aly_templateCodeLogin'])){
            $response = $this->_sendCode($mobile,'aly_templateCode',  ["number" => $this->getCode($mobile)]);
        }else{
            $response = $this->_sendCode($mobile,'aly_templateCodeLogin',  ["number" => $this->getCode($mobile)]);
        }

        if ($response->Code == 'OK' && $response->Message == 'OK') {
            return $this->show_json(1);
        } else {
            return $this->show_json(0,  $response->Message);
        }
    }
    public function sendBalance($mobile, $ext)
    {

        $response = $this->_sendCode($mobile,'aly_templateBalanceCode', $ext);

        if ($response->Code == 'OK' && $response->Message == 'OK') {
            return $this->show_json(1);
        } else {
            return $this->show_json(0,  $response->Message);
        }
    }


    public function sendMemberRecharge($mobile, $ext = [])
    {
        $response = $this->_sendCode($mobile,'aly_templatereChargeCode', $ext);

        if ($response->Code == 'OK' && $response->Message == 'OK') {
            return $this->show_json(1);
        } else {
            return $this->show_json(0,  $response->Message);
        }
    }

    public function sendGoods($mobile, $ext = [])
    {
        $response = $this->_sendCode($mobile,'aly_templateSendMessageCode', $ext);

        if ($response->Code == 'OK' && $response->Message == 'OK') {
            return $this->show_json(1);
        } else {
            return $this->show_json(0,  $response->Message);
        }
    }

    public function sendWithdrawSet($mobile, $state,$key='')
    {
        $response = $this->_sendCode($mobile,'aly_templateCode',  ["number" => $this->getCode($mobile,$key)]);

        if ($response->Code == 'OK' && $response->Message == 'OK') {
            return $this->show_json(1);
        } else {
            return $this->show_json(0,  $response->Message);
        }
    }



    private function _sendCode($mobile, $template, $ext = [])
    {
        if(empty($this->sms[$template])){
            return $this->show_json(0, '发送失败，请检查短信配置!');
        }

        $aly_sms = new \app\common\services\aliyun\AliyunSMS(trim($this->sms['aly_appkey']), trim($this->sms['aly_secret']));

        $response = $aly_sms->sendSms(
            $this->sms['aly_signname'], // 短信签名
            $this->sms[$template], // 短信模板编号
            $mobile, // 短信接收者
            $ext //封装好的数据
        );

        return $response;

    }


}
