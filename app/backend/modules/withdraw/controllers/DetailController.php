<?php
/**
 * Created by PhpStorm.
 *
 * User: king/QQ：995265288
 * Date: 2018/7/27 上午11:14
 * Email: livsyitian@163.com
 */

namespace app\backend\modules\withdraw\controllers;


use app\common\facades\Setting;
use app\common\services\Session;
use app\common\models\WithdrawMergeServicetaxRate;
use app\common\services\finance\Withdraw;


class DetailController extends PreController
{
    /**
     * 提现记录详情 接口
     *
     * @throws \Throwable
     */
    public function index()
    {
        $result_data = $this->resultData();
        $withdraw_data = $result_data['item'];

        if ($withdraw_data->status == 0) {  //为审核时，如果是合并提现，修改劳务费比例
            $withdraw_set = \Setting::get('withdraw.income');
            if ($withdraw_data->pay_way == 'balance' && $withdraw_set['balance_special']) {
                $merge_percent = null;
            } else {
                $merge_percent = WithdrawMergeServicetaxRate::uniacid()->where('withdraw_id', $withdraw_data->id)->where('is_disabled', 0)->first();
            }
            if ($merge_percent) {
                $withdraw_data->servicetax_rate = $merge_percent->servicetax_rate;
                $base_amount = !$withdraw_set['service_tax_calculation'] ? bcsub($withdraw_data->amounts, $withdraw_data->poundage, 2) : $withdraw_data->amounts;
                $withdraw_data->servicetax = bcmul($base_amount, bcdiv($withdraw_data->servicetax_rate, 100, 4), 2);
            } elseif ($withdraw_data->pay_way != 'balance' || !$withdraw_set['balance_special']) {
                    $base_amount = !$withdraw_set['service_tax_calculation'] ? bcsub($withdraw_data->amounts, $withdraw_data->poundage, 2) : $withdraw_data->amounts;
                    $res = Withdraw::getWithdrawServicetaxPercent($base_amount);
                    $withdraw_data->servicetax_rate = $res['servicetax_percent'];
                    $withdraw_data->servicetax = $res['servicetax_amount'];
            }

            $withdraw_data->actual_amounts = bcsub(bcsub($withdraw_data->amounts, $withdraw_data->poundage, 2), $withdraw_data->servicetax, 2);
            $result_data['item'] = $withdraw_data;
        }

        return view('withdraw.detail', $result_data);
    }


    public function validatorWithdrawModel($withdrawModel)
    {
    }

    private function resultData()
    {
        $set = Setting::getByGroup('pay_password') ?: [];
        return [
            'item' => $this->withdrawModel,
            'is_verify' => !empty($set['withdraw_verify']['is_phone_verify']) ? true : false,
            'expire_time' => Session::get('withdraw_verify') ?: null,
            'verify_phone' => $set['withdraw_verify']['phone'] ?: "",
            'verify_expire' => $set['withdraw_verify']['verify_expire'] ? intval($set['withdraw_verify']['verify_expire']) : 10
        ];
    }


}
