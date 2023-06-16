<?php
/**
 * Created by PhpStorm.
 * 
 *
 *
 * Date: 2021/8/26
 * Time: 15:47
 */

namespace app\common\payment\setting;


use app\common\models\PayType;
use app\common\payment\types\BasePaymentTypes;

abstract class BasePaymentSetting
{
	public $paymentTypes;
	public $payType;

	public function __construct(BasePaymentTypes $basePaymentTypes)
	{
		$this->paymentTypes = $basePaymentTypes;
	}

	public function setPayType(PayType $payType)
	{
		$this->payType = $payType;
	}

	abstract public function canUse();

	public function getCode()
	{
		return $this->payType->code;
	}

	 public function getName()
	 {
		 if (app('plugins')->isEnabled('pay-manage')) {
			 return \Yunshop\PayManage\models\PayType::currentPayAlias($this->payType->id);
		 }
		 return $this->payType->name;
	 }

	 public function getId()
	 {
		 if (!miniVersionCompare('1.1.132')) {
             if ($this->payType->code == 'wechatMinPay') {
				 return 1;
			 }
		 }
	 	return $this->payType->id;
	 }

	 public function getWeight()
	 {
	 	return 10;
	 }

	 public function needPassword()
	 {
	 	return false;
	 }


}