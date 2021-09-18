<?php
/**
 * Created by PhpStorm.
 * User: shenyang
 * Date: 2017/9/22
 * Time: 下午3:12
 */

namespace app\frontend\modules\payment\controllers;


use app\common\components\ApiController;
use app\common\services\password\PasswordService;

class PasswordController extends ApiController
{
    public function check()
    {
        if ($this->masterSwitch()) $this->_check();


        return $this->successJson('成功', []);
    }

    public function multiple()
    {
        return $this->successJson('', (new PasswordService())->multipleSwitch());
    }

    private function _check()
    {
        $this->validate(['password' => 'required|string']);

        (new PasswordService())->checkPayPassword($this->memberId(), $this->password());
    }

    private function masterSwitch()
    {
        return (new PasswordService())->masterSwitch();
    }

    private function memberId()
    {
        return \YunShop::app()->getMemberId();
    }

    private function password()
    {
        return request()->input('password');
    }
}