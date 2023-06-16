<?php


namespace app\frontend\modules\order\taxFee;

use app\frontend\modules\order\models\PreOrder;

abstract class BaseOrderTaxFee
{
    /**
     * @var PreOrder
     */
    protected $order;
    /**
     * 税费名称
     * @var string
     */
    protected $name;
    /**
     * 展示选项名
     * @var string
     */
    protected $show_name;
    /**
     * 税费标识
     * @var
     */
    protected $code;
    /**
     * 税费金额（优惠的话就负数，要额外加钱就正数）
     * @var float
     */
    private $amount;

    public function __construct(PreOrder $order)
    {
        $this->order = $order;
    }

    /**
     * 是否开启
     * @return bool
     */
    public function enable()
    {
        return true;
    }

    public function getCode()
    {
        return $this->code;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getShowName()
    {
        return $this->show_name;
    }

    public function getRate()
    {
        return null;
    }

    /**
     * 是否显示
     * @return bool
     */
    public function isShow()
    {
        return true;
    }

    /**
     * 获取总金额
     * @return float
     */
    public function getAmount()
    {
        if (!isset($this->amount)) {
            $this->amount = $this->_getAmount();
        }
        return $this->amount;
    }

    /**
     * 是否选中使用
     * @return bool
     */
    public function isChecked()
    {
        $checkedFee = $this->order->getParams('tax_fee')?:[];
        if (in_array($this->getCode(), $checkedFee)) {
            return true;
        }
        return false;
    }

    abstract protected function _getAmount();
}