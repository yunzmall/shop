<?php
/**
 * Created by PhpStorm.
 *
 * User: king/QQ：995265288
 * Date: 2018/7/27 下午5:01
 * Email: livsyitian@163.com
 */

namespace app\backend\modules\withdraw\controllers;


use app\backend\models\Withdraw;
use app\common\exceptions\ShopException;
use app\common\facades\Setting;
use app\common\services\Session;
use app\common\services\withdraw\PayedService;

class AgainPayController extends PreController
{
    /**
     * 提现记录 打款中重新打款接口
     */
    public function index()
    {
        \Log::debug('打款中重新打款接口+++++++++++++++++++++');

        if (!$this->checkVerify()) {
            $this->message('提现验证失败或验证已过期', yzWebUrl("withdraw.records", ['id' => $resultData['id']]), 'error');
        }
        
        $this->withdrawModel->status = 1;

        $result = (new PayedService($this->withdrawModel))->withdrawPay();

        return $result == true ? $this->successJson('打款成功') : $this->errorJson('打款失败，请刷新重试');
    }


    public function validatorWithdrawModel($withdrawModel)
    {
        if ($withdrawModel->status != Withdraw::STATUS_PAYING) {
            throw new ShopException('状态错误，不符合打款规则！');
        }
    }

    /**
     * 打款验证
     * @return bool
     */
    private function checkVerify()
    {
        $set = Setting::getByGroup('pay_password')['withdraw_verify'] ?: [];
        if (empty($set) || empty($set['is_phone_verify'])) {
            return true;
        }
        $verify = Session::get('withdraw_verify');  //没获取到
        if ($verify && $verify >= time()) {
            return true;
        }
        return false;
    }
}
