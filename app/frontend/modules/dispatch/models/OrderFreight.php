<?php
/**
 * Created by PhpStorm.
 * User: blank
 * Date: 2022/4/12
 * Time: 9:36
 */

namespace app\frontend\modules\dispatch\models;


use app\framework\Database\Eloquent\Collection;
use app\frontend\models\order\PreOrderDeduction;
use app\frontend\modules\dispatch\deduction\BaseFreightDeduction;
use app\frontend\modules\dispatch\deduction\CoinFreightDeduction;
use app\frontend\modules\dispatch\deduction\OrderFreightDeductManager;
use app\frontend\modules\dispatch\discount\BaseFreightDiscount;
use app\frontend\modules\dispatch\freight\BaseFreight;
use app\frontend\modules\dispatch\freight\pipes\OrderDeductionFreightPricePipe;
use app\frontend\modules\dispatch\freight\pipes\OrderFreightDeductionPricePipe;
use app\frontend\modules\dispatch\freight\pipes\OrderFreightDiscountPricePipe;
use app\frontend\modules\dispatch\freight\pipes\OrderInitialFreightPricePipe;
use app\frontend\modules\order\models\PreOrder;
use app\frontend\modules\order\PriceNode;
use app\frontend\modules\order\PriceNodeTrait;

class OrderFreight
{

    use PriceNodeTrait;

    /**
     * @var BaseFreight
     */
    public $priceCalculation;

    /**
     * @var PreOrder
     */
    protected $order;


    /**
     * @var Collection
     */
    protected $freightPrices;

    /**
     * @var float
     */
    protected $initialAmount;


    protected $discountWeight = 0;

    protected static $deductionWeight = 0;

    public function __construct(PreOrder $order)
    {
        $this->order = $order;

        // 订单运费抵扣集合
        $this->order->setRelation('orderFreightDeduction', new Collection());

    }

    public function getOrder()
    {
        return $this->order;
    }

    public function orderFreightDeduction()
    {
        return $this->order->orderFreightDeduction;
    }


    public function pushDeductionPricePipe(PreOrderDeduction $freightDeduction)
    {
        if (!$this->verifyPriceNodes($freightDeduction->getCode().'Deduction')) {
//            self::$deductionWeight++;
            $priceNode = new OrderFreightDeductionPricePipe($this, new CoinFreightDeduction($this->order, $freightDeduction), 5000 +  self::$deductionWeight);
            $this->getPriceNodes()->push($priceNode);
        }
    }


    /**
     * @return mixed
     * @throws \app\common\exceptions\AppException
     */
    public function _getPriceNodes()
    {
        // 订单节点
        $nodes = collect([
            new OrderInitialFreightPricePipe($this, 1000)
        ]);

        //订单运费优惠节点
        $discountNodes = $this->getDiscounts()->map(function (BaseFreightDiscount $discount) {
            $this->discountWeight += 1;
            return new OrderFreightDiscountPricePipe($this, $discount, 2000 + $this->discountWeight);
        });

        // 按照weight排序
        $nodes = $nodes->merge($discountNodes)->sortBy(function (PriceNode $priceNode) {
            return $priceNode->getWeight();
        })->values();
        return $nodes;
    }

    public function getDeductions()
    {
        return (new OrderFreightDeductManager($this->order))->getOrderFreightDeductions();
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function getDiscounts()
    {

        $discounts = collect([]);

        $configs = \app\common\modules\shop\ShopConfig::current()->get('shop-foundation.order-freight-discount');

        $discountGroup = collect($configs)->groupBy('type');

        foreach ($discountGroup as $group) {
            $discount_items = $group->sortByDesc('priority');
            foreach ($discount_items as $item) {
                /**
                 * @var BaseFreightDiscount $discountClass
                 */
                $discountClass = call_user_func($item['class'], $this->order);
                if ($discountClass->validate()) {
                    $discounts->push($discountClass);
                    break;
                }
            }

        }
        return $discounts;
    }

    /**
     * 订单初始运费金额
     */
    public function getInitialFreightAmount()
    {

        if (!isset($this->initialAmount)) {

            if (is_null($this->order->getOrderDispatchType()) || !$this->order->getOrderDispatchType()->needFreight()) {
                // 没选配送方式 或者 不需要配送不需要运费
                return $this->initialAmount = 0;
            }

            $this->initialAmount = $this->getPriceCalculation()->getAmount();


            $fullAmountFreeFreight = (new \app\frontend\modules\dispatch\models\fullAmountFreeFreight($this->order))->getAmount();
            $this->initialAmount = max($this->initialAmount - $fullAmountFreeFreight, 0);

        }
        return $this->initialAmount;
    }
    /**
     * 订单最终运费金额
     */
    public function getFinalFreightAmount()
    {
        return max($this->getPriceAfter($this->getPriceNodes()->last()->getKey()), 0);
    }


    public function getPriceCalculation()
    {
        if (!isset($this->priceCalculation)) {
            $this->priceCalculation = $this->getFreightClass();
        }

        return $this->priceCalculation;
    }


    public function getFreightClass()
    {

        $freightPrice = $this->getFreightPrices()->first(function ($freightPrice) {
            return $freightPrice->getGroup() == 'third_party';
        });

        if (!$freightPrice) {
            $freightPrice = $this->getFreightPrices()->first();
        }

        return $freightPrice;
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
}