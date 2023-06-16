<?php
/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 2021/8/26
 * Time: 14:00
 */

namespace app\frontend\modules\order\payment\types;


use app\common\models\OrderPay;
use app\common\payment\setting\alipay\AlipayJsapiSetting;
use app\common\payment\setting\converge\ConvergeAlipayH5Setting;
use app\common\payment\setting\converge\ConvergeUnionPaySetting;
use app\common\payment\setting\other\AnotherSetting;
use app\common\payment\setting\other\CODSetting;
use app\common\payment\setting\other\ConfirmSetting;
use app\common\payment\setting\other\ParentSetting;
use app\common\payment\setting\other\RemittanceSetting;
use app\common\payment\setting\other\DcmScanSetting;
use app\common\payment\setting\wechat\WechatCpsAppSetting;
use app\common\payment\setting\wechat\WechatJsapiSetting;
use app\common\payment\types\BasePaymentTypes;

class OrderPaymentTypes extends BasePaymentTypes
{
	public $orderPay;
	public function __construct(OrderPay $orderPay)
	{
		parent::__construct();
		$this->orderPay = $orderPay;
		self::rebind();
	}

	public function rebind()
	{
		app()->bind(CODSetting::class,\app\frontend\modules\order\payment\setting\CODSetting::class);
		app()->bind(RemittanceSetting::class,\app\frontend\modules\order\payment\setting\RemittanceSetting::class);
		app()->bind(AnotherSetting::class,\app\frontend\modules\order\payment\setting\AnotherSetting::class);
		app()->bind(ParentSetting::class,\app\frontend\modules\order\payment\setting\ParentSetting::class);
		app()->bind(DcmScanSetting::class,\app\frontend\modules\order\payment\setting\DcmScanSetting::class);
		app()->bind(WechatCpsAppSetting::class,\app\frontend\modules\order\payment\setting\WechatCpsAppSetting::class);
        app()->bind(WechatJsapiSetting::class,\app\frontend\modules\order\payment\setting\WechatJsapiSetting::class);
        app()->bind(AlipayJsapiSetting::class,\app\frontend\modules\order\payment\setting\AlipayJsapiSetting::class);
        app()->bind(ConfirmSetting::class,\app\frontend\modules\order\payment\setting\ConfirmSetting::class);
        app()->bind(ConvergeUnionPaySetting::class,\app\frontend\modules\order\payment\setting\ConvergeUnionPaySetting::class);
        app()->bind(ConvergeAlipayH5Setting::class,\app\frontend\modules\order\payment\setting\ConvergeAlipayH5Setting::class);
	}

    /**
     * @return OrderPay
     */
	public function getOrderPay()
    {
        return $this->orderPay;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection
     */
	public function getOrders()
	{
		return $this->orderPay->orders;
	}

    /**
     * @return mixed
     */
	public function getOrder()
	{
		return $this->orderPay->orders->first();
	}
}