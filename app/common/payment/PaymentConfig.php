<?php
/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 2021/8/27
 * Time: 16:59
 */

namespace app\common\payment;


use app\common\payment\method\alipay\AlipayAppPayment;
use app\common\payment\method\alipay\AlipayFacePayment;
use app\common\payment\method\alipay\AlipayJsapiPayment;
use app\common\payment\method\alipay\AlipayPayment;
use app\common\payment\method\alipay\AlipayPeriodDeductPayment;
use app\common\payment\method\alipay\AlipayScanPayment;
use app\common\payment\method\converge\ConvergeAlipayCardPayment;
use app\common\payment\method\converge\ConvergeAlipayH5Payment;
use app\common\payment\method\converge\ConvergeAlipayPayment;
use app\common\payment\method\converge\ConvergeQuickPayment;
use app\common\payment\method\converge\ConvergeSeparateAlipayPayment;
use app\common\payment\method\converge\ConvergeSeparatePayment;
use app\common\payment\method\converge\ConvergeWechatCardPayment;
use app\common\payment\method\converge\ConvergeUnionPayPayment;
use app\common\payment\method\converge\ConvergeWechatPayment;
use app\common\payment\method\other\AnotherPayment;
use app\common\payment\method\other\AuthPayPayment;
use app\common\payment\method\other\BalancePayment;
use app\common\payment\method\other\CashPayment;
use app\common\payment\method\other\CODPayment;
use app\common\payment\method\other\ConfirmPayment;
use app\common\payment\method\other\DcmScanPayment;
use app\common\payment\method\other\DianBangScanPayment;
use app\common\payment\method\other\EplusAliPayPayment;
use app\common\payment\method\other\EplusMiniPayPayment;
use app\common\payment\method\other\EplusWechatPayPayment;
use app\common\payment\method\other\HkScanAlipayPayment;
use app\common\payment\method\other\HkScanPayment;
use app\common\payment\method\other\JinepayPayment;
use app\common\payment\method\other\JueQiPayment;
use app\common\payment\method\other\LaKaLaAlipayPayment;
use app\common\payment\method\other\LaKaLaWechatPayment;
use app\common\payment\method\other\LeshuaAlipayPayment;
use app\common\payment\method\other\LeshuaPosPayment;
use app\common\payment\method\other\LeshuaWechatPayment;
use app\common\payment\method\other\LSPPayment;
use app\common\payment\method\other\LSPWalletPayment;
use app\common\payment\method\other\MemberCardPayment;
use app\common\payment\method\other\ParentPayment;
use app\common\payment\method\other\PayPalPayment;
use app\common\payment\method\other\RemittancePayment;
use app\common\payment\method\other\SandpayAlipayPayment;
use app\common\payment\method\other\SandpayWechatPayment;
use app\common\payment\method\other\SilverPointPayAlipayPayment;
use app\common\payment\method\other\SilverPointPayUnionPayPayment;
use app\common\payment\method\other\SilverPointPayWechatPayment;
use app\common\payment\method\other\StoreBalancePayment;
use app\common\payment\method\other\StorePayment;
use app\common\payment\method\other\ThirdPartyMiniPayment;
use app\common\payment\method\other\TouTiaoAlipayPayment;
use app\common\payment\method\other\TouTiaoWechatPayment;
use app\common\payment\method\other\XfAlipayPayment;
use app\common\payment\method\other\XfWechatPayment;
use app\common\payment\method\other\YopAlipayPayment;
use app\common\payment\method\other\YopWechatPayment;
use app\common\payment\method\wechat\WechatAppPayment;
use app\common\payment\method\wechat\WechatCpsAppPayment;
use app\common\payment\method\wechat\WechatFacePayment;
use app\common\payment\method\wechat\WechatH5Payment;
use app\common\payment\method\wechat\WechatJsapiPayment;
use app\common\payment\method\wechat\WechatMicroPayment;
use app\common\payment\method\wechat\WechatMinPayment;
use app\common\payment\method\wechat\WechatNativePayment;
use app\common\payment\method\wechat\WechatPayment;
use app\common\payment\method\wechat\WechatScanPayment;
use app\frontend\modules\order\payment\types\OrderPaymentTypes;
use app\common\payment\method\other\CodeSciencePayYuPayment;


class PaymentConfig
{
	public static $orderPaymentTypes = [];

	public static function get()
	{
		return [
			AlipayAppPayment::class,
			AlipayFacePayment::class,
			AlipayJsapiPayment::class,
			AlipayPayment::class,
			AlipayScanPayment::class,
			ConvergeAlipayPayment::class,
			ConvergeQuickPayment::class,
			ConvergeSeparateAlipayPayment::class,
			ConvergeSeparatePayment::class,
			ConvergeWechatPayment::class,
            ConvergeAlipayCardPayment::class,
            ConvergeWechatCardPayment::class,
            ConvergeUnionPayPayment::class,
            ConvergeAlipayH5Payment::class,
			WechatAppPayment::class,
			WechatCpsAppPayment::class,
			WechatFacePayment::class,
			WechatH5Payment::class,
			WechatJsapiPayment::class,
			WechatMinPayment::class,
			WechatNativePayment::class,
			WechatPayment::class,
			WechatScanPayment::class,
            WechatMicroPayment::class,
			AnotherPayment::class,
			BalancePayment::class,
			CashPayment::class,
			CODPayment::class,
			ConfirmPayment::class,
			DcmScanPayment::class,
			DianBangScanPayment::class,
			HkScanAlipayPayment::class,
			HkScanPayment::class,
			JueQiPayment::class,
			MemberCardPayment::class,
			PayPalPayment::class,
			ParentPayment::class,
			RemittancePayment::class,
			StorePayment::class,
			TouTiaoAlipayPayment::class,
			TouTiaoWechatPayment::class,
			YopAlipayPayment::class,
			YopWechatPayment::class,
			StoreBalancePayment::class,
			XfAlipayPayment::class,
            XfWechatPayment::class,
            SandpayAlipayPayment::class,
            SandpayWechatPayment::class,
            LaKaLaWechatPayment::class,
            LaKaLaAlipayPayment::class,
            LeshuaAlipayPayment::class,
            LeshuaWechatPayment::class,
            LeshuaPosPayment::class,
            LSPPayment::class,
            SilverPointPayAlipayPayment::class,
            SilverPointPayUnionPayPayment::class,
            SilverPointPayWechatPayment::class,
            CodeSciencePayYuPayment::class,
            EplusWechatPayPayment::class,
            EplusMiniPayPayment::class,
            EplusAliPayPayment::class,
            LSPWalletPayment::class,
            JinepayPayment::class,
            AuthPayPayment::class,
            ThirdPartyMiniPayment::class,
            AlipayPeriodDeductPayment::class,
        ];
	}

	public static function getOrderPayment($order)
	{
        if (app()->bound('OrderPaymentTypes')) {
            return app('OrderPaymentTypes',['order'=>$order]);
        }
		return static::$orderPaymentTypes[$order->plugin_id]?:OrderPaymentTypes::class;
	}

	public static function attachOrderPayment($order_plugin_id,$order_payment_types)
	{
		static::$orderPaymentTypes[$order_plugin_id] = $order_payment_types;
	}


}