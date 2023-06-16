<?php
/**
 * Created by PhpStorm.
 *
 * 
 *
 * Date: 2022/7/4
 * Time: 16:12
 */

namespace app\payment\controllers;

use app\common\models\PayOrder;
use app\common\models\PayType;
use app\common\models\PayWithdrawOrder;
use app\common\models\Withdraw;
use app\payment\PaymentController;
use Yunshop\SilverPointPay\models\ProfitShare;
use Yunshop\SilverPointPay\models\WithdrawRelation;
use Yunshop\SilverPointPay\services\SilverPointPay;
use app\common\helpers\Url;

class SilverPointPayController extends PaymentController
{
    protected $uniacid;

    public function notifyUrlPay()
    {
        $this->debugMsg('接受银典异步回调');

        $result = $this->getNotifyRequest();


        //增加是否是代付验证，因为银典逻辑问题，此处做兼容
        if ($result['type_num'] == 1108) {
            return $this->withdraw($result);
        }

        if ($result['status']) {
            $res = $this->processOrder($result);
            if ($res) {
                return $this->successReply();
            }
        }
    }

    /**
     * 1108"说明是代付订单，目前只有余额提现、收入提现使用代付打款
     *
     * 银典支付-代付逻辑，如果配置了回调地址，优先使用配置回调地址，所以不能直接使用withdrawNotifyUrl
     *
     * 这个改用 notifyUrlPay 方法，根据 txnNum == 1108 验证是银典支付-代付打款逻辑（提现打款）
     */
    public function withdraw($params)
    {
        $this->debugMsg('接受银典支付-代付异步回调');

        if ($params['status'] == 1) {
            $pay_refund_model = PayWithdrawOrder::getOrderInfo($params['out_order_no']);

            if ($pay_refund_model) {
                $pay_refund_model->status = 2;
                $pay_refund_model->trade_no = $params['out_order_no'];
                $pay_refund_model->save();

                \app\common\services\finance\Withdraw::paySuccess($params['out_order_no']);

                return $this->successReply();
            }

            \Log::debug('银典支付-代付', 'withdraw.succeeded');
        } else {
            $pay_refund_model = PayWithdrawOrder::getOrderInfo($params['out_order_no']);

            if ($pay_refund_model) {
                \Log::debug('银典支付-代付', 'withdraw.failed');

                \app\common\services\finance\Withdraw::payFail($params['out_order_no']);

                $withdrawId = Withdraw::where('withdraw_sn', $params['out_order_no'])->first()->id;

                WithdrawRelation::where('withdraw_id', $withdrawId)->update(['status' => -1]);
            }
        }
    }


    public function frontUrlUnionPay()
    {
        $request = request()->all();
        $order_info = unserialize($request['afford']);
        $this->uniacid = $order_info['i'];
        \YunShop::app()->uniacid = $this->uniacid;
        \Setting::$uniqueAccountId = $this->uniacid;

        $silverPointPay = new SilverPointPay();
        $respData = $silverPointPay->queryTradeNo($order_info['order_no']);

        $effective = false;

        if ($respData["respCode"] == '0000' && $respData["transStatus"] == '100') {
            $effective = true;
        }

        if ($effective) {
            redirect(Url::absoluteApp('member/payYes', ['i' => $order_info['i']]))->send();
        } else {
            redirect(Url::absoluteApp('member/payErr', ['i' => $order_info['i']]))->send();
        }
    }

