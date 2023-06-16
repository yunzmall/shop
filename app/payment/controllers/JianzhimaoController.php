<?php

namespace app\payment\controllers;

use app\common\models\Member;
use app\common\models\PayWithdrawOrder;
use app\payment\PaymentController;
use Yunshop\JianzhimaoWithdraw\models\ElectronicSign;
use Yunshop\JianzhimaoWithdraw\models\WithdrawRelation;
use Yunshop\JianzhimaoWithdraw\services\JianzhimaoService;

class JianzhimaoController extends PaymentController
{
    // 签约回调
    public function silentSignUrl()
    {
        \Log::debug('兼职猫回调信息', request()->all());
        \YunShop::app()->uniacid = \Setting::$uniqueAccountId = request()->input('i');
        $data = request()->only(['extraMsg', 'phone', 'name', 'idNo', 'cardNo', 'signDate', 'eProtocol', 'sign', 'signDate']);

        // 参数为空的话, 把参数补上
        if (!$data['cardNo']) {
            $data['cardNo'] = 'null';
        }

        $service = new JianzhimaoService;
        $res = $service->verifySign($data);

        if (!$res) {
            \Log::debug('兼职猫签约回调签名验证失败', $data);
            echo 'fail';
            exit();
        }

        $extraMsg = json_decode(base64_decode($data['extraMsg']), true);

        if (!$extraMsg) {
            \Log::debug('兼职猫签约回调透传参数解析失败', $data);
            echo 'fail';
            exit();
        }

        $member = Member::where('mobile', $data['phone'])->first();

        if (!$member) {
            \Log::debug('兼职猫签约回调会员不存在', $data);
            echo 'fail';
            exit();
        }

        $electronicSign = ElectronicSign::where('member_id', $member['uid'])->first();

        if (!$electronicSign) {
            \Log::debug('兼职猫签约回调电签记录不存在', $data);
            echo 'fail';
            exit();
        }

        $electronicSign->status = 2;
        $electronicSign->msg = '签约成功';
        $electronicSign->save();
        echo 'ok';
        exit();
    }

    // 提现回调
    public function payBillUrl()
    {
        \Log::debug('兼职猫回调信息', request()->all());


        $data = request()->only(['result', 'msg', 'batchNo', 'name', 'idNo', 'cardNo', 'preTaxMoney', 'afterTaxMoney', 'taxMoney', 'extraMoney', 'status', 'completeTime', 'remark', 'sign']);

        // 参数为空的话, 把参数补上
        if (!$data['cardNo']) {
            $data['cardNo'] = 'null';
        }

        $service = new JianzhimaoService;
        $res = $service->verifySign($data);

        if (!$res) {
            \Log::debug('兼职猫提现回调签名验证失败', $data);
            echo 'fail';
            exit();
        }

        $withdrawRelation = WithdrawRelation::where('batch_no', $data['batchNo'])->first();

        if ($withdrawRelation && $data['result'] == 1) {
            $pay_refund_model = PayWithdrawOrder::getOrderInfo($withdrawRelation->withdraw->withdraw_sn);

            if ($pay_refund_model) {
                $pay_refund_model->status = 2;
                $pay_refund_model->trade_no = $withdrawRelation->withdraw->withdraw_sn;
                $pay_refund_model->save();

                \app\common\services\finance\Withdraw::paySuccess($withdrawRelation->withdraw->withdraw_sn);
                \Log::debug('兼职猫-银行卡提现', 'withdraw.succeeded');
            }
            echo "ok";

        } else {

            $pay_refund_model = PayWithdrawOrder::getOrderInfo($withdrawRelation->withdraw->withdraw_sn);
            if ($pay_refund_model) {
                \Log::debug('兼职猫-银行卡提现', 'withdraw.failed');
                \app\common\services\finance\Withdraw::payFail($withdrawRelation->withdraw->withdraw_sn);
                $withdrawRelation->update(['status' => -1]);
            }
            echo "fail";
        }
    }
}
