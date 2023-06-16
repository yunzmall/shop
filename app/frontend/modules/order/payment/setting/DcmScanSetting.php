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

use app\common\payment\setting\other\DcmScanSetting as BaseDcmScanSetting;

class DcmScanSetting extends BaseDcmScanSetting
{
	public function canUse()
	{
		return \Setting::get('plugin.dcm-scan-pay.switch');
	}
}