<?php
/****************************************************************
 * Author:  libaojia
 * Date:    2017/9/20 上午10:10
 * Email:   livsyitian@163.com
 * QQ:      995265288
 * User:    芸众商城 www.yunzshop.com
 ****************************************************************/

namespace app\frontend\modules\member\controllers;


use app\common\components\ApiController;
use app\common\facades\Setting;
use app\common\services\password\PasswordService;
use app\frontend\models\Member;
use app\frontend\models\MemberShopInfo;
use app\frontend\modules\member\services\MemberService;
use app\common\services\Session;

class BalancePasswordController extends ApiController
{
    private $memberModel;


    /**
     * 支付密码是否启用，todo 应该整合到 password 中，需要前端配合，暂未整理
     */
    public function isUse()
    {
        if (!$this->getMemberModel()) return $this->errorJson('未获取到会员信息');

        if ($this->payPasswordState()) return $this->successJson('ok', ['is_use' => true]);

        return $this->successJson('ok', ['is_use' => false]);
    }

    /**
     * 支付密码总开关是否开启，可以考虑是否增加操作项总是否有开启，没有可以返回关闭状态
     *
     * @return bool
     */
    private function payPasswordState()
    {
        return (new PasswordService())->masterSwitch();
    }


    /**
     * 会员是否设置密码
     */
    public function isHasPassword()
    {
        if (!$this->getMemberModel()) return $this->errorJson('未获取到会员信息');

        $mobile = $this->memberModel->mobile ? $this->memberModel->mobile : '';
        if ($this->memberModel->yzMember->pay_password && $this->memberModel->yzMember->salt) {
            return $this->successJson('ok', ['is_has' => true, 'mobile' => $mobile]);
        }
        return $this->successJson('ok', ['is_has' => false, 'mobile' => $mobile]);
    }


    /**
     * 设置支付密码
     * code 1 成功， 2失败， 3未绑定手机号
     * @return \Illuminate\Http\JsonResponse
     */
    public function setPassword()
    {
        $result = $this->checkData();
        if ($result !== true) {
            return $this->successJson($result, ['code' => 2]);
        }

        if (!$this->memberModel->mobile) {
            return $this->successJson('请先绑定手机号', ['code' => 3]);
        }

        $data = (new PasswordService())->create(trim(\YunShop::request()->password));
        $result = MemberShopInfo::where('member_id', \YunShop::app()->getMemberId())->update(['pay_password' => $data['password'], 'salt' => $data['salt']]);

        if (!$result) {
            return $this->errorJson('设置密码失败，请重试', ['code' => 2]);
        }
        return $this->successJson('设置密码成功', ['code' => 1]);

    }


    /**
     * 发送短信验证码
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendCode()
    {
        $mobile = \YunShop::request()->mobile;
        $state = \YunShop::request()->state ?: '86';
        $sms_type = \YunShop::request()->sms_type;

        if (empty($mobile)) {
            return $this->errorJson('请填入手机号');
        }

        $sms = app('sms')->sendCode($mobile, $state);

        if (0 == $sms['status']) {
            return $this->errorJson($sms['json']);
        }

        return $this->successJson();
    }


    //修改密码
    public function updatePassword()
    {
        $result = $this->checkData();
        if ($result !== true) {
            return $this->errorJson($result);
        }

        $password = trim(\YunShop::request()->password);
        /*$old_password = trim(\YunShop::request()->old_password);*/

        $passwordService = new PasswordService();

        /*$result = $passwordService->check($old_password,$this->memberModel->yzMember->pay_password,$this->memberModel->yzMember->salt);
        if (!$result) {
            return $this->errorJson('原密码错误，请重试！');
        }*/

        //验证码验证
        $check_code = MemberService::checkCode();
        if ($check_code['status'] != 1) {
            return $this->errorJson($check_code['json']);
        }

        $password = $passwordService->make($password, $this->memberModel->yzMember->salt);
        $result = MemberShopInfo::where('member_id', \YunShop::app()->getMemberId())->update(['pay_password' => $password]);

        if (!$result) {
            return $this->errorJson('密码修改失败，请重试');
        }
        return $this->successJson('密码修改成功');
    }


    public function index()
    {
        if (!$this->getMemberModel()) {
            return $this->errorJson('未获取到会员信息');
        }
        if (!$this->memberModel->mobile) {
            return $this->errorJson('请先绑定手机号');
        }

        if (!$this->memberModel->yzMember->pay_password || $this->memberModel->yzMember->salt) {
            return $this->errorJson('请先设置密码', ['mobile' => $this->memberModel->mobile]);
        }


        return $this->successJson('ok');
    }


    private function checkData()
    {
        if (!$this->getMemberModel()) {
            return '未获取到会员信息';
        }

        $validator = \Validator::make(request()->all(), $this->rules(), $this->rulesMessage(), $this->attributes());
        if ($validator->fails()) {
            return $validator->messages()->first();
        }

        //验证码验证
        /*$check_code = MemberService::checkCode();
        if ($check_code['status'] != 1) {
            return $check_code['json'];
        }*/

        return true;
    }

    private function rules()
    {
        if ((new PasswordService())->multipleSwitch()) {
            return [
                'password'  => 'required|min:8|max:16',
                'confirmed' => 'required|same:password'
            ];
        }
        return [
            'password'  => 'required|min:6|max:6|regex:/^[0-9]*$/',
            'confirmed' => 'required|same:password'
        ];
    }

    private function rulesMessage()
    {
        if ((new PasswordService())->multipleSwitch()) {
            return [
                'required' => ':attribute 不能为空',
                'max'      => ':attribute 最多16位',
                'same'     => ':attribute 不匹配'
            ];
        }
        return [
            'regex'    => ':attribute 必须是纯数字',
            'required' => ':attribute 不能为空',
            'min'      => ':attribute 最少6位',
            'max'      => ':attribute 最多6位',
            'same'     => ':attribute 不匹配'
        ];
    }

    private function attributes()
    {
        return [
            'password' => '密码',
            'confirmed' => '确认密码',
        ];
    }


    private function getMemberModel()
    {
        return $this->memberModel = Member::select('uid', 'mobile')->with(['yzMember' => function ($query) {
            $query->select('member_id', 'pay_password', 'salt');
        }])->where('uid', \YunShop::app()->getMemberId())->first();
    }
}
