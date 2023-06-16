<?php
/**
 * Created by PhpStorm.
 *
 * User: king/QQ：995265288
 * Date: 2018/6/12 下午4:30
 * Email: livsyitian@163.com
 */

namespace app\backend\modules\withdraw\controllers;


use app\backend\models\Withdraw;
use app\common\exceptions\ShopException;
use app\common\services\withdraw\PayedService;

class PayController extends PreController
{
    /**
     * 提现记录打款接口
     */
    public function index()
    {
        \Log::debug('提现记录打款接口++++++++++++++++++++');

        $result = (new PayedService($this->withdrawModel))->withdrawPay();

        return $result == true ? $this->successJson('打款成功') : $this->errorJson('打款失败，请刷新重试');
    }


    public function validatorWithdrawModel($withdrawModel)
    {
        if ($withdrawModel->status != Withdraw::STATUS_AUDIT) {
            throw new ShopException('状态错误，不符合打款规则！');
        }
    }






}
