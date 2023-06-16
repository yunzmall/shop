<?php
/**
 * Created by PhpStorm.
 * 
 *
 *
 * Date: 2021/9/3
 * Time: 18:49
 */

namespace app\frontend\modules\order\payment\setting;

use app\common\payment\setting\other\ParentSetting as BaseParentSetting;
use Yunshop\ParentPayment\common\services\PaymentService;

class ParentSetting extends BaseParentSetting
{
	public function canUse()
	{
		return  app('plugins')->isEnabled('parent-payment')
			&& \Setting::get('plugin.parent_payment.plugin_state')
			&& (!request()->input('pid') || request()->input('pid') == \YunShop::app()->getMemberId())
			&& (new PaymentService())->canUse(\YunShop::app()->getMemberId(), $this->paymentTypes->getOrder()->id)
			&& $this->paymentTypes->getOrders()->contains(function ($order) {
				return $order->plugin_id == 0;
			})
			&& $this->paymentTypes->getOrders()->count() == 1;
	}
}