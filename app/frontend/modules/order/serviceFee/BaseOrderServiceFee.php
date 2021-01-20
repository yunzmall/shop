<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/8/20
 * Time: 11:14
 */

namespace app\frontend\modules\order\serviceFee;


use app\frontend\modules\order\models\PreOrder;

abstract class BaseOrderServiceFee
{
    /**
     * @var PreOrder
     */
    protected $order;
    /**
     * 服务费名称
     * @var string
     */
    protected $name;
    /**
     * 服务费标识
     * @var
     */
    protected $code;
    /**
     * 服务费金额
     * @var float
     */
    private $amount;

    public function __construct(PreOrder $order)
    {
        $this->order = $order;

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

        return $this->amount;
    }
    public function getCode(){
        return $this->code;
    }
    public function getName(){
        return $this->name;
    }

    /**
     * 是否开启
     * @return bool
     */
    public function enable()
    {
        return true;
    }

    /**
     * 是否选中
     * @return bool
     */
    public function isChecked()
    {
        $checkedFee = $this->order->getParams('service_fee')?:[];

        if (in_array($this->getCode(), $checkedFee)) {
            return true;
        }

        return false;
    }

    /**
     * 是否显示
     */
    public function isShow()
    {
        if ($this->isChecked()) {
            return true;
        }

        return false;
    }

    abstract protected function _getAmount();

}