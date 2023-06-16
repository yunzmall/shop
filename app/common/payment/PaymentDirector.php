<?php
/**
 * Created by PhpStorm.
 *
 * 
 *
 * Date: 2021/8/26
 * Time: 16:52
 */

namespace app\common\payment;



use app\common\models\PayType;
use app\common\payment\types\BasePaymentTypes;

class PaymentDirector
{
	public $paymentTypes;

	public function setPaymentTypes(BasePaymentTypes $basePaymentTypes)
	{
		$this->paymentTypes = $basePaymentTypes;
		app()->singleton(BasePaymentTypes::class,function () {
			return $this->paymentTypes;
		});
		return $this;
	}

	public function getPaymentButton()
	{
		$paymentMethodList = collect(app()->tagged('paymentMethod'));
		$paymentMethodList = $paymentMethodList->filter(function ($method) {
			$method->setTypes($this->paymentTypes);
			$method->setPayType();
			return $method->getCode() && $method->canUse();
		});
		$buttonList = $paymentMethodList->map(function ($payment) {
			return  [
				'code' 	    	=> $payment->getCode(),
				'name'   		=> $payment->getName(),
				'value'         => $payment->getId(),
				'need_password' => $payment->needPassword(),
				'weight'    	=> $payment->getWeight(),
			];
		});
		return $buttonList->sortByDesc('weight')->values();


	}
}