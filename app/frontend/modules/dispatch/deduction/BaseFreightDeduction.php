<?php
/**
 * Created by PhpStorm.
 * User: blank
 * Date: 2022/4/13
 * Time: 15:27
 */

namespace app\frontend\modules\dispatch\deduction;


use app\frontend\models\order\PreOrderDeduction;
use app\frontend\modules\order\models\PreOrder;
use app\frontend\modules\dispatch\models\PreOrderFreightDeduction;

abstract class BaseFreightDeduction
{

    protected $name;

    protected $code;

    /**
     * @var PreOrder
     */
    protected $order;

    /**
     * @var \app\frontend\modules\dispatch\models\OrderFreight|mixed
     */
    protected $orderFreight;

    /**
     * @var PreOrderDeduction
     */
    protected $orderDeduction;

    public function __construct(PreOrder $order, PreOrderDeduction $orderDeduction)
    {
        $this->order = $order;

        $this->orderFreight = $order->getFreightManager();

        $this->orderDeduction = $orderDeduction;
    }

    public function isChecked()
    {
        return $this->orderDeduction->isChecked();
    }

    public function getCode()
    {
        return $this->orderDeduction->getCode();
    }

    public function getName()
    {
        return $this->orderDeduction->getName();
    }

    /**
     * @return bool
     */
    public function calculated()
    {
        return isset($this->amount);
    }

    public function preSave()
    {
        return true;
    }


    /**
     * 获取总金额
     * @return float
     */
    public function getAmount()
    {
        if (isset($this->amount)) {
            return $this->amount;
        }
        $this->amount = $this->_getAmount();

        $this->amount = $this->_getAmount();
        if($this->amount && $this->preSave()){
            // 将抵扣总金额保存在订单优惠信息表中

            $coin = $this->orderDeduction->newCoin()->setCoin($this->amount);

            $preOrderDiscount = new PreOrderFreightDeduction([
                'code' => $this->getCode(),
                'amount' => $this->amount,
                'name' => $this->getName(),
                'coin' => $coin->getCoin(),
            ]);
            $preOrderDiscount->setOrder($this->order);
        }

        return $this->amount;
    }

    /**
     * 获取金额
     * @return int
     */
    abstract protected function _getAmount();
}