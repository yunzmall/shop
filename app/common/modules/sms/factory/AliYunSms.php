<?php
/**
 * Created by PhpStorm.
 * Author:
 * Date: 2021/2/2
 * Time: 16:05
 */

namespace app\common\modules\sms\factory;


use app\common\modules\sms\Sms;

class AliYunSms extends Sms
{
    public function _sendCode($mobile, $state, $ext = null)
    {
        switch ($this->template) {
            case 'register':
                $template = 'aly_templateCode';
                $ext = ["number" => $this->getCode($mobile)];
                break;
            case 'password':
                $template = 'aly_templateCodeForget';
                $ext = ["number" => $this->getCode($mobile)];
                break;
            case 'login':
                if(empty($this->sms['aly_templateCodeLogin'])){
                    $template = 'aly_templateCode';
                }else{
                    $template = 'aly_templateCodeLogin';
                }
                $ext = ["number" => $this->getCode($mobile)];
                break;
            case 'balance':
                $template = 'aly_templateBalanceCode';
                break;
            case 'member_recharge':
                $template = 'aly_templatereChargeCode';
                break;
            case 'goods':
                $template = 'aly_templateSendMessageCode';
                break;
            case 'withdraw_set':
                $template = 'aly_templateCode';
                $ext = ["number" => $this->getCode($mobile,$this->key)];
                break;
            default:
                return '短信发送失败：未知短信类型';
        }

        if(empty($this->sms[$template])){
            return '发送失败，请检查短信配置!';
        }

        $aly_sms = new \app\common\services\aliyun\AliyunSMS(trim($this->sms['aly_appkey']), trim($this->sms['aly_secret']));

        $response = $aly_sms->sendSms(
            $this->sms['aly_signname'], // 短信签名
            $this->sms[$template], // 短信模板编号
            $mobile, // 短信接收者
            $ext //封装好的数据
        );
        if ($response->Code != 'OK' || $response->Message != 'OK') {
            return $response->Message;
        }
        return true;
    }
}
