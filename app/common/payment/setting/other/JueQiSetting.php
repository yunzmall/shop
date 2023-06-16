<?php
/**
 * Created by PhpStorm.
 *
 * 
 *
 * Date: 2021/8/26
 * Time: 14:02
 */

namespace app\common\payment\setting\other;


use app\common\helpers\Client;
use app\common\payment\setting\BasePaymentSetting;

class JueQiSetting extends BasePaymentSetting
{
	public function canUse()
	{
		return request()->input('type') != 7
			&& app('plugins')->isEnabled('jueqi-pay')
			&& \Setting::get('plugin.jueqi_pay_set.switch') == 1
			&& Client::is_weixin() === true;
	}
}