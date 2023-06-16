<?php
/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 2021/8/26
 * Time: 14:01
 */

namespace app\common\payment\method;


use app\common\models\PayType;
use app\common\payment\setting\BasePaymentSetting;
use app\common\payment\types\BasePaymentTypes;

abstract class BasePayment
{
	public $BasePaymentTypes;
	public $BasePaymentSetting;

	public function canUse()
	{
		return $this->BasePaymentSetting->canUse() && $this->BasePaymentTypes->canUse($this->code);
	}

	public function setTypes(BasePaymentTypes $BasePaymentTypes)
	{
		$this->BasePaymentTypes = $BasePaymentTypes;
	}

	public function setPayType()
	{
		$pay_type = $this->BasePaymentTypes->listPayType->where('code',$this->code)->first()?:new PayType();
		$this->BasePaymentSetting->setPayType($pay_type);
	}

	protected function setSetting(BasePaymentSetting $BasePaymentSetting)
	{
		$this->BasePaymentSetting = $BasePaymentSetting;
	}

	public function __call($method , $parameters)
	{
		return $this->BasePaymentSetting->$method(...$parameters);
	}



}