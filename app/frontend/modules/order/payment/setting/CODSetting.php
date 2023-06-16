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

use app\common\payment\setting\other\CODSetting as BaseCODSetting;

class CODSetting extends BaseCODSetting
{
	public function canUse()
	{
		foreach ($this->paymentTypes->getOrders() as $order){
			if ($order->isVirtual()) {
				return false;
			}
		}
		return \Setting::get('shop.pay.COD');
	}
}