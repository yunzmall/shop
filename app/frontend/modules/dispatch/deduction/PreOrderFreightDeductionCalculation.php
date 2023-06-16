<?php
/**
 * Created by PhpStorm.
 * User: blank
 * Date: 2022/4/13
 * Time: 18:23
 */

namespace app\frontend\modules\dispatch\deduction;


use app\common\exceptions\AppException;
use app\frontend\models\order\PreOrderDeduction;
use app\frontend\models\order\PreOrderDiscount;
use app\common\models\VirtualCoin;
use app\frontend\models\MemberCoin;
use app\frontend\modules\deduction\models\Deduction;
use app\frontend\modules\dispatch\models\OrderFreight;
use app\frontend\modules\order\models\PreOrder;

class PreOrderFreightDeductionCalculation
{

    /**
     * @var PreOrder
     */
    public $order;

    /**
     * @var Deduction
     */
    private $deduction;


    /**
     * @var OrderFreight
     */
    private $orderFreight;

    public function init(Deduction $deduction, OrderFreight $orderFreight)
    {
        $this->deduction = $deduction;

        $this->orderFreight = $orderFreight;

        $this->order = $orderFreight->getOrder();

    }

    public function getUidAttribute()
    {
        return $this->order->uid;
    }

    public function getCodeAttribute()
    {
        return $this->getCode();
    }

    public function getNameAttribute()
    {
        return $this->getName();
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->getDeduction()->getCode();
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->getDeduction()->getName();
    }

    /**
     * @return bool
     */
    public function getIsEnableAttribute()
    {
        return $this->isEnableDeductFreight();
    }


    /**
     * 此抵扣对应的虚拟币
     * @return VirtualCoin
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function newCoin()
    {
        return app('CoinManager')->make($this->getCode());
    }

    /**
     * @return mixed
     * @throws \app\common\exceptions\AppException
     */
    public function getAmountAttribute()
    {
        return $this->getAmount();
    }

    protected $deductionAmount;

    /**
     * @return mixed
     * @throws \app\common\exceptions\AppException
     */
    public function getAmount()
    {

        if (!$this->deductionAmount) {

            $this->deductionAmount = $this->newCoin();

            // 购买者不存在虚拟币记录
            if (!$this->getMemberCoin()) {
                trace_log()->deduction('订单抵扣', "{$this->getName()} 用户没有对应虚拟币");
                return $this->deductionAmount;
            }

            $deductionFreightAmount =  $this->orderFreight->getPriceBefore($this->getCode().'Deduction');

            $amount = min($this->getMemberCoin()->getMaxUsableCoin()->getMoney(), $deductionFreightAmount);

            $this->deductionAmount = $this->newCoin()->setMoney($amount);
        }

        return $this->deductionAmount;
    }

    public function getOrder()
    {
        return $this->order;
    }

    public function getDeduction()
    {
        return $this->deduction;
    }

    /**
     * @return Deduction
     */
    public function getOrderDeduction()
    {
        return $this->order->getOrderDeductManager()->getOrderDeductions()->where('code', $this->getCode())->first();
    }


    public function isChecked()
    {
        $this->getOrderDeduction()->isChecked();
    }

    /**
     * 下单用户此抵扣对应虚拟币的余额
     * @return MemberCoin
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    private function getMemberCoin()
    {
        if (isset($this->memberCoin)) {
            return $this->memberCoin;
        }
        $code = $this->getCode();

        return \app\frontend\modules\deduction\EnableDeductionService::getInstance()->getMemberCoin($code);
    }

    //是否开启运费抵扣
    public function isEnableDeductFreight()
    {
        return $this->getDeduction()->isEnableDeductDispatchPrice();
    }

}