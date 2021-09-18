<?php
/**
 * Created by PhpStorm.
 * Name: 芸众商城系统
 * Author: 广州市芸众信息科技有限公司
 * Profile: 广州市芸众信息科技有限公司位于国际商贸中心的广州，专注于移动电子商务生态系统打造，拥有芸众社交电商系统、区块链数字资产管理系统、供应链管理系统、电子合同等产品/服务。官网 ：www.yunzmall.com  www.yunzshop.com
 * Date: 2021/6/15
 * Time: 14:21
 */

namespace app\frontend\modules\memberCart\services;


use app\common\models\DispatchType;
use app\frontend\modules\memberCart\models\DispatchTypeOrder;
use app\frontend\modules\order\dispatch\DispatchTypeMenu;

class DispatchTypeMenuService
{

    public $order;

    protected $groupCollection;

    protected $orderDispatchType;

    protected $dispatchTypesSetting;


    protected $shopAllEnableDispatchType;

    public function __construct($shopAllEnableDispatchType, DispatchTypeOrder $dispatchTypeOrder)
    {

        $this->order = $dispatchTypeOrder;

        $this->shopAllEnableDispatchType = $shopAllEnableDispatchType;

        $this->dispatchTypesSetting = $this->getDispatchTypesSetting();
    }

    /**
     * 当前公众号的配送设置，未设置取默认
     * @return \app\framework\Database\Eloquent\Collection|static
     */
    public function getDispatchType()
    {
        if (!isset($this->shopAllEnableDispatchType)) {
            $this->shopAllEnableDispatchType =  DispatchType::getAllEnableDispatchType();
        }
        return  $this->shopAllEnableDispatchType;
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
                return new $dispatchTypeClass($dispatchType,$this->order);
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