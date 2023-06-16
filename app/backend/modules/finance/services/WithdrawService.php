<?php
namespace app\backend\modules\finance\services;

use app\backend\modules\finance\services\BalanceService;
use app\common\exceptions\AppException;
use app\common\exceptions\ShopException;
use app\common\models\Member;
use app\common\services\credit\ConstService;
use app\common\services\finance\BalanceChange;
use app\common\services\finance\Withdraw;
use app\common\services\PayFactory;

/**
 * Created by PhpStorm.
 * Author:
 * Date: 2017/3/31
 * Time: 下午3:13
 */
class WithdrawService extends Withdraw
{
    public static function createStatusService($withdraw)
    {

        switch ($withdraw->status) {
            case -1:
                return '无效';
                break;
            case 0:
                return '未审核';
                break;
            case 1:
                return '未打款';
                break;
            case 2:
                return '已打款';
                break;
            case 3:
                return '驳回';
                break;

        }
    }

    public static function balanceWithdrawPay($withdraw, $remark)
    {
        $data = array(
           /* 'member_id' => $withdraw->member_id,
            'money' => $withdraw->actual_amounts,
            'serial_number' => '',
            'operator' => '-2',
            'operator_id' => $withdraw->id,
            'remark' => $remark,
            'service_type' => \app\common\models\finance\Balance::BALANCE_INCOME,*/

            'member_id'     => $withdraw->member_id,
            'remark'        => $remark,
            'source'        => ConstService::SOURCE_INCOME,
            'relation'      => '',
            'operator'      => ConstService::OPERATOR_MEMBER,
            'operator_id'   => $withdraw->id,
            'change_value'  => $withdraw->actual_amounts
        );
        return (new BalanceChange())->income($data);
    }

    public static function wechatWithdrawPay($withdraw, $remark)
    {
        $memberId = $withdraw->member_id;
        $sn = $withdraw->withdraw_sn;
        $amount = $withdraw->actual_amounts;

        $memberModel = Member::uniacid()->where('uid', $withdraw->member_id)->with(['hasOneFans', 'hasOneMiniApp', 'hasOneWechat'])->first();

        //优先使用微信会员打款
        if ($memberModel->hasOneFans->openid) {
            $result = PayFactory::create(PayFactory::PAY_WEACHAT)->doWithdraw($memberId, $sn, $amount, $remark);
            //微信会员openid不存在时，假设使用小程序会员openid
        } elseif (app('plugins')->isEnabled('min-app') && $memberModel->hasOneMiniApp->openid) {
            $result = PayFactory::create(PayFactory::PAY_WE_CHAT_APPLET)->doWithdraw($memberId, $sn, $amount, $remark);
        } elseif (app('plugins')->isEnabled('app-set') && $memberModel->hasOneWechat->openid) {
            $result = PayFactory::create(PayFactory::PAY_APP_WEACHAT)->doWithdraw($memberId, $sn, $amount, $remark);
        } else {
            throw new ShopException("余额提现ID：{$withdraw->id}，提现失败：提现会员openid错误");
        }
        return $result;
    }

    public static function alipayWithdrawPay($withdraw, $remark)
    {
        if (is_array($withdraw)) {
            $result = PayFactory::create(2)->doBatchWithdraw($withdraw);
        } else {
            $result = PayFactory::create(2)->doWithdraw($withdraw->member_id, $withdraw->withdraw_sn, $withdraw->actual_amounts, $remark);
        }

        if (is_array($result)) {
            return $result;
        }

        redirect($result)->send();
    }

    public static function eupWithdrawPay($withdraw)
    {
        return  PayFactory::create(PayFactory::PAY_EUP)->doWithdraw($withdraw->member_id, $withdraw->withdraw_sn, $withdraw->actual_amounts);
    }

    //blank 易宝余额提现
    public static function yopWithdrawPay($withdraw)
    {
        return  PayFactory::create(PayFactory::YOP)->doWithdraw($withdraw->member_id, $withdraw->withdraw_sn, $withdraw->actual_amounts);
    }

    public static function huanxunPayment($withdraw)
    {
        return  PayFactory::create(PayFactory::PAY_Huanxun_Quick)->doWithdraw($withdraw->member_id, $withdraw->withdraw_sn, $withdraw->actual_amounts);
    }

    /**
     * 汇聚余额提现
     *
     * @param $withdraw
     * @param $remark
     * @return array|mixed
     * @throws AppException
     */
    public static function convergePayMent($withdraw, $remark)
    {
        return  PayFactory::create(PayFactory::PAY_WECHAT_HJ)->doWithdraw($withdraw->member_id, $withdraw->withdraw_sn, $withdraw->actual_amounts, $remark);
    }

    public static function highLightWithdrawPay($withdraw)
    {
        return  PayFactory::create(PayFactory::HIGH_LIGHT)->doWithdraw($withdraw->member_id, $withdraw->withdraw_sn, $withdraw->actual_amounts,'',$withdraw->pay_way);
    }

    public static function workerWithdrawPay($withdraw)
    {
        return  PayFactory::create(PayFactory::WORK_WITHDRAW_PAY)->doWithdraw($withdraw->member_id, $withdraw->withdraw_sn, $withdraw->actual_amounts,'',$withdraw->pay_way);
    }

    public static function eplusWithdrawPay($withdraw)
    {
        return PayFactory::create(PayFactory::EPLUS_MINI_PAY)->doWithdraw($withdraw->member_id, $withdraw->withdraw_sn, $withdraw->actual_amounts, '', $withdraw->pay_way);
    }

    public static function silverPointWithdrawPayment($withdraw)
    {
        return PayFactory::create(PayFactory::SILVER_POINT_PAYMENT)->doWithdraw($withdraw->member_id, $withdraw->withdraw_sn, $withdraw->actual_amounts, '', $withdraw->pay_way);
    }

    public static function jianzhimaoBankPayment($withdraw)
    {
        return PayFactory::create(PayFactory::JIANZHIMAO_BANK)->doWithdraw($withdraw->member_id, $withdraw->withdraw_sn, $withdraw->actual_amounts, '', $withdraw->pay_way);
    }

    public static function taxWithdrawBankPayment($withdraw)
    {
        return PayFactory::create(PayFactory::TAX_WITHDRAW_BANK)->doWithdraw($withdraw->member_id, $withdraw->withdraw_sn, $withdraw->actual_amounts, '', $withdraw->pay_way);
    }
}