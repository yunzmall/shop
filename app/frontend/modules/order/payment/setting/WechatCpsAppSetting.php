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

use app\common\payment\setting\wechat\WechatCpsAppSetting as BaseWechatCpsAppSetting;

class WechatCpsAppSetting extends BaseWechatCpsAppSetting
{
	public function canUse()
	{
		return request()->wechat_app_pay_type == 'cps' && \Setting::get('plugin.aggregation-cps.pay_info')['weixin_pay'];
	}
}