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

use app\common\payment\setting\other\RemittanceSetting as BaseRemittanceSetting;

class RemittanceSetting extends BaseRemittanceSetting
{
	public function canUse()
	{
		return \Setting::get('shop.pay.remittance')
			&& $this->paymentTypes->getOrders()->contains(function ($order) {
				return $order->id > 0;
			});
	}
}