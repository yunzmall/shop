<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/8/3
 * Time: 9:42
 */

namespace app\frontend\modules\order\dispatch;


use app\common\models\DispatchType;
use app\common\modules\order\OrderCollection;
use app\frontend\modules\order\models\PreOrder;

abstract class DispatchTypeMenu
{

    /**
     * 首个订单
     * @var PreOrder
     */
    protected $order;


    /**
     * 下单页所以订单集合
     * @var OrderCollection
     */
    protected $orders;

    /**
     *
     * @var DispatchType
     */
    protected $dispatchType;

    public function __construct($dispatchType = null, $order, $orders)
    {
        $this->order = $order;
        $this->orders = $orders;
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
        $result = $this->order->orderGoods->first()->goods->goodsDispatchTypeIds();
        foreach ($this->order->orderGoods as $orderGoods) {
            // 与结果取差，删掉不相交的值
            $diffIds = array_diff($result, $orderGoods->goods->goodsDispatchTypeIds());
            foreach ($result as $key => $item) {
                if (in_array($item, $diffIds)) {
                    unset($result[$key]);
                }
            }
        }
        return in_array($this->getId(), $result);
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