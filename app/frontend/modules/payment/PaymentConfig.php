<?php
/**
 * Created by PhpStorm.
 * User: shenyang
 * Date: 2017/11/7
 * Time: 下午5:59
 */

namespace app\frontend\modules\payment;

use app\common\models\PayType;
use app\common\models\OrderPay;
use app\frontend\modules\payment\managers\OrderPaymentTypeSettingManager;
use app\frontend\modules\payment\orderPayments\AlipayFacePayment;
use app\frontend\modules\payment\orderPayments\AlipayJsapiPayment;
use app\frontend\modules\payment\orderPayments\AlipayToutiaoPayment;
use app\frontend\modules\payment\orderPayments\AlipayScanPayHjment;
use app\frontend\modules\payment\orderPayments\AlipayScanPayment;
use app\frontend\modules\payment\orderPayments\AnotherPayment;
use app\frontend\modules\payment\orderPayments\AppPayment;
use app\frontend\modules\payment\orderPayments\CloudAliPayment;
use app\frontend\modules\payment\orderPayments\CloudPayment;
use app\frontend\modules\payment\orderPayments\ConfirmPayPayment;
use app\frontend\modules\payment\orderPayments\ConvergenceSeparateAliPayment;
use app\frontend\modules\payment\orderPayments\ConvergenceSeparatePayment;
use app\frontend\modules\payment\orderPayments\ConvergeQuickPayPayment;
use app\frontend\modules\payment\orderPayments\DcmScanPayPayment;
use app\frontend\modules\payment\orderPayments\HkScanAliPayment;
use app\frontend\modules\payment\orderPayments\HkScanPayment;
use app\frontend\modules\payment\orderPayments\JueqiPayment;
use app\frontend\modules\payment\orderPayments\DepositPayment;
use app\frontend\modules\payment\orderPayments\HuanxunPayment;
use app\frontend\modules\payment\orderPayments\CODPayment;
use app\frontend\modules\payment\orderPayments\CreditPayment;
use app\frontend\modules\payment\orderPayments\LcgBalancePayment;
use app\frontend\modules\payment\orderPayments\LcgBankCardPayment;
use app\frontend\modules\payment\orderPayments\MemberCardPayment;
use app\frontend\modules\payment\orderPayments\ParentPayment;
use app\frontend\modules\payment\orderPayments\PayPalPayment;
use app\frontend\modules\payment\orderPayments\RemittancePayment;
use app\frontend\modules\payment\orderPayments\StoreBalancePayment;
use app\frontend\modules\payment\orderPayments\UsdtPayment;
use app\frontend\modules\payment\orderPayments\AlipayPayHjment;
use app\frontend\modules\payment\orderPayments\WechatCpsAppPayPayment;
use app\frontend\modules\payment\orderPayments\WechatFacePayment;
use app\frontend\modules\payment\orderPayments\WechatH5Payment;
use app\frontend\modules\payment\orderPayments\WechatJsapiPayment;
use app\frontend\modules\payment\orderPayments\WechatNativePayment;
use app\frontend\modules\payment\orderPayments\WechatToutiaoPayment;
use app\frontend\modules\payment\orderPayments\WechatPayHjment;
use app\frontend\modules\payment\orderPayments\WebPayment;
use app\frontend\modules\payment\orderPayments\WechatScanPayHjment;
use app\frontend\modules\payment\orderPayments\WechatScanPayment;
use app\frontend\modules\payment\orderPayments\WftAlipayPayment;
use app\frontend\modules\payment\orderPayments\YopAlipayPayment;
use app\frontend\modules\payment\orderPayments\YopPayment;
use app\frontend\modules\payment\orderPayments\YopProAlipayPayment;
use app\frontend\modules\payment\orderPayments\YopProWechatPayment;
use app\frontend\modules\payment\orderPayments\YunAliPayment;
use app\frontend\modules\payment\orderPayments\YunPayment;
use app\frontend\modules\payment\orderPayments\WftPayment;
use app\frontend\modules\payment\orderPayments\DianBangScanPayment;
use app\frontend\modules\payment\paymentSettings\OrderPaymentSettingCollection;
use app\frontend\modules\payment\paymentSettings\shop\AlipayAppSetting;
use app\frontend\modules\payment\paymentSettings\shop\AlipayFacePaySetting;
use app\frontend\modules\payment\paymentSettings\shop\AlipayJsapiPaySetting;
use app\frontend\modules\payment\paymentSettings\shop\AlipayToutiaoSetting;
use app\frontend\modules\payment\paymentSettings\shop\AlipayScanPayHjSetting;
use app\frontend\modules\payment\paymentSettings\shop\AlipayScanPaySetting;
use app\frontend\modules\payment\paymentSettings\shop\AlipaySetting;
use app\frontend\modules\payment\paymentSettings\shop\AnotherPaySetting;
use app\frontend\modules\payment\paymentSettings\shop\BalanceSetting;
use app\frontend\modules\payment\paymentSettings\shop\CloudPayAliSetting;
use app\frontend\modules\payment\paymentSettings\shop\CloudPayWechatSetting;
use app\frontend\modules\payment\paymentSettings\shop\ConfirmPaySetting;
use app\frontend\modules\payment\paymentSettings\shop\ConvergenceSeparateSetting;
use app\frontend\modules\payment\paymentSettings\shop\ConvergeQuickPaySetting;
use app\frontend\modules\payment\paymentSettings\shop\DcmScanPaySetting;
use app\frontend\modules\payment\paymentSettings\shop\HkScanAliPaySetting;
use app\frontend\modules\payment\paymentSettings\shop\HkScanPaySetting;
use app\frontend\modules\payment\paymentSettings\shop\JueqiPayWechatSetting;
use app\frontend\modules\payment\paymentSettings\shop\DepositSetting;
use app\frontend\modules\payment\paymentSettings\shop\HuanxunPaySetting;
use app\frontend\modules\payment\paymentSettings\shop\CODSetting;
use app\frontend\modules\payment\paymentSettings\shop\LcgBalanceSetting;
use app\frontend\modules\payment\paymentSettings\shop\LcgBankCardSetting;
use app\frontend\modules\payment\paymentSettings\shop\MemberCardSetting;
use app\frontend\modules\payment\paymentSettings\shop\ParentPaymentSetting;
use app\frontend\modules\payment\paymentSettings\shop\PayPalSetting;
use app\frontend\modules\payment\paymentSettings\shop\RemittanceSetting;
use app\frontend\modules\payment\paymentSettings\shop\StoreBalanceSetting;
use app\frontend\modules\payment\paymentSettings\shop\UsdtPaySetting;
use app\frontend\modules\payment\paymentSettings\shop\WechatCpsAppPaySetting;
use app\frontend\modules\payment\paymentSettings\shop\WechatFacePaySetting;
use app\frontend\modules\payment\paymentSettings\shop\WechatH5Setting;
use app\frontend\modules\payment\paymentSettings\shop\WechatJsapiPaySetting;
use app\frontend\modules\payment\paymentSettings\shop\WechatNativeSetting;
use app\frontend\modules\payment\paymentSettings\shop\WechatToutiaoSetting;
use app\frontend\modules\payment\paymentSettings\shop\WechatPayHjSetting;
use app\frontend\modules\payment\paymentSettings\shop\AlipayPayHjSetting;
use app\frontend\modules\payment\paymentSettings\shop\WechatAppPaySetting;
use app\frontend\modules\payment\paymentSettings\shop\WechatPaySetting;
use app\frontend\modules\payment\paymentSettings\shop\WechatScanPayHjSetting;
use app\frontend\modules\payment\paymentSettings\shop\WechatScanPaySetting;
use app\frontend\modules\payment\paymentSettings\shop\WftAlipaySetting;
use app\frontend\modules\payment\paymentSettings\shop\YopAlipaySetting;
use app\frontend\modules\payment\paymentSettings\shop\YopProAlipaySetting;
use app\frontend\modules\payment\paymentSettings\shop\YopProWechatSetting;
use app\frontend\modules\payment\paymentSettings\shop\YopSetting;
use app\frontend\modules\payment\paymentSettings\shop\YunPayAliSetting;
use app\frontend\modules\payment\paymentSettings\shop\YunPayWechatSetting;
use app\frontend\modules\payment\paymentSettings\shop\WftSetting;
use app\frontend\modules\payment\paymentSettings\shop\DianBangScanSetting;
use app\frontend\modules\payment\orderPayments\XfpayAlipayPayment;
use app\frontend\modules\payment\paymentSettings\shop\XfpayAlipaySetting;
use app\frontend\modules\payment\orderPayments\XfpayWechatPayment;
use app\frontend\modules\payment\paymentSettings\shop\XfpayWechatSetting;


