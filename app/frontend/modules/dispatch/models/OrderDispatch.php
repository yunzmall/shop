<?php
/**
 * Created by PhpStorm.
 * Author: 芸众商城 www.yunzshop.com
 * Date: 2017/3/9
 * Time: 上午9:25
 */

namespace app\frontend\modules\dispatch\models;

use app\common\events\dispatch\OrderDispatchWasCalculated;
use app\frontend\modules\dispatch\discount\EnoughReduce;
use app\frontend\modules\dispatch\discount\LevelFreeFreight;
use app\frontend\modules\dispatch\freight\BaseFreight;
use app\frontend\modules\order\models\PreOrder;


class OrderDispatch
{
    /**
     * @var PreOrder
     */
    private $order;
    /**
     * @var float
     */
    private $freight;


    protected $freightPrices;


    /**
     * OrderDispatch constructor.
     * @param PreOrder $preOrder
     */
    public function __construct(PreOrder $preOrder)
    {
        $this->order = $preOrder;
    }

    public function getFreightPrices()
    {
        if (!isset($this->freightNodes)) {
            $this->freightPrices = $this->_getFreightPrices();
        }

        return $this->freightPrices;
    }

    //订单运费集合
    public function _getFreightPrices()
    {
        // 订单运费集合
        $freightPrices = collect();

        $freightConfig = \app\common\modules\shop\ShopConfig::current()->get('shop-foundation.order-freight');


        foreach ($freightConfig as $configItem) {

            $freightPrices->push(call_user_func($configItem['class'], $this->order, $configItem['weight']));
        }

        $freightPrices = $freightPrices->filter(function(BaseFreight $freightPrice) {
            //过滤不能使用的运费计算方式
            return $freightPrice->needDispatch();
        })->sortBy(function (BaseFreight $freightPrice) {
            // 按照weight排序
            return $freightPrice->getWeight();
        })->values();

        return $freightPrices;
    }

    public function freightPriority()
    {

        foreach ($this->getFreightPrices() as $freightPrice) {
           switch ($freightPrice->getGroup()) {
               case 'third_party':
                   return $freightPrice->getAmount();
                   break;
               case 'max':
                   return $this->getFreightPrices()->max(function(BaseFreight $freight) {
                       return $freight->getAmount();
                   });
                   break;
               case 'sum':
                   return $this->getFreightPrices()->sum(function(BaseFreight $freight) {
                       return $freight->getAmount();
                   });
                   break;
               default:
           }
        }

        return 0;
    }

    public function _getFreight()
    {

        if (is_null($this->order->getOrderDispatchType()) || !$this->order->getOrderDispatchType()->needFreight()) {
            // 没选配送方式 或者 不需要配送不需要运费
            return 0;
        }

        return $this->freightPriority();
    }

    /**
     * 订单运费
     * @return float|int
     */
    public function getFreight()
    {

        if (!isset($this->freight)) {

            $this->freight = $this->_getFreight();

            //获取满额包邮类
            $enoughReduce = $this->order->getEnoughReduce();
            //运费优惠计算
            //$this->freight = max($this->freight - (new EnoughReduce($this->order))->getAmount(), 0);

            $this->freight = max($this->freight - $enoughReduce->getAmount(), 0);
            $this->freight = max($this->freight - (new LevelFreeFreight($this->order))->getAmount(), 0);

        }

        return $this->freight;
    }
}