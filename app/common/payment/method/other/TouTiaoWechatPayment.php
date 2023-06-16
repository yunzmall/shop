<?php
/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 2021/8/26
 * Time: 14:02
 */

namespace app\common\payment\method\other;


use app\common\payment\method\BasePayment;
use app\common\payment\setting\other\TouTiaoWechatSetting;

class TouTiaoWechatPayment extends BasePayment
{
	public $code = 'toutiaoPayWechat';
	
	public function __construct(TouTiaoWechatSetting $paymentSetting)
	{
		$this->setSetting($paymentSetting);
	}
}