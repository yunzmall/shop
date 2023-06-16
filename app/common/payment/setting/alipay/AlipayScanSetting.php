<?php
/**
 * Created by PhpStorm.
 * 
 *
 *
 * Date: 2021/8/26
 * Time: 14:02
 */

namespace app\common\payment\setting\alipay;



use app\common\facades\Setting;
use app\common\payment\setting\BasePaymentSetting;

class AlipayScanSetting extends BasePaymentSetting
{
    public function canUse()
    {
        if (request()->input('type') == 9) {
            return true;
        }
        if (request()->is_store_pos) {
            return true;
        }
        $setting = Setting::get('shop.pay');
        if (request()->is_shop_pos && $setting['alipay'] && $setting['alipay_pay_api'] == 1) {
            return true;
        }
        return false;
    }

	public function getWeight()
	{
		return 899;
	}
}