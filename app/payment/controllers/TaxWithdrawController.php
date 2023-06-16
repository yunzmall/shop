<?php

namespace app\payment\controllers;

use app\common\models\PayWithdrawOrder;
use app\common\models\Withdraw;
use app\payment\PaymentController;
use Yunshop\TaxWithdraw\models\WithdrawRelation;
use Yunshop\TaxWithdraw\services\TaxService;

class TaxWithdrawController extends PaymentController
{
    public function backNotify()
    {
        $request = request()->all();
        \Log::debug('税筹提现打款回调信息打印', $request);
        \YunShop::app()->uniacid = \Setting::$uniqueAccountId = request()->input('i');

        $taxService = new TaxService;
        $data = $taxService->decrypt($request['encrypt']);

        \Log::debug('税筹提现打款回调解密数据', $data);
        $data = json_decode($data, true);

        $withdraw = Withdraw::where('withdraw_sn', $data['merOrderNo'])->first();
        $withdrawRelation = WithdrawRelation::where('withdraw_id', $withdraw['id'])->first();

        if ($withdrawRelation && $data['orderStatus'] == '03') {
            $pay_refund_model = PayWithdrawOrder::getOrderInfo($withdrawRelation->withdraw->withdraw_sn);

            if ($pay_refund_model) {
                $pay_refund_model->status = 2;
                $pay_refund_model->trade_no = $withdrawRelation->withdraw->withdraw_sn;
                $pay_refund_model->save();

                \app\common\services\finance\Withdraw::paySuccess($withdrawRelation->withdraw->withdraw_sn);
                \Log::debug('税筹提现-银行卡提现', 'withdraw.succeeded');
            }
            echo "success";

        } else {

            $pay_refund_model = PayWithdrawOrder::getOrderInfo($withdrawRelation->withdraw->withdraw_sn);
            if ($pay_refund_model) {
                \Log::debug('税筹提现-银行卡提现', 'withdraw.failed');
                \app\common\services\finance\Withdraw::payFail($withdrawRelation->withdraw->withdraw_sn);
                $withdrawRelation->update(['status' => -1]);
            }
            echo "fail";
        }
    }
}
