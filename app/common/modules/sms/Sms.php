<?php
/**
 * Created by PhpStorm.
 * Author: 芸众商城 www.yunzshop.com
 * Date: 2021/2/2
 * Time: 14:09
 */
namespace app\common\modules\sms;

use app\common\services\Session;
use app\frontend\modules\member\models\smsSendLimitModel;

abstract class Sms
{
    public $sms;

    private $smsDeadLine = 5;

    public function __construct($sms)
    {
        $this->sms = $sms;
    }

    //通用(注册）
    abstract function sendCode($mobile, $state);

    //登录
    abstract function sendLog($mobile, $state);

    //找回密码
    abstract function sendPwd($mobile ,$state);

    //余额定时提醒
    abstract function sendBalance($mobile, $ext);

    //商品发货提醒
    abstract function sendGoods($mobile, $ext);

    //会员充值提醒
    abstract function sendMemberRecharge($mobile, $ext);

    /**
     * 更新发送短信条数
     *
     * 每天最多5条
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

        \Cache::put('app_login_'.$mobile, $code, 1);
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