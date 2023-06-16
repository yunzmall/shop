<?php


namespace app\common\modules\sms\factory;


use app\common\modules\sms\Sms;
use Yunshop\LxSms\services\LxSmsService;

class LxSms extends Sms
{
    public function _sendCode($mobile, $state, $ext = null)
    {
        if (!$ext) {
            $ext = [];
        }
        $code = $this->getCode($mobile,$this->key);
        switch ($this->template) {
            case 'register':
                $content = $this->sms['lx_templateCode'];
                $ext = array_merge($ext, ['number' => $code]);
                break;
            case 'password':
                $content = $this->sms['lx_templateCodeForget'];
                $ext = array_merge($ext, ['number' => $code]);
                break;
            case 'login':
                if (empty($this->sms['lx_templateCodeLogin'])) {
                    $content = $this->sms['lx_templateCode'];
                } else {
                    $content = $this->sms['lx_templateCodeLogin'];
                }
                $ext = array_merge($ext, ['number' => $code]);
                break;
            case 'balance':
                $content = $this->sms['lx_templateBalanceCode'];
                break;
            case 'member_recharge':
                $content = $this->sms['lx_templatereChargeCode'];
                break;
            case 'goods':
                $content = $this->sms['lx_templateSendMessageCode'];
                break;
            case 'withdraw_set':
                $content = $this->sms['lx_templateCode'];
                $ext = array_merge($ext, ['number' => $code]);
                break;
            default:
                return '短信发送失败：未知短信类型';
        }
        try {
            if (!app('plugins')->isEnabled('lx-sms')) {
                throw new \Exception('未开启验证码，请联系管理员！');
            }
            $data = [
                'sendPhone' => $mobile,
                'accName' => $this->sms['acc_name'],
                'accPwd' => $this->sms['acc_pwd'],
                'msg' => $this->changeContent(trim($content), $ext).'【' . $this->sms['lx_sign'] . '】',
            ];
            $sms = new LxSmsService();
            $res = $sms->send($data);
            if ($res['replyCode'] != 1) {
                throw new \Exception($res['replyMsg']);
            }
            return true;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    private function changeContent($content, $ext)
    {
        if (strexists($content, '[number]') && $ext['number']) {
            $content = str_replace('[number]', $ext['number'], $content);
        }
        if (strexists($content, '[preshop]') && $ext['preshop']) {
            $content = str_replace('[preshop]', $ext['preshop'], $content);
        }
        if (strexists($content, '[endshop]') && $ext['endshop']) {
            $content = str_replace('[endshop]', $ext['endshop'], $content);
        }
        if (strexists($content, '[amount]') && $ext['amount']) {
            $content = str_replace('[amount]', $ext['amount'], $content);
        }
        if (strexists($content, '[amounts]') && $ext['amounts']) {
            $content = str_replace('[amounts]', $ext['amounts'], $content);
        }
        if (strexists($content, '[date]') && $ext['date']) {
            $content = str_replace('[date]', $ext['date'], $content);
        }
        if (strexists($content, '[name]') && $ext['name']) {
            $content = str_replace('[name]', $ext['name'], $content);
        }
        if (strexists($content, '[time]') && $ext['time']) {
            $content = str_replace('[time]', $ext['time'], $content);
        }
        if (strexists($content, '[shop]') && $ext['shop']) {
            $content = str_replace('[shop]', $ext['shop'], $content);
        }
        return $content;
    }
}