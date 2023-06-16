<?php
/**
 * Created by PhpStorm.
 * User: king
 * Date: 2018/10/27
 * Time: 4:33 PM
 */

namespace app\backend\modules\withdraw\controllers;


use app\backend\models\Withdraw;
use app\backend\modules\income\models\Income;
use app\common\events\withdraw\WithdrawRebutAuditEvent;
use app\common\exceptions\ShopException;
use app\common\services\income\WithdrawIncomeApplyService;
use Illuminate\Support\Facades\DB;
use Yunshop\WithdrawalLimit\Common\services\WithdrawHandleService;

class AuditedRebutController extends PreController
{
    /**
     * 提现记录 审核后驳回接口
     */
    public function index()
    {
        $result = $this->auditedRebut();

        return $result == true ? $this->successJson('驳回成功') : $this->errorJson('驳回失败，请刷新重试');
    }

    public function validatorWithdrawModel($withdrawModel)
    {
        if ($withdrawModel->status != Withdraw::STATUS_AUDIT) {
            throw new ShopException('状态错误，不符合审核后驳回规则！');
        }
    }

    /**
     * @return bool
     */
    private function auditedRebut()
    {
        DB::transaction(function () {
            $this->_auditedRebut();
        });
        return true;
    }

    /**
     * @throws ShopException
     */
    private function _auditedRebut()
    {
        $result = $this->updateWithdrawStatus();
        if (!$result) {
            throw new ShopException('驳回失败：更新状态失败');
        }
        $result = $this->updateIncomePayStatus();
        if (!$result) {
            throw new ShopException('驳回失败：更新收入失败');
        }
        WithdrawIncomeApplyService::rebut($this->withdrawModel);
    }

    /**
     * @return bool
     */
    private function updateWithdrawStatus()
    {
        $this->withdrawModel->status = Withdraw::STATUS_REBUT;
        $this->withdrawModel->arrival_at = time();
        $this->withdrawModel->reject_reason = request()->reject_reason ? : '';

        return $this->withdrawModel->save();
    }

    /**
     * @return bool
     */
    private function updateIncomePayStatus()
    {
        $income_ids = explode(',', $this->withdrawModel->type_id);

        if (count($income_ids) > 0) {
            //提现额度
            if(app('plugins')->isEnabled('withdrawal-limit'))
            {
                WithdrawHandleService::handle('reject',$income_ids,$this->withdrawModel);
            }

            //后台审核执行驳回事件
            event(new WithdrawRebutAuditEvent($this->withdrawModel,$income_ids));

            return Income::whereIn('id', $income_ids)->where('pay_status', Income::PAY_STATUS_WAIT)->update(['status' => Income::STATUS_INITIAL, 'pay_status' => Income::PAY_STATUS_REJECT]);
        }
        return false;
    }
}
