<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/8/3
 * Time: 9:42
 */

namespace app\frontend\modules\order\dispatch;


use app\common\models\DispatchType;
use app\frontend\modules\memberCart\models\DispatchTypeOrder;

abstract class DispatchTypeMenu
{

    /**
     * 当前订单
     * @var DispatchTypeOrder
     */
    protected $order;


    /**
     *
     * @var DispatchType
     */
    protected $dispatchType;

    public function __construct($dispatchType = null, DispatchTypeOrder $order)
    {
        $this->order = $order;

        $this->dispatchType = $dispatchType;
    }

    public function getId()
    {
        return $this->dispatchType['id'];
    }

    public function getName()
    {
        return $this->dispatchType['name'];
    }

    public function getCode()
    {
        return $this->dispatchType['code'];
    }

    /**
     * 配送方式订单是否能使用
     * @return bool
     */
    public function canUse()
    {
        return $this->orderGoodsEnable();
    }

    /**
     * 订单商品支持的配送方式
     * @return bool
     */
    public function orderGoodsEnable()
    {
        return in_array($this->getId(), $this->order->dispatch_type_ids);
    }

    /**
     * 配送方式是否开启
     * @return mixed
     */
    public function enable()
    {
        return $this->dispatchType['enable'];
    }

    public function sort()
    {
        return $this->dispatchType['sort'];
    }

    /**
     * @return array
     */
    public function data()
    {
        return [
            'dispatch_type_id' => $this->dispatchType['id'],
            'name' => $this->dispatchType['name'],
        ];
    }

}