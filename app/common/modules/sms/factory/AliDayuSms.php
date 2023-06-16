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


class AliDayuSms extends Sms
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
        switch ($this->template) {
            case 'register':
                list($result['template'], $result['params']) = [$this->sms['templateCode'], @explode("\n", $this->sms['product'])];
                break;
            case 'password':
                list($result['template'], $result['params']) = [$this->sms['templateCodeForget'], @explode("\n", $this->sms['forget'])];
                break;
            case 'login':
                if(empty($this->sms['templateCodeLogin'])){
                    list($result['template'], $result['params']) = [$this->sms['templateCode'], @explode("\n", $this->sms['product'])];
                }else{
                    list($result['template'], $result['params']) = [$this->sms['templateCodeLogin'], @explode("\n", $this->sms['login'])];
                }
                break;
            default:
                return '短信发送失败：未知短信类型';
        }
        $code = $this->getCode($mobile,$this->key);

        if (count($result['params']) > 1) {
            $nparam['code'] = "{$code}";
            foreach ($result['params'] as $param) {
                $param = trim($param);
                $explode_param = explode("=", $param);
                if (!empty($explode_param[0])) {
                    $nparam[$explode_param[0]] = "{$explode_param[1]}";
                }
            }
            $content = json_encode($nparam);
        } else {
            $explode_param = explode("=", $result['params'][0]);
            $content = json_encode(array('code' => (string)$code, 'product' => $explode_param[1]));
        }

        $top_client = new \iscms\AlismsSdk\TopClient(trim($this->sms['appkey']), trim($this->sms['secret']));
        $name = trim($this->sms['signname']);
        $templateCode = trim($result['template']);

        config([
            'alisms.KEY' => trim($this->sms['appkey']),
            'alisms.SECRETKEY' => trim($this->sms['secret'])
        ]);

        $sms = new SendsmsPusher($top_client);
        $res = $sms->send($mobile, $name, $content, $templateCode);
        if (!isset($res->result->success)) {
            return $res->msg . '/' . $res->sub_msg;
        }
        return true;
    }

}