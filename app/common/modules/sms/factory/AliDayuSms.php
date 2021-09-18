<?php
/**
 * Created by PhpStorm.
 * Author: 芸众商城 www.yunzshop.com
 * Date: 2021/2/2
 * Time: 15:33
 */

namespace app\common\modules\sms\factory;


use app\common\modules\sms\Sms;
use iscms\Alisms\SendsmsPusher;


class AliDayuSms extends Sms
{

    public function sendCode($mobile, $state = '86')
    {
        if ($this->smsSendLimit($mobile)) {

            list($result['template'], $result['params']) = [$this->sms['templateCode'], @explode("\n", $this->sms['product'])];

            $issendsms = $this->_sendCode($mobile, $result);

            if (isset($issendsms->result->success)) {
                $this->updateSmsSendTotal($mobile);
                return $this->show_json(1);
            } else {
                return $this->show_json(0, $issendsms->msg . '/' . $issendsms->sub_msg);
            }
        } else {
            return $this->show_json(0,  '发送短信数量达到今日上限');
        }

    }

    public function sendPwd($mobile, $state = '86')
    {
        list($result['template'], $result['params']) = [$this->sms['templateCodeForget'], @explode("\n", $this->sms['forget'])];

        $issendsms = $this->_sendCode($mobile, $result);

        if (isset($issendsms->result->success)) {
            return $this->show_json(1);
        } else {
            return $this->show_json(0, $issendsms->msg . '/' . $issendsms->sub_msg);
        }

    }

    public function sendLog($mobile, $state = '86')
    {
        if(empty($this->sms['templateCodeLogin'])){
            list($result['template'], $result['params']) = [$this->sms['templateCode'], @explode("\n", $this->sms['product'])];
        }else{
            list($result['template'], $result['params']) = [$this->sms['templateCodeLogin'], @explode("\n", $this->sms['login'])];
        }

        $issendsms = $this->_sendCode($mobile, $result);

        if (isset($issendsms->result->success)) {
            return $this->show_json(1);
        } else {
            return $this->show_json(0, $issendsms->msg . '/' . $issendsms->sub_msg);
        }
    }

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

    public function sendWithdrawSet($mobile, $state = '86',$key='')
    {
        if ($this->smsSendLimit($mobile)) {

            list($result['template'], $result['params']) = [$this->sms['templateCode'], @explode("\n", $this->sms['product'])];

            $issendsms = $this->_sendCode($mobile, $result,$key);

            if (isset($issendsms->result->success)) {
                $this->updateSmsSendTotal($mobile);
                return $this->show_json(1);
            } else {
                return $this->show_json(0, $issendsms->msg . '/' . $issendsms->sub_msg);
            }
        } else {
            return $this->show_json(0,  '发送短信数量达到今日上限');
        }
    }

    private function _sendCode($mobile, $result,$key = '')
    {
        $code = $this->getCode($mobile,$key);

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
        return  $sms->send($mobile, $name, $content, $templateCode);
    }

}