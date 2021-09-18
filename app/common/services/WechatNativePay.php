<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/12/30
 * Time: 17:42
 */

namespace app\common\services;

use app\common\exceptions\AppException;
use app\common\helpers\Url;
use app\common\facades\EasyWeChat;


class WechatNativePay extends Pay
{
	protected $notify_url;


	protected $paySet;

	/**
	 * WechatH5Pay constructor.
	 * @throws AppException
	 */
	public function __construct()
	{
		$this->notify_url = Url::shopSchemeUrl('payment/wechat/notifyPc.php');

		$this->paySet = \Setting::get('shop.pay');

		if (empty($this->paySet['weixin_mchid']) || empty($this->paySet['weixin_apisecret']) || empty($this->paySet['weixin_appid']) || empty($this->paySet['weixin_secret'])) {

			throw new AppException('没有设定支付参数');
		}
	}


	/**
	 * 创建支付对象
	 *
	 * @param $pay
	 * @return \EasyWeChat\Payment\Payment
	 */
	public function getEasyWeChatApp($pay, $notify_url)
	{
		$options = [
			'app_id'  => $pay['weixin_appid'],
			'secret'  => $pay['weixin_secret'],
			// payment
			'payment' => [
				'merchant_id'        => $pay['weixin_mchid'],
				'key'                => $pay['weixin_apisecret'],
				'cert_path'          => $pay['weixin_cert'],
				'key_path'           => $pay['weixin_key'],
				'notify_url'         => $notify_url
			]
		];
		$app = EasyWeChat::payment($options);
		return $app;
	}

	/**
	 * @param $data
	 * @return mixed|void
	 */
	public function doPay($data)
	{
		$op = '微信扫码支付-订单号：' . $data['order_no'];
		$pay_order_model = $this->log($data['extra']['type'], '微信扫码支付', $data['amount'], $op, $data['order_no'], Pay::ORDER_STATUS_NON, \YunShop::app()->getMemberId());

		if (empty(\YunShop::app()->getMemberId())) {
			throw new AppException('无法获取用户ID');
		}


		//$this->setParameter('appid', $this->paySet['weixin_appid']);
		//$this->setParameter('mch_id', $this->paySet['weixin_mchid']);
		$this->setParameter('sign_type', 'MD5');
		$this->setParameter('trade_type',  'NATIVE'); //NATIVE -Native支付
		$this->setParameter('product_id', $data['order_no']);//trade_type=NATIVE时，此参数必传。此参数为二维码中包含的商品ID，商户自行定义。
		$this->setParameter('nonce_str', str_random(16));
		$this->setParameter('body', mb_substr($data['subject'], 0, 120));
		$this->setParameter('attach', \YunShop::app()->uniacid);
		$this->setParameter('out_trade_no',  $data['order_no']);
		$this->setParameter('total_fee',  $data['amount'] * 100);  // 单位：分
		$this->setParameter('spbill_create_ip',  self::getClientIP());
		$this->setParameter('notify_url',  $this->notify_url);

		// $this->setParameter('scene_info',  json_encode($this->getSceneInfo(), JSON_UNESCAPED_UNICODE));



		//请求数据日志
		self::payRequestDataLog($data['order_no'], $pay_order_model->type,
			$pay_order_model->third_type, $this->getAllParameters());



		$payment = $this->getEasyWeChatApp($this->paySet, $this->notify_url);



		$result = $payment->order->unify($this->getAllParameters());
		\Log::debug('预下单', $result);

		if ($result['return_code'] == 'SUCCESS' && $result['result_code'] == 'SUCCESS'){

			//code_ur 此url用于生成支付二维码，然后提供给用户进行扫码支付,有效期为2小时
			return ['code_url'=>$result['code_url']];

		} elseif ($result['return_code'] == 'SUCCESS') {
			throw new AppException($result['err_code_des']);
		} else {
			throw new AppException($result['return_msg']);
		}

		return false;
	}

	public function getSceneInfo()
	{
		$a = [
			'store_info' => [
				'id' => '0',//门店id
				'name' => '商城', //门店名称
				'area_code' => '商城', //门店行政区划码
				'address' => '商城', //门店详细地址
			],
		];

		return $a;
	}

	/**
	 * @param 订单号 $out_trade_no
	 * @param 订单总金额 $totalmoney
	 * @param 退款金额 $refundmoney
	 * @return mixed|void
	 */
	public function doRefund($out_trade_no, $totalmoney, $refundmoney)
	{

		$out_refund_no = $this->setUniacidNo(\YunShop::app()->uniacid);
		$op = '微信退款 订单号：' . $out_trade_no . '退款单号：' . $out_refund_no . '退款金额：' . $refundmoney;
		if (empty($out_trade_no)) {
			throw new AppException('参数错误');
		}

		$pay_order_model = $this->refundlog(Pay::PAY_TYPE_REFUND, '微信扫码支付退款', $refundmoney, $op, $out_trade_no, Pay::ORDER_STATUS_NON, 0);



		$payment = $this->getEasyWeChatApp($this->paySet, $this->notify_url);
		try {
			$result = $payment->refund->byOutTradeNumber($out_trade_no, $out_refund_no, $totalmoney * 100, $refundmoney * 100);
		} catch (\Exception $e) {
			throw new AppException('微信接口错误:' . $e->getMessage());
		}

		$this->payResponseDataLog($out_trade_no, '微信扫码支付退款', json_encode($result));
		$status = $this->queryRefund($payment, $out_trade_no);
		\Log::debug('---微信扫码支付退款状态---'.$status, $result);

		if ($status == 'PROCESSING' || $status == 'SUCCESS') {
			$this->changeOrderStatus($pay_order_model, Pay::ORDER_STATUS_COMPLETE, $result->transaction_id);
			return true;
		} else {
			throw new AppException('微信接口错误:'.$result->return_msg . '-' . $result->err_code_des . '/' . $status);
		}
	}

	private function changeOrderStatus($model, $status, $trade_no)
	{
		$model->status = $status;
		$model->trade_no = $trade_no;
		$model->save();
	}

	/**
	 * 订单退款查询
	 * @param $payment
	 * @param $out_trade_no
	 * @return mixed
	 */
	public function queryRefund($payment, $out_trade_no)
	{
		$result = $payment->refund->queryByOutTradeNumber($out_trade_no);

		foreach ($result as $key => $value) {
			if (preg_match('/refund_status_\d+/', $key)) {
				return $value;
			}
		}

		return 'fail';
	}

	/**
	 * @param 提现者用户ID $member_id
	 * @param 提现批次单号 $out_trade_no
	 * @param 提现金额 $money
	 * @param 提现说明 $desc
	 * @param 只针对微信 $type
	 * @return mixed|void
	 */
	public function doWithdraw($member_id, $out_trade_no, $money, $desc, $type)
	{

	}

	public function buildRequestSign()
	{

	}
}