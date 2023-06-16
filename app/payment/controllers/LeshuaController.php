<?php
/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 2022/3/30
 * Time: 17:58
 */
namespace app\payment\controllers;

use app\common\models\PayOrder;
use app\common\models\PayType;
use app\payment\PaymentController;
use Yunshop\LeshuaPay\models\MerchantOpen;
use Yunshop\LeshuaPay\models\SplitApply;
use Yunshop\LeshuaPay\models\XmlToArray;
use Yunshop\LeshuaPay\services\LeshuaPay;

class LeshuaController extends PaymentController
{
    const LESHUA_ALIPAY = 85;
    const LESHUA_WECHAT = 86;
    const LESHUA_POS = 87;

    /**
     * @return null
     */
    public function notifyUrlAlipay()
    {
        return $this->processOrder(self::LESHUA_ALIPAY);
    }

    public function notifyUrlWechat()
    {
        return $this->processOrder(self::LESHUA_WECHAT);
    }

    public function notifyUrlPos()
    {
        return $this->processOrder(self::LESHUA_POS);
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

    protected function processOrder($type)
    {
        $xml = request()->getContent();
        $data = XmlToArray::convert($xml, true);
        \Log::debug(self::class. '--: 乐刷数据: ' . json_encode($data));

        $sign = $data['sign'];
        $data['attach'] = empty($data['attach']) ? "" : $data['attach'];
        $data['goods_tag'] = empty($data['goods_tag']) ? "" : $data['goods_tag'];

        unset($data['error_code']);
        unset($data['sign']);
        unset($data['@root']);

        $payOrder = PayOrder::where('out_order_no', $data['third_order_id'])->first();

        if (!$payOrder) {
            \Log::debug(self::class. '--: 未找到支付订单');
            return;
        }

        \YunShop::app()->uniacid = $payOrder->uniacid;
        \Setting::$uniqueAccountId = $payOrder->uniacid;
        $leshua = new LeshuaPay;
        $res = $leshua->verifySign($data, $sign);
        if (!$res) {
            \Log::debug(self::class . '验证签名失败: ');
            // 触发自主查询订单.
            $queryRes = $leshua->queryOrder($data['third_order_id']);
            if ($queryRes['resp_code'] === '0' && $queryRes['result_code'] === '0' && $queryRes['status'] == 2) {
                \Log::debug(self::class . '自主查询订单: 支付成功');
            } else {
                \Log::debug(self::class . '自主查询订单: 支付失败 ' . json_encode($queryRes));
                return;
            }
        }

        $currentPayType = $this->currentPayType($type);
        $data = $this->setData($data['third_order_id'], $data['third_order_id'], $data['amount'], $currentPayType['id'], $currentPayType['name']);
        $this->payResutl($data);
        \Log::debug(self::class . '订单支付成功--订单号: ' . $data['third_order_id']);
        echo '000000';
    }

    public function currentPayType($payId)
    {
        return PayType::find($payId);
    }

    // 分账商户回调
    public function splitApply()
    {
        \Log::debug(self::class. '--: 分账商户回调: ' . json_encode(request()->all()));

        $splitApply = SplitApply::where('merchant', request()->input('merchantId'))->first();

        if (!$splitApply) {
            \Log::debug(self::class. '--: 未找到分账商户');
            return;
        }

        $splitApply->status = request()->input('status');

        $merchantOpen = MerchantOpen::firstOrNew([
            'uniacid' => $splitApply->uniacid,
            'is_store' => 1,
            'store_id' => $splitApply->store_id,
            'merchant' => $splitApply->merchant,
        ]);

        $merchantOpen->status = request()->input('status');

        $merchantOpen->save();
        $splitApply->save();
    }
}