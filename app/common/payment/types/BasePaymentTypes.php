<?php
/**
 * Created by PhpStorm.
 * 
 * 
 *
 * Date: 2021/8/26
 * Time: 13:59
 */

namespace app\common\payment\types;


use app\common\models\PayType;

abstract class BasePaymentTypes
{
	public $filterCode;

	public $availableCode;

	public $listPayType = [];

	public function __construct()
	{
		$this->listPayType = PayType::get();
	}

	public function canUse(string $code)
	{
		//需要过滤掉的支付方式
		if (in_array($code,$this->filterCode)) {
			return false;
		}
		//设置了可用的支付方式验证
		if (!empty($this->availableCode) && !in_array($code,$this->availableCode)) {
			return false;
		}
		return true;
	}

	public function setFilterCode(array $filter_code)
	{
		$this->filterCode = $filter_code;
	}
}