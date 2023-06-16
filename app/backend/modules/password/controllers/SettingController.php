<?php
/****************************************************************
 * Author:  king -- LiBaoJia
 * Date:    5/19/21 10:11 AM
 * Email:   livsyitian@163.com
 * QQ:      995265288
 * IDE:     PhpStorm
 * 
 * 
 *
 ****************************************************************/


namespace app\backend\modules\password\controllers;


use app\common\components\BaseController;
use app\common\facades\Setting;
use app\common\modules\shop\ShopConfig;

class SettingController extends BaseController
{
    public function index()
    {
        if ($this->postData()) return $this->store();

        return view('password.setting', $this->viewData());
    }

    /**
     * 数据存储
     */
    private function store()
    {
        $postData = $this->postData();
        $postData['withdraw_verify'] = $this->setWithdrawVerify();
        collect($postData)->each(function ($item, $key) {
            Setting::set("pay_password.{$key}", $item);
        });

        return $this->successJson('支付密码设置成功');
    }

    private function setWithdrawVerify()
    {
        $withdraw_verify = [];
        if (request()->withdraw_verify) {
            $data = request()->withdraw_verify;
            if (($data['is_phone_verify'] || $data['is_member_export_verify'] || $data['is_commission_export_verify']) && !$data['phone']) {
                return $this->errorJson('开启了校验验证，必须设置校验手机号');
            }
            if ($data['verify_expire'] && intval($data['verify_expire']) > 120) {
                return $this->errorJson('校验有效期不得超过120分钟');
            }
            $set = $this->setData()['withdraw_verify']?:[];
            if ($set && $set['phone']) {//之前已设置手机号
                if ($data['phone'] && $data['phone'] <> $set['phone']) {//更改了手机号
                    //验证原手机
                    $check = app('sms')->checkCode($set['phone'],$data['form2']['verify_code'],'_editWithdraw');
                    if ($check['status'] == 0) {
                        return $this->errorJson('原手机验证码验证错误：'.$check['json']);
                    }
                    //验证新手机
                    $check = app('sms')->checkCode($data['phone'],$data['form2']['verify_code_new'],'_editWithdrawNew');
                    if ($check['status'] == 0) {
                        return $this->errorJson('新手机验证码验证错误：'.$check['json']);
                    }
                } elseif(($set['is_phone_verify'] && !$data['is_phone_verify']) ||
                    ($set['is_member_export_verify'] && !$data['is_member_export_verify']) ||
                    ($set['is_commission_export_verify'] && !$data['is_commission_export_verify'])) {//原先开启的现关闭
                    //验证手机验证码
                    if (empty($data['form3']['verify_code'])) {
                        return $this->errorJson('关闭场景需要验证手机验证码，请填写');
                    }
                    $check = app('sms')->checkCode($set['phone'],$data['form3']['verify_code'],'_closeWithdraw');
                    if ($check['status'] == 0) {
                        return $this->errorJson('手机验证码验证错误：'.$check['json']);
                    }
                }
            } else {//没有设置过手机号
                if ($data['phone']) {
                    $check = app('sms')->checkCode($data['phone'],$data['form1']['verify_code'],'_setWithdraw');
                    if ($check['status'] == 0) {
                        return $this->errorJson($check['json']);
                    }
                }
            }
            $withdraw_verify = [
                'is_set_phone' => $data['phone']?1:0,
                'is_phone_verify' => $data['is_phone_verify']?1:0,
                'is_member_export_verify' => $data['is_member_export_verify']?1:0,
                'is_commission_export_verify' => $data['is_commission_export_verify']?1:0,
                'phone' => $data['phone']?:"",
                'verify_expire' => $data['verify_expire']?:"",
            ];
        }
        return $withdraw_verify;
    }

    /**
     * 提交数据
     *
     * @return array
     */
    private function postData()
    {
        return request()->input('pay_password', []);
    }

    /**
     * view 数据
     *
     * @return array
     */
    private function viewData()
    {
        return [
            'setting'   => $this->setData(),
            'condition' => $this->conditionData(),
            'withdraw_verify' => $this->setData()['withdraw_verify'] ?: [],
        ];
    }

    /**
     * 设置数据
     *
     * @return array
     */
    private function setData()
    {
        return Setting::getByGroup('pay_password') ?: [];
    }

    /**
     * 自动加载插件配置使用支付密码项
     *
     * @return array
     */
    private function conditionData()
    {
        return ShopConfig::current()->get('password');
    }

    /**
     * 发送验证码
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendVerifyCode()
    {
        $phone = request()->phone;
        switch (request()->type) {
            case 1://设置提现手机号获取验证码
                $key = '_setWithdraw';
                break;
            case 2://更改提现手机号获取原手机号验证码
                $key = '_editWithdraw';
                $set = $this->setData()['withdraw_verify'];
                $phone = $set['phone'];
                break;
            case 3://更改提现手机号获取新手机号验证码
                $key = '_editWithdrawNew';
                break;
            case 4://关闭提现手机号验证获取原手机号验证码
                $key = '_closeWithdraw';
                $set = $this->setData()['withdraw_verify'];
                $phone = $set['phone'];
                break;
            default:
                return $this->errorJson('类型错误');
        }
        if (!$phone) {
            return $this->errorJson('手机号不能为空');
        }
        $sms = app('sms')->sendWithdrawSet($phone,'86',$key);
        if ($sms['status'] == 0) {
            return $this->errorJson($sms['json']);
        }
        return $this->successJson();
    }

    /**
     * 校验验证码
     * @return \Illuminate\Http\JsonResponse
     */
    public function verifyWithdrawCode()
    {
        $sms = app('sms');
        $code = request()->code;
        $phone = request()->phone;
        if (empty($code)) {
            return $this->errorJson('请填写验证码');
        }
        if (empty($phone) && request()->type <> 3) {
            return $this->errorJson('请填写手机号');
        }
        $set = $this->setData()['withdraw_verify'];
        switch (request()->type) {
            case 1: //设置提现手机号验证校验验证码
                if ($set && $set['phone']) {
                    return $this->errorJson('使用验证类型错误!');
                }
                $key = '_setWithdraw';
                $check = $sms->checkCode($phone,$code,$key);
                if ($check['status'] == 0) {
                    return $this->errorJson($check['json']);
                }
                break;
            case 2://更改提现手机号验证校验验证码
                if (empty($set) || !$set['phone']) {
                    return $this->errorJson('使用验证类型错误!');
                }
                $oldCode = request()->oldCode;
                if (empty($oldCode)) {
                    return $this->errorJson('请填写原手机号的验证码');
                }
                $key = '_editWithdraw';
                $check = $sms->checkCode($set['phone'],$oldCode,$key);
                if ($check['status'] == 0) {
                    return $this->errorJson($check['json']);
                }
                $key = '_editWithdrawNew';
                $check = $sms->checkCode($phone,$code,$key);
                if ($check['status'] == 0) {
                    return $this->errorJson($check['json']);
                }
                break;
            case 3://关闭提现手机号验证校验验证码
                if (empty($set) || !$set['phone']) {
                    return $this->errorJson('使用验证类型错误!');
                }
                $key = '_closeWithdraw';
                $check = $sms->checkCode($set['phone'],$code,$key);
                if ($check['status'] == 0) {
                    return $this->errorJson($check['json']);
                }
                break;
            default:
                return $this->errorJson('验证码校验类型错误');
        }
        return $this->successJson('校验通过');
    }
}
