<?php
/**
 * Created by PhpStorm.
 *
 * User: king/QQ：995265288
 * Date: 2018/7/27 上午11:14
 * Email: livsyitian@163.com
 */

namespace app\backend\modules\withdraw\controllers;


use app\backend\models\Withdraw;
use app\common\components\BaseController;
use app\common\facades\Setting;
use app\common\models\Income;
use app\common\services\Session;
use app\common\models\WithdrawMergeServicetaxRate;


class DetailController extends BaseController
{
    /**
     * @var Withdraw
     */
    private $withdrawModel;

    /**
     * 提现记录详情 接口
     *
     * @throws \Throwable
     */
    public function index()
    {
        if (!$this->withdrawModel = $this->withdrawModel()) return $this->errorJson();

        return request()->ajax() ? $this->jsonData() : view('withdraw.detail', $this->resultData());
    }

    private function jsonData()
    {
        return $this->successJson('ok', $this->resultData());
    }

    private function withdrawModel()
    {
        return Withdraw::with([
            'member',
            'bankCard',
            'hasOneYzMember'
        ])->find($this->recordId());
    }

    /**
     * @return int
     */
    private function recordId()
    {
        return request()->input('id');
    }

    private function incomeModels()
    {
        $incomeModels = Income::getIncomeByIds($this->withdrawModel->type_id)
            ->select('id','member_id','dividend_code','incometable_type','incometable_id','type_name','amount',
                'status','pay_status','created_at','order_sn','detail')
            ->with(['hasManyOrder'=>function($order) {
                $order->select('id','order_sn','status','refund_id')
                ->with(['hasOneRefundApply'=>function($refundApply) {
                    $refundApply->select('id','status');
                }]);
            }])->get();

        //按照前段要求更改数据格式
        $incomeModels->map(function ($incomeModel) {
            if ($incomeModel->detail) {

                $detail = json_decode($incomeModel->detail, 1);

                if ($incomeModel->hasManyOrder && isset($detail['order'])) {
                    $detail['order']['data'][] = [
                        'title' => '订单状态',
                        'value' => $incomeModel->hasManyOrder->status_name
                    ];
                    if ($incomeModel->hasManyOrder->hasOneRefundApply) {
                        $detail['order']['data'][] = [
                            'title' => '售后状态',
                            'value' => $incomeModel->hasManyOrder->hasOneRefundApply->status_name
                        ];
                    }
                }

                $incomeModel->detail = collect($detail)->values()->toJson();
            }
        });
        return $incomeModels;
    }

    private function resultData()
    {
        $result_data = $this->_resultData();

        if ($this->withdrawModel->status == 0) {  //为审核时，如果是合并提现，修改劳务费比例
            $withdraw_set = \Setting::get('withdraw.income');
            if ($this->withdrawModel->pay_way == 'balance' && $withdraw_set['balance_special']) {
                $merge_percent = null;
            } else {
                $merge_percent = WithdrawMergeServicetaxRate::uniacid()->where('withdraw_id', $this->withdrawModel->id)->where('is_disabled', 0)->first();
            }
            if ($merge_percent) {
                $this->withdrawModel->servicetax_rate = $merge_percent->servicetax_rate;
                $base_amount = !$withdraw_set['service_tax_calculation'] ? bcsub($this->withdrawModel->amounts, $this->withdrawModel->poundage, 2) : $this->withdrawModel->amounts;
                $this->withdrawModel->servicetax = bcmul($base_amount, bcdiv($this->withdrawModel->servicetax_rate, 100, 4), 2);
            } elseif ($this->withdrawModel->pay_way != 'balance' || !$withdraw_set['balance_special']) {
                $base_amount = !$withdraw_set['service_tax_calculation'] ? bcsub($this->withdrawModel->amounts, $this->withdrawModel->poundage, 2) : $this->withdrawModel->amounts;
                $res = \app\common\services\finance\Withdraw::getWithdrawServicetaxPercent($base_amount,$this->withdrawModel);
                $this->withdrawModel->servicetax_rate = $res['servicetax_percent'];
                $this->withdrawModel->servicetax = $res['servicetax_amount'];
            }

            $this->withdrawModel->actual_amounts = bcsub(bcsub($this->withdrawModel->amounts, $this->withdrawModel->poundage, 2), $this->withdrawModel->servicetax, 2);
        }

        return $result_data;
    }

    private function _resultData()
    {
        $set = Setting::getByGroup('pay_password') ?: [];
        $incomeList = $this->incomeModels();

        $this->withdrawModel->member->level_name = '';
        if ($this->withdrawModel->member) {
            $this->withdrawModel->member->level_name = $this->withdrawModel->member->levelName();
        }

        return [
            'withdraw'      => $this->withdrawModel,
            'income_list'   => $incomeList,
            'income_total'  => $incomeList->count(),
            'is_verify'     => !empty($set['withdraw_verify']['is_phone_verify']) ? true : false,
            'expire_time'   => Session::get('withdraw_verify') ?: null,
            'verify_phone'  => $set['withdraw_verify']['phone'] ?: "",
            'verify_expire' => $set['withdraw_verify']['verify_expire'] ? intval($set['withdraw_verify']['verify_expire']) : 10
        ];
    }
}