class PaymentConfig
{
    static function get()
    {
        return [
            'balance'                => [
                'payment'  => function (OrderPay $orderPay, PayType $payType, OrderPaymentSettingCollection $settings) {
                    return new CreditPayment($orderPay, $payType, $settings);
                },
                'settings' => [
                    'shop' => function (OrderPay $orderPay) {
                        return new BalanceSetting($orderPay);
                    }
                ]
            ],
            'parentPayment'          => [
                'payment'  => function (OrderPay $orderPay, PayType $payType, OrderPaymentSettingCollection $settings) {
                    return new ParentPayment($orderPay, $payType, $settings);
                },
                'settings' => [
                    'shop' => function (OrderPay $orderPay) {
                        return new ParentPaymentSetting($orderPay);
                    }
                ]
            ],
            'alipay'                 => [
                'payment'  => function (OrderPay $orderPay, PayType $payType, OrderPaymentSettingCollection $settings) {
                    return new WebPayment($orderPay, $payType, $settings);
                },
                'settings' => [
                    'shop' => function (OrderPay $orderPay) {
                        return new AlipaySetting($orderPay);
                    }
                ]
            ],
            'wechatPay'              => [
                'payment'  => function (OrderPay $orderPay, PayType $payType, OrderPaymentSettingCollection $settings) {
                    return new WebPayment($orderPay, $payType, $settings);
                },
                'settings' => [
                    'shop' => function (OrderPay $orderPay) {
                        return new WechatPaySetting($orderPay);
                    }
                ]
            ],
            'jueqi-pay'              => [
                'payment'  => function (OrderPay $orderPay, PayType $payType, OrderPaymentSettingCollection $settings) {
                    return new JueqiPayment($orderPay, $payType, $settings);
                },
                'settings' => [
                    'shop' => function (OrderPay $orderPay) {
                        return new JueqiPayWechatSetting($orderPay);
                    }
                ]
            ],
            'alipayApp'              => [
                'payment'  => function (OrderPay $orderPay, PayType $payType, OrderPaymentSettingCollection $settings) {
                    return new AppPayment($orderPay, $payType, $settings);
                },
                'settings' => [
                    'shop' => function (OrderPay $orderPay) {
                        return new AlipayAppSetting($orderPay);
                    }
                ]
            ],
            'cloudPayWechat'         => [
                'payment'  => function (OrderPay $orderPay, PayType $payType, OrderPaymentSettingCollection $settings) {
                    return new CloudPayment($orderPay, $payType, $settings);
                },
                'settings' => [
                    'shop' => function (OrderPay $orderPay) {
                        return new CloudPayWechatSetting($orderPay);
                    }
                ]
            ],
            'wechatApp'              => [
                'payment'  => function (OrderPay $orderPay, PayType $payType, OrderPaymentSettingCollection $settings) {

                    return new AppPayment($orderPay, $payType, $settings);
                },
                'settings' => [
                    'shop' => function (OrderPay $orderPay) {
                        return new WechatAppPaySetting($orderPay);
                    }
                ]
            ],
            'yunPayWechat'           => [
                'payment'  => function (OrderPay $orderPay, PayType $payType, OrderPaymentSettingCollection $settings) {
                    return new YunPayment($orderPay, $payType, $settings);
                },
                'settings' => [
                    'shop' => function (OrderPay $orderPay) {
                        return new YunPayWechatSetting($orderPay);
                    }
                ]
            ],
            'cloudPayAlipay'         => [
                'payment'  => function (OrderPay $orderPay, PayType $payType, OrderPaymentSettingCollection $settings) {
                    return new CloudAliPayment($orderPay, $payType, $settings);
                },
                'settings' => [
                    'shop' => function (OrderPay $orderPay) {
                        return new CloudPayAliSetting($orderPay);
                    }
                ]
            ],
            'anotherPay'             => [
                'payment'  => function (OrderPay $orderPay, PayType $payType, OrderPaymentSettingCollection $settings) {
                    return new AnotherPayment($orderPay, $payType, $settings);
                },
                'settings' => [
                    'shop' => function (OrderPay $orderPay) {
                        return new AnotherPaySetting($orderPay);
                    }
                ]
            ],
            'yunPayAlipay'           => [
                'payment'  => function (OrderPay $orderPay, PayType $payType, OrderPaymentSettingCollection $settings) {
                    return new YunAliPayment($orderPay, $payType, $settings);
                },
                'settings' => [
                    'shop' => function (OrderPay $orderPay) {
                        return new YunPayAliSetting($orderPay);
                    }
                ],
            ],
            'huanxunQuick'           => [
                'payment'  => function (OrderPay $orderPay, PayType $payType, OrderPaymentSettingCollection $settings) {
                    return new HuanxunPayment($orderPay, $payType, $settings);
                },
                'settings' => [
                    'shop' => function (OrderPay $orderPay) {
                        return new HuanxunPaySetting($orderPay);
                    }
                ]
            ],
            'COD'                    => [
                'payment'  => function (OrderPay $orderPay, PayType $payType, OrderPaymentSettingCollection $settings) {
                    return new CODPayment($orderPay, $payType, $settings);
                },
                'settings' => [
                    'shop' => function (OrderPay $orderPay) {
                        return new CODSetting($orderPay);
                    }
                ]
            ],
            'remittance'             => [
                'payment'  => function (OrderPay $orderPay, PayType $payType, OrderPaymentSettingCollection $settings) {
                    return new RemittancePayment($orderPay, $payType, $settings);
                },
                'settings' => [
                    'shop' => function (OrderPay $orderPay) {
                        return new RemittanceSetting($orderPay);
                    }
                ],
            ],
            'wftPay'                 => [
                'payment'  => function (OrderPay $orderPay, PayType $payType, OrderPaymentSettingCollection $settings) {
                    return new WftPayment($orderPay, $payType, $settings);
                },
                'settings' => [
                    'shop' => function (OrderPay $orderPay) {
                        return new WftSetting($orderPay);
                    }
                ]
            ],
            'wftAlipay'              => [
                'payment'  => function (OrderPay $orderPay, PayType $payType, OrderPaymentSettingCollection $settings) {
                    return new WftAlipayPayment($orderPay, $payType, $settings);
                },
                'settings' => [
                    'shop' => function (OrderPay $orderPay) {
                        return new WftAlipaySetting($orderPay);
                    }
                ]
            ],
            'DianBangScan'           => [
                'payment'  => function (OrderPay $orderPay, PayType $payType, OrderPaymentSettingCollection $settings) {
                    return new DianBangScanPayment($orderPay, $payType, $settings);
                },
                'settings' => [
                    'shop' => function (OrderPay $orderPay) {
                        return new DianBangScanSetting($orderPay);
                    }
                ]
            ],
            'yop'                    => [
                'payment'  => function (OrderPay $orderPay, PayType $payType, OrderPaymentSettingCollection $settings) {
                    return new YopPayment($orderPay, $payType, $settings);
                },
                'settings' => [
                    'shop' => function (OrderPay $orderPay) {
                        return new YopSetting($orderPay);
                    }
                ]
            ],
            'yopAlipay'              => [
                'payment'  => function (OrderPay $orderPay, PayType $payType, OrderPaymentSettingCollection $settings) {
                    return new YopAlipayPayment($orderPay, $payType, $settings);
                },
                'settings' => [
                    'shop' => function (OrderPay $orderPay) {
                        return new YopAlipaySetting($orderPay);
                    }
                ]
            ],
            'UsdtPay'                => [
                'payment'  => function (OrderPay $orderPay, PayType $payType, OrderPaymentSettingCollection $settings) {
                    return new UsdtPayment($orderPay, $payType, $settings);
                },
                'settings' => [
                    'shop' => function (OrderPay $orderPay) {
                        return new UsdtPaySetting($orderPay);
                    }
                ]
            ],
            'convergePaySeparate'    => [
                'payment'  => function (OrderPay $orderPay, PayType $payType, OrderPaymentSettingCollection $settings) {
                    return new ConvergenceSeparatePayment($orderPay, $payType, $settings);
                },
                'settings' => [
                    'shop' => function (OrderPay $orderPay) {
                        return new ConvergenceSeparateSetting($orderPay);
                    }
                ]
            ],
            'convergeAliPaySeparate' => [
                'payment'  => function (OrderPay $orderPay, PayType $payType, OrderPaymentSettingCollection $settings) {
                    return new ConvergenceSeparateAliPayment($orderPay, $payType, $settings);
                },
                'settings' => [
                    'shop' => function (OrderPay $orderPay) {
                        return new ConvergenceSeparateSetting($orderPay);
                    }
                ]
            ],
            'convergePayWechat'      => [
                'payment'  => function (OrderPay $orderPay, PayType $payType, OrderPaymentSettingCollection $settings) {
                    return new WechatPayHjment($orderPay, $payType, $settings);
                },
                'settings' => [
                    'shop' => function (OrderPay $orderPay) {
                        return new WechatPayHjSetting($orderPay);
                    }
                ]
            ],
            'convergePayAlipay'      => [
                'payment'  => function (OrderPay $orderPay, PayType $payType, OrderPaymentSettingCollection $settings) {
                    return new AlipayPayHjment($orderPay, $payType, $settings);
                },
                'settings' => [
                    'shop' => function (OrderPay $orderPay) {
                        return new AlipayPayHjSetting($orderPay);
                    }
                ]
            ],
            'convergePayAlipayScan'  => [
                'payment'  => function (OrderPay $orderPay, PayType $payType, OrderPaymentSettingCollection $settings) {
                    return new AlipayScanPayHjment($orderPay, $payType, $settings);
                },
                'settings' => [
                    'shop' => function (OrderPay $orderPay) {
                        return new AlipayScanPayHjSetting($orderPay);
                    }
                ]
            ],
            'convergePayWechatScan'  => [
                'payment'  => function (OrderPay $orderPay, PayType $payType, OrderPaymentSettingCollection $settings) {
                    return new WechatScanPayHjment($orderPay, $payType, $settings);
                },
                'settings' => [
                    'shop' => function (OrderPay $orderPay) {
                        return new WechatScanPayHjSetting($orderPay);
                    }
                ]
            ],
            'DepositPay'             => [
                'payment'  => function (OrderPay $orderPay, PayType $payType, OrderPaymentSettingCollection $settings) {
                    return new DepositPayment($orderPay, $payType, $settings);
                },
                'settings' => [
                    'shop' => function (OrderPay $orderPay) {
                        return new DepositSetting($orderPay);
                    }
                ]
            ],
            'lcgBalance'             => [
                'payment'  => function (OrderPay $orderPay, PayType $payType, OrderPaymentSettingCollection $settings) {
                    return new LcgBalancePayment($orderPay, $payType, $settings);
                },
                'settings' => [
                    'shop' => function (OrderPay $orderPay) {
                        return new LcgBalanceSetting($orderPay);
                    }
                ]
            ],
            'lcgBankCard'            => [
                'payment'  => function (OrderPay $orderPay, PayType $payType, OrderPaymentSettingCollection $settings) {
                    return new LcgBankCardPayment($orderPay, $payType, $settings);
                },
                'settings' => [
                    'shop' => function (OrderPay $orderPay) {
                        return new LcgBankCardSetting($orderPay);
                    }
                ]
            ],
            'WechatScan'             => [
                'payment'  => function (OrderPay $orderPay, PayType $payType, OrderPaymentSettingCollection $settings) {
                    return new WechatScanPayment($orderPay, $payType, $settings);
                },
                'settings' => [
                    'shop' => function (OrderPay $orderPay) {
                        return new WechatScanPaySetting($orderPay);
                    }
                ]
            ],
            'WechatFace'             => [
                'payment'  => function (OrderPay $orderPay, PayType $payType, OrderPaymentSettingCollection $settings) {
                    return new WechatFacePayment($orderPay, $payType, $settings);
                },
                'settings' => [
                    'shop' => function (OrderPay $orderPay) {
                        return new WechatFacePaySetting($orderPay);
                    }
                ]
            ],
            'WechatJsapi'            => [
                'payment'  => function (OrderPay $orderPay, PayType $payType, OrderPaymentSettingCollection $settings) {
                    return new WechatJsapiPayment($orderPay, $payType, $settings);
                },
                'settings' => [
                    'shop' => function (OrderPay $orderPay) {
                        return new WechatJsapiPaySetting($orderPay);
                    }
                ]
            ],
            'AlipayJsapi'            => [
                'payment'  => function (OrderPay $orderPay, PayType $payType, OrderPaymentSettingCollection $settings) {
                    return new AlipayJsapiPayment($orderPay, $payType, $settings);
                },
                'settings' => [
                    'shop' => function (OrderPay $orderPay) {
                        return new AlipayJsapiPaySetting($orderPay);
                    }
                ]
            ],
            'AlipayScan'             => [
                'payment'  => function (OrderPay $orderPay, PayType $payType, OrderPaymentSettingCollection $settings) {
                    return new AlipayScanPayment($orderPay, $payType, $settings);
                },
                'settings' => [
                    'shop' => function (OrderPay $orderPay) {
                        return new AlipayScanPaySetting($orderPay);
                    }
                ]
            ],
            'AlipayFace'             => [
                'payment'  => function (OrderPay $orderPay, PayType $payType, OrderPaymentSettingCollection $settings) {
                    return new AlipayFacePayment($orderPay, $payType, $settings);
                },
                'settings' => [
                    'shop' => function (OrderPay $orderPay) {
                        return new AlipayFacePaySetting($orderPay);
                    }
                ]
            ],
            'toutiaoPayWechat'       => [
                'payment'  => function (OrderPay $orderPay, PayType $payType, OrderPaymentSettingCollection $settings) {
                    return new WechatToutiaoPayment($orderPay, $payType, $settings);
                },
                'settings' => [
                    'shop' => function (OrderPay $orderPay) {
                        return new WechatToutiaoSetting($orderPay);
                    }
                ]
            ],
            'toutiaoPayAlipay'       => [
                'payment'  => function (OrderPay $orderPay, PayType $payType, OrderPaymentSettingCollection $settings) {
                    return new AlipayToutiaoPayment($orderPay, $payType, $settings);
                },
                'settings' => [
                    'shop' => function (OrderPay $orderPay) {
                        return new AlipayToutiaoSetting($orderPay);
                    }
                ]
            ],
            'MemberCard'             => [
                'payment'  => function (OrderPay $orderPay, PayType $payType, OrderPaymentSettingCollection $settings) {
                    return new MemberCardPayment($orderPay, $payType, $settings);
                },
                'settings' => [
                    'shop' => function (OrderPay $orderPay) {
                        return new MemberCardSetting($orderPay);
                    }
                ]
            ],
            'yopProWechat'           => [
                'payment'  => function (OrderPay $orderPay, PayType $payType, OrderPaymentSettingCollection $settings) {
                    return new YopProWechatPayment($orderPay, $payType, $settings);
                },
                'settings' => [
                    'shop' => function (OrderPay $orderPay) {
                        return new YopProWechatSetting($orderPay);
                    }
                ]
            ],
            'yopProAlipay'           => [
                'payment'  => function (OrderPay $orderPay, PayType $payType, OrderPaymentSettingCollection $settings) {
                    return new YopProAlipayPayment($orderPay, $payType, $settings);
                },
                'settings' => [
                    'shop' => function (OrderPay $orderPay) {
                        return new YopProAlipaySetting($orderPay);
                    }
                ]
            ],
            'HkScanPay'              => [
                'payment'  => function (OrderPay $orderPay, PayType $payType, OrderPaymentSettingCollection $settings) {
                    return new HkScanPayment($orderPay, $payType, $settings);
                },
                'settings' => [
                    'shop' => function (OrderPay $orderPay) {
                        return new HkScanPaySetting($orderPay);
                    }
                ]
            ],
            'payPal'                 => [
                'payment'  => function (OrderPay $orderPay, PayType $payType, OrderPaymentSettingCollection $settings) {
                    return new PayPalPayment($orderPay, $payType, $settings);
                },
                'settings' => [
                    'shop' => function (OrderPay $orderPay) {
                        return new PayPalSetting($orderPay);
                    }
                ]
            ],
            'convergeQuickPay'       => [
                'payment'  => function (OrderPay $orderPay, PayType $payType, OrderPaymentSettingCollection $settings) {
                    return new ConvergeQuickPayPayment($orderPay, $payType, $settings);
                },
                'settings' => [
                    'shop' => function (OrderPay $orderPay) {
                        return new ConvergeQuickPaySetting($orderPay);
                    }
                ]
            ],
            'HkScanAlipay'           => [
                'payment'  => function (OrderPay $orderPay, PayType $payType, OrderPaymentSettingCollection $settings) {
                    return new HkScanAliPayment($orderPay, $payType, $settings);
                },
                'settings' => [
                    'shop' => function (OrderPay $orderPay) {
                        return new HkScanAliPaySetting($orderPay);
                    }
                ]
            ],
            'confirmPay'             => [
                'payment'  => function (OrderPay $orderPay, PayType $payType, OrderPaymentSettingCollection $settings) {
                    return new ConfirmPayPayment($orderPay, $payType, $settings);
                },
                'settings' => [
                    'shop' => function (OrderPay $orderPay) {
                        return new ConfirmPaySetting($orderPay);
                    }
                ]
            ],
            'wechatH5'               => [
                'payment'  => function (OrderPay $orderPay, PayType $payType, OrderPaymentSettingCollection $settings) {
                    return new WechatH5Payment($orderPay, $payType, $settings);
                },
                'settings' => [
                    'shop' => function (OrderPay $orderPay) {
                        return new WechatH5Setting($orderPay);
                    }
                ]
            ],
            'wechatNative'           => [
                'payment'  => function (OrderPay $orderPay, PayType $payType, OrderPaymentSettingCollection $settings) {
                    return new WechatNativePayment($orderPay, $payType, $settings);
                },
                'settings' => [
                    'shop' => function (OrderPay $orderPay) {
                        return new WechatNativeSetting($orderPay);
                    }
                ]
            ],
            'dcmScanPay'             => [
                'payment'  => function (OrderPay $orderPay, PayType $payType, OrderPaymentSettingCollection $settings) {
                    return new DcmScanPayPayment($orderPay, $payType, $settings);
                },
                'settings' => [
                    'shop' => function (OrderPay $orderPay) {
                        return new DcmScanPaySetting($orderPay);
                    }
                ]
            ],
            'wechatCpsAppPay'              => [
                'payment'  => function (OrderPay $orderPay, PayType $payType, OrderPaymentSettingCollection $settings) {
                    return new WechatCpsAppPayPayment($orderPay, $payType, $settings);
                },
                'settings' => [
                    'shop' => function (OrderPay $orderPay) {
                        return new WechatCpsAppPaySetting($orderPay);
                    }
                ]
            ],
            'storeBalance'                => [
                'payment'  => function (OrderPay $orderPay, PayType $payType, OrderPaymentSettingCollection $settings) {
                    return new StoreBalancePayment($orderPay, $payType, $settings);
                },
                'settings' => [
                    'shop' => function (OrderPay $orderPay) {
                        return new StoreBalanceSetting($orderPay);
                    }
                ]
            ],
            'xfpayAlipay'              => [
                'payment'  => function (OrderPay $orderPay, PayType $payType, OrderPaymentSettingCollection $settings) {
                    return new XfpayAlipayPayment($orderPay, $payType, $settings);
                },
                'settings' => [
                    'shop' => function (OrderPay $orderPay) {
                        return new XfpayAlipaySetting($orderPay);
                    }
                ]
            ],
            'xfpayWechat'              => [
                'payment'  => function (OrderPay $orderPay, PayType $payType, OrderPaymentSettingCollection $settings) {
                    return new XfpayWechatPayment($orderPay, $payType, $settings);
                },
                'settings' => [
                    'shop' => function (OrderPay $orderPay) {
                        return new XfpayWechatSetting($orderPay);
                    }
                ]
            ],
        ];
    }
}