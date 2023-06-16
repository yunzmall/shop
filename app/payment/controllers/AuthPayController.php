<?php
/**
 * Created by PhpStorm.
 * 
 *
 *
 * Date: 2022/12/27
 * Time: 11:04
 */

namespace app\payment\controllers;

use app\common\facades\Setting;
use app\common\models\PayOrder;
use app\common\models\PayType;
use app\payment\PaymentController;
use Yunshop\SubAuthPayment\models\SubOrder;
use Yunshop\SubAuthPayment\services\ManageService;

class AuthPayController extends PaymentController
{
    protected $jsonData;

    public function notifyUrl()
    {
        $this->validator();

        if (!$this->jsonData['data']['uniacid']) {
            return $this->errorJson('缺少必要参数: uniacid');
        }

        $this->setUniacid($this->jsonData['data']['uniacid']);

        $set = Setting::get('sub-auth-payment.set');

        if ($set['appid'] !== $this->jsonData['appid']) {
            return $this->errorJson('商户不存在');
        }

        $manageService = new ManageService;
        $result = $manageService->verify($this->jsonData, $set['secret']);

        $payOrder = PayOrder::where('out_order_no', $this->jsonData['data']['pay_sn'])->first();

        if (!$payOrder) {
            \Log::debug(self::class . '--: 未找到支付订单');
            return $this->errorJson('未找到支付订单');
        }

        $subOrder = SubOrder::firstOrNew([
            'uniacid' => $this->jsonData['data']['uniacid'],
            'pay_sn' => $this->jsonData['data']['pay_sn'],
        ]);

        $subOrder->member_id = $payOrder->member_id;
        $subOrder->amount = $this->jsonData['data']['amount'];
        $subOrder->type = 1;

        if ($result) {

            $currentPayType = $this->currentPayType(105);
            $payResultData = $this->setData($this->jsonData['data']['pay_sn'], $this->jsonData['data']['pay_sn'], ($this->jsonData['data']['amount'] * 100), $currentPayType['id'], $currentPayType['name']);
            $this->payResutl($payResultData);
            \Log::debug(self::class . '订单支付成功--订单号: ' . $this->jsonData['data']['pay_sn']);
            $subOrder->save();
            return $this->successJson();
        }

        return $this->errorJson();
    }

    protected function validator()
    {
        if (!request()->isMethod('post')) {
            return $this->errorJson('请求方式错误');
        }

        $jsonData = request()->json()->all();

        \Log::debug(self::class . '--: 微信借权支付回调通知: ' . json_encode($jsonData));

        if (!$jsonData) {
            return $this->errorJson('参数异常');
        }

        if (!$jsonData['appid'] || !$jsonData['data'] || !$jsonData['sign']) {
            return $this->errorJson('请确认必填参数');
        }

        $this->jsonData = $jsonData;
    }

    protected function setUniacid($uniacid)
    {
        \YunShop::app()->uniacid = $uniacid;
        Setting::$uniqueAccountId = $uniacid;
    }

    private function currentPayType($payId)
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
            'total_fee' => $total_fee,
            'out_trade_no' => $order_no,
            'trade_no' => $trade_no,
            'unit' => 'fen',
            'pay_type' => $pay_type,
            'pay_type_id' => $pay_type_id,
        ];
    }
}
