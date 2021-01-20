<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/8/3
 * Time: 9:50
 */

namespace app\frontend\modules\order\dispatch\order;

use app\common\models\DispatchType;
use app\frontend\modules\order\models\PreOrder;
use app\frontend\modules\dispatch\models\PreOrderAddress;
use Illuminate\Foundation\Validation\ValidatesRequests;

abstract class BaseOrderDispatchType
{

    use ValidatesRequests;

    public $order;


    protected $dispatchType;

    public function __construct(PreOrder $order,DispatchType $dispatchType)
    {
        $this->order = $order;

        $this->dispatchType = $dispatchType;

        $this->_init();
    }

    abstract function _init();


    public function preOrderAddress()
    {
        return new PreOrderAddress();
    }

    public function getDispatchType()
    {
        return $this->dispatchType;
    }

    /**
     * 是否需要地址
     * @return int
     */
    public function needSend()
    {
        return $this->dispatchType->need_send;
    }

    /**
     * 是否需要计算运费
     * @return int
     */
    public function needFreight()
    {
        return $this->dispatchType->need_send;
    }
}