<?php
/**
 * Created by PhpStorm.
 * Author: 芸众商城 www.yunzshop.com
 * Date: 2017/3/24
 * Time: 下午12:42
 */

namespace app\common\services;

use app\backend\modules\member\models\MemberRelation;
use app\common\models\finance\Balance;
use app\common\models\PayOrder;
use app\common\models\PayRefundOrder;
use app\common\services\finance\BalanceChange;
use app\frontend\modules\finance\services\BalanceService;
use app\common\models\OrderPay;
use app\common\models\PayType;
use app\common\services\credit\ConstService;

class CreditPay extends Pay
{

    public function doPay($params = [])
    {
        $operation = '余额订单支付 订单号：' . $params['order_no'];
        $this->log($params['extra']['type'], '余额', $params['amount'], $operation, $params['order_no'], Pay::ORDER_STATUS_NON, $params['member_id']);

        self::payRequestDataLog($params['order_no'], $params['extra']['type'], '余额', json_encode($params));

        //切换新余额接口，原接口废弃
        $data = [
            'member_id' => $params['member_id'],
            'remark' => $params['remark'] ?: '',
            'relation' => $params['order_no'],
            'operator' => $params['operator'] ?: 0,
            'operator_id' => $params['operator_id'] ?: 0,
            'change_value' => $params['amount']
        ];
        $result = (new BalanceChange())->consume($data);

        if ($result === true) {
            $pay_order_model = PayOrder::uniacid()->where('out_order_no', $params['order_no'])->first();

            if ($pay_order_model) {
                $pay_order_model->status = 2;
                $pay_order_model->trade_no = $params['trade_no'];
                $pay_order_model->third_type = '余额';
                $pay_order_model->save();
            }

            return true;
        } else {
            return false;
        }


    }

	public function doRefund($out_trade_no, $totalmoney, $refundmoney)
	{

		$order_pay = OrderPay::select(['id','uid'])
			->where('pay_sn', $out_trade_no)
			->first();
		$pay_uid = $order_pay->uid;
		$return_rd = $this->createOrderRD();

		//退款记录
		PayRefundOrder::create([
			'uniacid' => \YunShop::app()->uniacid,
			'member_id' => 0,
			'int_order_no' => '',
			'out_order_no' => $out_trade_no,
			'status' => 2,
			'type' => '余额',
			'trade_no' => 0,
			'price' => $refundmoney
		]);
		$data = [
			'member_id' => $pay_uid,
			'remark' => '余额订单退款 订单号:(' . $out_trade_no . ')退款单号：(' . $return_rd . ')退款金额：' . $refundmoney,
			'source' => ConstService::SOURCE_CANCEL_CONSUME,
			'relation' => $return_rd,
			'operator' => ConstService::OPERATOR_ORDER,
			'operator_id' => $pay_uid,
			'change_value' => $refundmoney
		];
		//判断是否代付
		if ($order_pay->behalfPay) {
			$data['member_id'] = $order_pay->behalfPay->behalf_id;
			$data['remark'] .= "（代付退款）";
		}
		return $result = (new BalanceChange())->cancelConsume($data);
	}


    /**
     * 生成唯一单号
     * @return string
     */
    public function createOrderRD()
    {
        $ordersn = createNo('RD', true);
        while (1) {
            if (!Balance::ofOrderSn($ordersn)->first()) {
                break;
            }
            $ordersn = createNo('RD', true);
        }
        return $ordersn;
    }

    public function doWithdraw($member_id, $out_trade_no, $money, $desc, $type = 1)
    {
        if($money <= 0) return ['errno' => 1,'message' => '提现金额不能小于1元'];
        switch ($type){
            case 1:
                $type = '供应商';
                break;
            default:
                $type = '';
                break;
        }

        $data = array(
            'member_id' => $member_id,
            'remark' => $type . '余额提现: uid 为 ' . $member_id . ' 的用户, 获得余额 ' . $money . '元',
            'source' => ConstService::SOURCE_INCOME,
            'relation' => '',
            'operator' => ConstService::OPERATOR_SHOP,
            'operator_id' => $member_id,
            'change_value' => $money,
        );
        $op = "{$type}余额提现 订单号：{$out_trade_no}，提现金额：{$money}";

        try {
            (new BalanceChange())->income($data);
            $this->withdrawlog(Pay::PAY_TYPE_WITHDRAW,$type . '余额提现',$money,$op,$out_trade_no,Pay::ORDER_STATUS_COMPLETE,$member_id);
        } catch (\Exception $e) {
            return ['errno' => 1,'message' => $e->getMessage()];
        }

        return ['errno' => 0,'message' => '余额提现成功'];
    }

    public function buildRequestSign()
    {
        // TODO: Implement buildRequestSign() method.
    }
}