    public function notifySplitBill()
    {
        $this->debugMsg('接受银典分账异步回调');
        $msg = isset($GLOBALS['HTTP_RAW_POST_DATA']) ? $GLOBALS['HTTP_RAW_POST_DATA'] : file_get_contents(
            "php://input"
        );
        if (empty($msg)) {
            # 如果没有数据，直接返回失败
            $this->debugMsg('未接收到数据');
            return [
                'status' => 0,
                'msg'    => '未接收到数据',
            ];
        }

        $this->uniacid = request()->input('i');

        if (!$this->uniacid) {
            $this->debugMsg('未获取到公众号参数');
            return [
                'status' => 0,
                'msg'    => '未获取到公众号参数',
            ];
        }

        $this->debugMsg('解密前报文', $msg);

        \YunShop::app()->uniacid = $this->uniacid;
        \Setting::$uniqueAccountId = $this->uniacid;

        $silverPointPay = new SilverPointPay();
        $reqStr = $silverPointPay->decrypt($msg);

        $this->debugMsg('解密后报文', $reqStr);

        $responseData = json_decode($reqStr, true);

        if (!$responseData) {
            $this->debugMsg('解密后报文为空');
            return [
                'status' => 0,
                'msg'    => '解密后报文为空',
            ];
        }

        $profitShare = ProfitShare::where('profit_share_sn', $responseData['outOrderId'])->first();

        if ($responseData['transStatus'] == '100') {
            $this->debugMsg('银典数据-分账成功', $responseData);
            $profitShare->status = ProfitShare::SPLIT_SUCCESS;
            $profitShare->receive_at = time(); // 到账时间
        } elseif ($responseData['transStatus'] == '101') {
            $this->debugMsg('银典数据-待支付', $responseData);
            $profitShare->status = ProfitShare::UN_SPLIT;
        } elseif ($responseData['transStatus'] == '102') {
            $this->debugMsg('银典数据-支付失败', $responseData);
            $profitShare->status = ProfitShare::UN_SPLIT;
        } elseif ($responseData['transStatus'] == '103') {
            $this->debugMsg('银典数据-订单处理中', $responseData);
            $profitShare->status = ProfitShare::SPLITTING;
        } elseif ($responseData['transStatus'] == '104') {
            $this->debugMsg('银典数据-已撤销', $responseData);
            $profitShare->status = ProfitShare::SPLIT_FAIL;
        } else {
            $profitShare->trans_status = $responseData['transStatus'];
        }

        $profitShare->save();

        return $this->successReply();
    }

    protected function processOrder($data)
    {
        $payOrder = PayOrder::where('out_order_no', $data['out_order_no'])->first();
        if (!$payOrder) {
            $this->debugMsg('未找到支付订单', $data);
            return false;
        }

        $currentPayType = $this->currentPayType($payOrder->type);
        $setData = $this->setData(
            $payOrder['out_order_no'],
            $data['out_order_no'],
            bcmul($data['amount'], 100, 0),
            $currentPayType['id'],
            $currentPayType['name']
        );
        $this->payResutl($setData);
        $this->debugMsg('订单支付成功--订单号', $data['out_order_no']);

        return true;
    }

    protected function getNotifyRequest()
    {
        $msg = isset($GLOBALS['HTTP_RAW_POST_DATA']) ? $GLOBALS['HTTP_RAW_POST_DATA'] : file_get_contents(
            "php://input"
        );
        if (empty($msg)) {
            # 如果没有数据，直接返回失败
            $this->debugMsg('未接收到数据');
            return [
                'status' => 0,
                'msg'    => '未接收到数据',
            ];
        }

        $this->uniacid = request()->input('i');

        if (!$this->uniacid) {
            $this->debugMsg('未获取到公众号参数');
            return [
                'status' => 0,
                'msg'    => '未获取到公众号参数',
            ];
        }

        $this->debugMsg('解密前报文', $msg);

        \YunShop::app()->uniacid = $this->uniacid;
        \Setting::$uniqueAccountId = $this->uniacid;

        $silverPointPay = new SilverPointPay();
        $reqStr = $silverPointPay->decrypt($msg);

        $this->debugMsg('解密后报文', $reqStr);

        $respData = json_decode($reqStr, true);

        if ($respData['transStatus'] == '100') {
            return [
                'status'       => 1,
                'out_order_no' => $respData["outOrderId"],  // 外部订单号
                'order_no'     => $respData["orderCd"],  // 支付订单号
                'amount'       => $respData["transAmt"],  // 交易金额
                'type_num'     => $respData['txnNum'] //"txnNum":"1108"说明是代付订单
            ];
        } else {
            $this->debugMsg('回调失败', $reqStr);

            return [
                'status'   => 0,
                'msg'      => $reqStr,
                'type_num' => $respData['txnNum'] //"txnNum":"1108"说明是代付订单
            ];
        }
    }

    protected function successReply()
    {
        return json_encode([
            'respCode' => '0000',
            'respMsg'  => '成功'
        ]);
    }

    protected function debugMsg($title, $data = [])
    {
        \Log::debug(self::class . ' ' . $title . ': ' . json_encode($data));
    }

    protected function currentPayType($payId)
    {
        return PayType::find($payId);
    }

    /**
     * 支付回调参数
     *
     * @param $order_no
     * @param $parameter
     * @return array
     */
    public function setData($order_no, $trade_no, $total_fee, $pay_type_id, $pay_type)
    {
        return [
            'total_fee'    => $total_fee,
            'out_trade_no' => $order_no,
            'trade_no'     => $trade_no,
            'unit'         => 'fen',
            'pay_type'     => $pay_type,
            'pay_type_id'  => $pay_type_id,
        ];
    }
}