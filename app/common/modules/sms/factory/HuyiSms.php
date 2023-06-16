<?php
/**
 * Created by PhpStorm.
 * Author:
 * Date: 2021/2/2
 * Time: 14:50
 */

namespace app\common\modules\sms\factory;


use app\common\modules\sms\Sms;

class HuyiSms extends Sms
{
    public function sendBalance($mobile, $state)
    {
        return;
    }

    public function sendGoods($mobile, $state)
    {
        return;
    }

    public function sendMemberRecharge($mobile, $state)
    {
        return;
    }

    public function _sendCode($mobile, $state,$key='')
    {
        $code = $this->getCode($mobile,$this->key);

        $content = "您的验证码是：" . $code . "。请不要把验证码泄露给其他人。如非本人操作，可不用理会！";

        if ($state == '86') {
            $account = trim($this->sms['account']);
            $pwd = trim($this->sms['password']);
            $url = 'http://106.ihuyi.cn/webservice/sms.php?method=Submit';
            $smsrs = file_get_contents($url . '&account=' . $account . '&password=' . $pwd . '&mobile=' . $mobile . '&content=' . rawurlencode($content));
        } else {
            $account = trim($this->sms['account2']);
            $pwd = trim($this->sms['password2']);
            $url = 'http://api.isms.ihuyi.com/webservice/isms.php?method=Submit';
            $mobile = $state . ' ' . $mobile;

            $data = array(
                'account' => $account,
                'password' => $pwd,
                'mobile' => $mobile,
                'content' => $content,
            );
            $query = http_build_query($data);
            $smsrs = file_get_contents($url . '&' . $query);
        }

        $res = xml_to_array($smsrs);

        if ($res['SubmitResult']['code'] != 2) {
            return $res['SubmitResult']['msg'];
        }
        return true;
    }

}