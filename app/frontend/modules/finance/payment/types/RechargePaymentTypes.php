<?php
/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 2021/8/26
 * Time: 14:00
 */

namespace app\frontend\modules\finance\payment\types;


use app\common\models\OrderPay;
use app\common\payment\setting\converge\ConvergeAlipayH5Setting;
use app\common\payment\setting\other\CODSetting;
use app\common\payment\types\BasePaymentTypes;
use app\frontend\modules\finance\services\BalanceRechargeSetService;

class RechargePaymentTypes extends BasePaymentTypes
{
    public $filterCode = [
        //余额支付
        'balance',
        'MemberCard',

        'jinepayH5',
        'authPay',

        'AlipayJsapi', //  支付宝JSAPI支付（服务商）
        'WechatJsapi' //   微信JSAPI支付（服务商）
    ];

    public function __construct()
    {
        parent::__construct();

        app()->bind(ConvergeAlipayH5Setting::class, \app\frontend\modules\order\payment\setting\ConvergeAlipayH5Setting::class);

        $service = new BalanceRechargeSetService();
        if ($service->getAppointPay()) {
            $this->availableCode = $service->getCanUsePayment();
        }
    }
}