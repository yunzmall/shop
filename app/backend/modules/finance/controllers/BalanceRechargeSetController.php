<?php
/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 2022/2/18
 * Time: 9:41
 */

namespace app\backend\modules\finance\controllers;

use app\common\components\BaseController;
use app\common\facades\Setting;

class BalanceRechargeSetController extends BaseController
{
    public function index()
    {
        $data = [
            'set' => Setting::get('finance.balance_recharge_set') ? : $this->defaultSet()
        ];
        return view('finance.balance.recharge_set',$data)->render();
    }

    private function defaultSet()
    {
        return [
            'appoint_pay' => 0,
            'wechat' => 1,
            'wechat_limit' => '',
            'alipay' => 1,
            'alipay_limit' => '',
            'pay_wechat_hj' => 1,
            'pay_wechat_hj_limit' => '',
            'pay_alipay_hj' => 1,
            'pay_alipay_hj_limit' => '',
            'converge_quick_pay' => 1,
            'converge_quick_pay_limit' => '',
        ];
    }

    public function store()
    {
        $set = request()->set;
        $res = Setting::set('finance.balance_recharge_set',$set);
        if (!$res) {
            return $this->errorJson('保存失败');
        }
        return $this->successJson('保存成功');
    }
}