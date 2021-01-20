<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/8/3
 * Time: 10:28
 */

namespace app\frontend\modules\order\dispatch;

use app\common\models\DispatchType;
use app\frontend\modules\order\models\PreOrder;
use app\common\modules\order\OrderCollection;


class DispatchTypeMenuManager
{
    /**
     * 首个订单
     * @var PreOrder
     */
    private $order;


    /**
     * 下单订单集合
     * @var OrderCollection
     */
    private $orders;

    protected $orderDispatchType;

    protected $dispatchTypesSetting;

    public function __construct(PreOrder $order,OrderCollection $orders)
    {
        $this->order = $order;

        $this->orders = $orders;

        $this->dispatchTypesSetting = $this->getDispatchTypesSetting();
    }

    /**
     * 当前公众号的配送设置，未设置取默认
     * @return \app\framework\Database\Eloquent\Collection|static
     */
    public function getDispatchType()
    {
        return DispatchType::getAllEnableDispatchType();
    }

    /**
     * 获取当前订单配送方法配置
     * 根据订单 plugin_id 获取插件订配送方法，为null获取标准商城配送方式
     * @return array|mixed
     */
    public function getDispatchTypesSetting()
    {
        $configs = \app\common\modules\shop\ShopConfig::current()->get('shop-foundation.order-dispatch-menu')[$this->order->plugin_id];

        if (is_null($configs)) {
            $configs = \app\common\modules\shop\ShopConfig::current()->get('shop-foundation.order-dispatch-menu.shop');
        }

        return $configs;
    }

    /**
     * 商城中开启的配送方式
     * @return \Illuminate\Support\Collection|static
     */
    public function allEnableDispatchType()
    {
        $allDispatchType = $this->getDispatchType();

        if ($allDispatchType->isEmpty()) {
            return collect([]);
        }

        $orderDispatchTypes = $allDispatchType->map(function ($dispatchType) {
            $dispatchTypeClass = $this->dispatchTypesSetting[$dispatchType->code];
            if (class_exists($dispatchTypeClass)) {
                return new $dispatchTypeClass($dispatchType,$this->order, $this->orders);
            }
            return null;
        })->filter(function ($dispatchType) {
            //开启的
            return isset($dispatchType) && $dispatchType instanceof DispatchTypeMenu && $dispatchType->enable();
        })->sortByDesc(function (DispatchTypeMenu $dispatchType) {
            // 按照sort进行倒序排序
            return $dispatchType->sort();
        })->values();

        return $orderDispatchTypes;
    }

    //订单可选择的配送方式
    public function canUseDispatchType()
    {
        $orderDispatchTypes = $this->allEnableDispatchType()->filter(function ($dispatchType) {
            // 可用的
            return isset($dispatchType) && $dispatchType instanceof DispatchTypeMenu && $dispatchType->canUse();
        });
        return $orderDispatchTypes;
    }


    /**
     * 返回订单配送方法集合
     * @return mixed
     */
    public function getOrderDispatchType()
    {
        if (!isset($this->orderDispatchType)) {

            $this->orderDispatchType = $this->canUseDispatchType();

        }

        return $this->orderDispatchType;
    }
}