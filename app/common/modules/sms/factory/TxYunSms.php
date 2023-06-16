<?php
/**
 * Created by PhpStorm.
 * Author:
 * Date: 2021/2/2
 * Time: 17:52
 */

namespace app\common\modules\sms\factory;


use app\common\modules\sms\Sms;
use app\common\services\txyunsms\SmsSingleSender;

class TxYunSms extends Sms
{
    public function _sendCode($mobile,$state,$ext = [])
    {
        switch ($this->template) {
            case 'register':
                $template = 'tx_templateCode';
                $ext = [$this->getCode($mobile)];
                break;
            case 'password':
                $template = 'tx_templateCodeForget';
                $ext = [$this->getCode($mobile)];
                break;
            case 'login':
                if(empty($this->sms['tx_templateCodeLogin'])){
                    $template = 'tx_templateCode';
                }else{
                    $template = 'tx_templateCodeLogin';
                }
                $ext = [$this->getCode($mobile)];
                break;
            case 'balance':
                $template = 'tx_templateBalanceCode';
                break;
            case 'member_recharge':
                $template = 'tx_templatereChargeCode';
                break;
            case 'goods':
                $template = 'tx_templateSendMessageCode';
                break;
            case 'withdraw_set':
                $template = 'tx_templateCode';
                $ext = [$this->getCode($mobile,$this->key)];
                break;
            default:
                return '短信发送失败：未知短信类型';
        }
        $ext = array_values($ext);
        if(empty($this->sms[$template])){
            return '发送失败，请检查短信配置!';
        }
        $sender = new SmsSingleSender(trim($this->sms['tx_sdkappid']), trim($this->sms['tx_appkey']));
        $response = $sender->sendWithParam($state, $mobile, $this->sms[$template], $ext, $this->sms['tx_signname'], "", "");  // 签名参数不能为空串
        $response = json_decode($response);

        if ($response->result != 0 || $response->errmsg != 'OK') {
            return $response->errmsg;
        }
        return true;
    }
}