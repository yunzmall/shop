<?php


namespace app\common\modules\stock;


use app\common\models\Order;

abstract class BaseNoDeductStock
{
    /**
     * @var Order
     */
    protected $order;
    protected $param;

    public function __construct(Order $order,$param = [])
    {
        $this->order = $order;
        $this->param = $param;
    }

    //是否扣除：true原逻辑扣除，false不扣除
    abstract public function isDeduct(): bool;
}