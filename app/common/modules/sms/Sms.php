<?php
/**
 * Created by PhpStorm.
 * Author:
 * Date: 2021/2/2
 * Time: 14:09
 */
namespace app\common\modules\sms;

use app\common\services\Session;
use app\frontend\modules\member\models\smsSendLimitModel;
use app\common\helpers\Cache;

abstract class Sms
{
    public $sms;

    private $smsDeadLine = 1000;//说改成不限制，（保留限制功能）

    public $template; //发送模板

    public $key = '';

    public function __construct($sms)
    {
        $this->sms = $sms;
    }

    //通用(注册）
    public function sendCode($mobile, $state = '86')
    {
        $this->template = 'register';
        if ($this->smsSendLimit($mobile)) {
            $res = $this->_sendCode($mobile, $state);
            if ($res === true) {
                $this->updateSmsSendTotal($mobile);
                return $this->show_json(1);
            } else {
                return $this->show_json(0, $res);
            }
        } else {
            return $this->show_json(0,  '发送短信数量达到今日上限');
        }
    }

    //登录
    public function sendLog($mobile, $state = '86')
    {
        $this->template = 'login';
        $res = $this->_sendCode($mobile, $state);
        if ( $res === true) {
            return $this->show_json(1);
        } else {
            return $this->show_json(0,  $res);
        }
    }

    //找回密码
    public function sendPwd($mobile ,$state)
    {
        $this->template = 'password';
        $res = $this->_sendCode($mobile, $state);
        if ( $res === true) {
            return $this->show_json(1);
        } else {
            return $this->show_json(0,  $res);
        }
    }

    //余额定时提醒
    public function sendBalance($mobile, $ext)
    {
        $this->template = 'balance';
        $res = $this->_sendCode($mobile, '86',$ext);
        if ( $res === true) {
            return $this->show_json(1);
        } else {
            return $this->show_json(0,  $res);
        }
    }

    //商品发货提醒
    public function sendGoods($mobile, $ext)
    {
        $this->template = 'goods';
        $res = $this->_sendCode($mobile, '86',$ext);
        if ( $res === true) {
            return $this->show_json(1);
        } else {
            return $this->show_json(0,  $res);
        }
    }

    //会员充值提醒
    public function sendMemberRecharge($mobile, $ext)
    {
        $this->template = 'member_recharge';
        $res = $this->_sendCode($mobile, '86',$ext);
        if ( $res === true) {
            return $this->show_json(1);
        } else {
            return $this->show_json(0,  $res);
        }
    }

    public function sendWithdrawSet($mobile, $state = '86',$key='')
    {
        $this->key = $key;
        $this->template = 'withdraw_set';
        if ($this->smsSendLimit($mobile)) {
            $res = $this->_sendCode($mobile, $state);
            if ( $res === true) {
                $this->updateSmsSendTotal($mobile);
                return $this->show_json(1);
            } else {
                return $this->show_json(0,  $res);
            }
        } else {
            return $this->show_json(0,  '发送短信数量达到今日上限');
        }
    }

    /**
     * @param $mobile
     * @param $state
     * @param null $ext
     * @return string|bool
     */
    abstract function _sendCode($mobile, $state, $ext = null);

    /**
     * 更新发送短信条数
     * 每天最多5条
     * @param $mobile
     */
    protected function updateSmsSendTotal($mobile)
    {
        $uniacid = \Yunshop::app()->uniacid;

        $curr_time = time();

        $mobile_info = smsSendLimitModel::getMobileInfo($uniacid, $mobile);

        if (!empty($mobile_info)) {
            $update_time = $mobile_info['created_at'];
            $total = $mobile_info['total'];

            if ($update_time <= $curr_time) {
                if (date('Ymd', $curr_time) == date('Ymd', $update_time)) {
                    if ($total <= 5) {
                        ++$total;

                        smsSendLimitModel::updateData(array(
                            'uniacid' => $uniacid,
                            'mobile' => $mobile), array(
                            'total' => $total,
                            'created_at' => $curr_time));
                    }
                } else {
                    smsSendLimitModel::updateData(array(
                        'uniacid' => $uniacid,
                        'mobile' => $mobile), array(
                        'total' => 1,
                        'created_at' => $curr_time));
                }
            }
        } else {
            smsSendLimitModel::insertData(array(
                    'uniacid' => $uniacid,
                    'mobile' => $mobile,
                    'total' => 1,
                    'created_at' => $curr_time)
            );
        }
    }

    protected function getCode($mobile ,$key = '')
    {
        $code = rand(1000, 9999);

        Session::set('codetime'.$key, time());
        Session::set('code'.$key, $code);
        Session::set('code_mobile'.$key, $mobile);

        Cache::put('app_login_'.$mobile, $code, 5);
		Cache::forget('code_num_'.$mobile);

        return $code;
    }

    protected function smsSendLimit($mobile)
    {
        $uniacid = \Yunshop::app()->uniacid;

        $curr_time = time();

        $mobile_info = smsSendLimitModel::getMobileInfo($uniacid, $mobile);

        if (!empty($mobile_info)) {
            $update_time = $mobile_info['created_at'];
            $total = $mobile_info['total'];

            if ((date('Ymd', $curr_time) != date('Ymd', $update_time))) {

                $total = 0;
            }
        } else {
            $total = 0;
        }

        if ($total < $this->smsDeadLine) {
            return true;
        } else {
            return false;
        }

    }

    protected function show_json($status = 1, $return = null)
    {
        return array(
            'status' => $status,
            'json' => $return,
        );
    }
}