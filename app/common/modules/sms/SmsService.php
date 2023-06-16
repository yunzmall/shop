<?php
/**
 * Created by PhpStorm.
 * Author:
 * Date: 2021/2/2
 * Time: 14:35
 */

namespace app\common\modules\sms;

use app\common\modules\sms\factory\SmsFactory;
use app\common\services\Session;
use app\common\helpers\Cache;

class SmsService
{
    //通用(注册）
    public function sendCode($mobile, $state = '86',$type = 0)
    {
        $class = SmsFactory::getSmsFactory($type);

        if (get_class($class)) {
			return $class->sendCode($mobile, $state);
        }

        return $this->show_json(0, '发送短信失败，请检查后台短信配置');
    }

    //找回密码
    public function sendPwd($mobile, $state = '86',$type = 0)
    {
        $class = SmsFactory::getSmsFactory($type);

        if (get_class($class)) {
            return $class->sendPwd($mobile, $state);
        }

        return $this->show_json(0, '发送短信失败，请检查后台短信配置');
    }

    //登录
    public function sendLog($mobile, $state = '86',$type = 0)
    {
        $class = SmsFactory::getSmsFactory($type);

        if (get_class($class)) {
            return $class->sendLog($mobile, $state);
        }

        return $this->show_json(0, '发送短信失败，请检查后台短信配置');
    }

    //余额定时提醒
    public function sendBalance($mobile, $etx = [])
    {
        $class = SmsFactory::getSmsFactory(0);

        if (get_class($class)) {
            return $class->sendBalance($mobile, $etx);
        }

        return $this->show_json(0, '发送短信失败，请检查后台短信配置');
    }

    //商品发货提醒
    public function sendGoods($mobile, $etx = [])
    {
        $class = SmsFactory::getSmsFactory(0);

        if (get_class($class)) {
            return $class->sendGoods($mobile, $etx);
        }

        return $this->show_json(0, '发送短信失败，请检查后台短信配置');
    }

    //会员充值提醒
    public function sendMemberRecharge($mobile, $etx = [])
    {
        $class = SmsFactory::getSmsFactory(0);

        if (get_class($class)) {
            return $class->sendMemberRecharge($mobile, $etx);
        }

        return $this->show_json(0, '发送短信失败，请检查后台短信配置');
    }

    //设置提现校验手机号
    public function sendWithdrawSet($mobile, $state = '86',$key='')
    {
        $class = SmsFactory::getSmsFactory(0);

        if (get_class($class)) {
            return $class->sendWithdrawSet($mobile, $state,$key);
        }

        return $this->show_json(0, '发送短信失败，请检查后台短信配置');
    }

    public function checkCode($mobile, $code,$key = '')
    {
        //app验证码统一用cache
        if (request()->type == 14 || request()->type == 15) {
            return $this->checkAppCode($mobile,$code);
        }
        if ((Session::get('codetime'.$key) + 60 * 5) < time()) {
            return $this->show_json(0, '验证码已过期,请重新获取');
        }

        if (Session::get('code_mobile'.$key) != $mobile) {
            return $this->show_json(0, '手机号错误,请重新获取');
        }
		//增加次数验证
		if (Cache::get('code_num_'.$mobile) >= 5) {
			return $this->show_json(0, '验证码错误次数过多,请重新获取');
		}
        if (Session::get('code'.$key) != $code) {
			Cache::increment('code_num_'.$mobile);
			return $this->show_json(0, '验证码错误,请重新获取');
        }
        return $this->show_json(1);
    }

    public function checkAppCode($mobile, $code)
    {
        $key = 'app_login_'. $mobile;
        if (!Cache::has($key)) {
            return $this->show_json('0','验证码已过期，请重新获取');
        }
        $value = Cache::get($key);
		//增加次数验证
		if (Cache::get('code_num_'.$mobile) >= 5) {
			return $this->show_json(0, '验证码错误次数过多,请重新获取');
		}
        if ($code != $value) {
			Cache::increment('code_num_'.$mobile);
			return $this->show_json('0','验证失败，请重新获取验证码');
        }
        Cache::forget($key);
        return $this->show_json('1');
    }

    protected function show_json($status = 1, $return = null)
    {
        return array(
            'status' => $status,
            'json' => $return,
        );
    }
}