<?php

namespace app\frontend\modules\orderGoods\price\option;

/**
 * Created by PhpStorm.
 * User: shenyang
 * Date: 2017/5/19
 * Time: 下午6:04
 */

use app\common\helpers\Serializer;
use app\frontend\models\order\PreOrderCoinExchange;
use app\frontend\models\orderGoods\PreOrderGoodsCoinExchange;
use app\frontend\modules\deduction\orderGoods\PreOrderGoodsDeduction;
use app\frontend\modules\order\PriceNode;
use app\frontend\modules\order\PriceNodeTrait;
use app\frontend\modules\orderGoods\discount\BaseDiscount;
use app\frontend\modules\orderGoods\GoodsPriceNodeBase;
use app\frontend\modules\orderGoods\OrderGoodsCouponPriceNode;
use app\frontend\modules\orderGoods\OrderGoodsDeductionPriceNode;
use app\frontend\modules\orderGoods\OrderGoodsDiscountPriceNode;
use app\frontend\models\orderGoods\PreOrderGoodsDiscount;
use app\frontend\modules\orderGoods\OrderGoodsTaxfeeNode;
use app\frontend\modules\orderGoods\price\adapter\GoodsAdapterManager;
use app\frontend\modules\orderGoods\taxFee\BaseTaxFee;

abstract class BaseOrderGoodsPrice extends OrderGoodsPrice
{
    use PriceNodeTrait;

    /**
     * @var float
     */
    private $deductionAmount;
    private $deductionCount;
    /**
     * @var float
     */
    private $paymentAmount;

    /**
     * @var float
     */
    private $price;


    protected $priceClass;
    private $deductionWeight = 0;

    public function _getPriceNodes()
    {
        // 订单节点
        $nodes = collect([
            new GoodsPriceNodeBase($this, 1000)
        ]);

        // 订单优惠的节点
        $discountNodes = $this->orderGoods->getDiscounts()->map(function (BaseDiscount $discount) {
            return new OrderGoodsDiscountPriceNode($this, $discount);
        });

//        foreach (\app\common\modules\shop\ShopConfig::current()->get('shop-foundation.goods-discount') as $configItem) {
//            $discountNodes->push(new OrderGoodsDiscountPriceNode($this, call_user_func($configItem['class'],  $this->orderGoods), $configItem['weight']));
//        }

        // 订单抵扣节点（!!!不要在循环中自增权重，设置为相同的权重是为，getPriceBeforeByWeight方法更容易编写）
        $deductionNodes = $this->orderGoods->getOrderGoodsDeductions()->map(function (PreOrderGoodsDeduction $preOrderGoodsDeduction) {
            return new OrderGoodsDeductionPriceNode($this, $preOrderGoodsDeduction, 2200);

        });

        $taxNodes = $this->orderGoods->getTaxFees()->map(function (BaseTaxFee $taxFee) {
            return new OrderGoodsTaxfeeNode($this, $taxFee, 2300);
        });

        // 按照weight排序
        return $nodes->merge($discountNodes)->merge($deductionNodes)->merge($taxNodes)->sortBy(function (PriceNode $priceNode) {
            return $priceNode->getWeight();
        })->values();
    }

    /**
     * @return mixed
     */
    abstract protected function goods();

    abstract protected function aGoodsPrice();

    /**
     * 成交价
     * @return mixed
     */
    public function getPrice()
    {

        if (isset($this->price)) {
            return $this->price;
        }
        if ($this->isCoinExchange()) {
            return 0;
        }


        // 商品销售价 - 等级优惠金额
        $this->price = $this->getGoodsPrice();

        $this->price =  max($this->price, 0);

        return $this->price;
    }

    //todo blank 商品价格适配器
    public function goodsPriceManager()
    {
        if (isset($this->priceClass)) {
            return $this->priceClass;
        }
        $this->priceClass = GoodsAdapterManager::preOrderGoods($this->orderGoods);

        return $this->priceClass;
    }

    public function getVipPrice()
    {
        if ($this->isCoinExchange()) {
            return 0;
        }
        return $this->getGoodsPrice() - $this->getMemberLevelDiscountAmount();
    }

    private $isCoinExchange;

    /**
     * @return bool
     */
    private function isCoinExchange()
    {

        if (!isset($this->isCoinExchange)) {

            //blank not deduction
            if ($this->orderGoods->order->isDeductionDisable()) {
                $this->isCoinExchange = false;
                return $this->isCoinExchange;
            }

            if (!$this->orderGoods->goods->hasOneSale->has_all_point_deduct) {
                $this->isCoinExchange = false;
            } else {
                $this->isCoinExchange = true;

                $relations = collect(\app\common\modules\shop\ShopConfig::current()->get('shop-foundation.coin-exchange'))->sortBy('weight');
                foreach ($relations as $configItem) {
                    $coinExchange = call_user_func($configItem['class'], $this);
                    if(!$coinExchange->validate()){
                        continue;
                    }
                    $orderCoinExchange = $coinExchange->setLog();
                    //todo 权重最大的过了直接断循环
                    break;
                }

                if(empty($orderCoinExchange)){
                    //获取商城设置: 判断 积分、余额 是否有自定义名称
                    $shopSet = \Setting::get('shop.shop');

                    // 优惠记录
                    $preOrderGoodsDiscount = new PreOrderGoodsDiscount([
                        'discount_code' => 'coinExchange',
                        'amount' => $this->getGoodsPrice() ?: 0,
                        'name' => $shopSet['credit1'] ? $shopSet['credit1'] . '全额抵扣' : '积分全额抵扣',
                    ]);
                    $preOrderGoodsDiscount->setOrderGoods($this->orderGoods);
                    // 全额抵扣记录
                    $orderGoodsCoinExchange = new PreOrderGoodsCoinExchange([
                        'code' => 'point',
                        'amount' => $this->getGoodsPrice() ?: 0,
                        'coin' => $this->orderGoods->goods->hasOneSale->all_point_deduct * $this->orderGoods->total,
                        'name' => $shopSet['credit1'] ? $shopSet['credit1'] . '全额抵扣' : '积分全额抵扣',
                    ]);
                    $orderGoodsCoinExchange->setOrderGoods($this->orderGoods);
                    $orderCoinExchange = new PreOrderCoinExchange([
                        'code' => 'point',
                        'amount' => $this->getGoodsPrice() ?: 0,
                        'coin' => $this->orderGoods->goods->hasOneSale->all_point_deduct * $this->orderGoods->total,
                        'name' => $shopSet['credit1'] ? $shopSet['credit1'] . '全额' : '积分全额',
                        'uid' => $this->orderGoods->uid,
                    ]);
                }

                $this->orderGoods->order->getOrderCoinExchanges()->addAndGroupByCode($orderCoinExchange);
            }
        }
        return $this->isCoinExchange;
    }

    /**
     * 获取订单商品支付金额
     * @return float|mixed
     * @throws \app\common\exceptions\AppException
     */
    public function getPaymentAmount()
    {
        if (!isset($this->paymentAmount)) {
            $this->paymentAmount = $this->getPriceAfter($this->getPriceNodes()->last()->getKey());
            $this->paymentAmount = max($this->paymentAmount, 0);

        }
        return $this->paymentAmount;
    }


    /**
     * 销售价(商品的原销售价)
     * @return mixed
     */
    public function getGoodsPrice()
    {
        return $this->aGoodsPrice() * $this->orderGoods->total;
    }

    /**
     * 成本价
     * @return mixed
     */
    public function getGoodsCostPrice()
    {
        return $this->goods()->cost_price * $this->orderGoods->total;
    }

    /**
     * 市场价
     * @return mixed
     */
    public function getGoodsMarketPrice()
    {
        return $this->goods()->market_price * $this->orderGoods->total;
    }


    /**
     * 优惠券价
     * @return int
     */
    public function getCouponAmount()
    {
        if (!isset($this->orderGoods->coupons)) {
            return 0;
        }

        return $this->orderGoods->coupons->sum('amount');
    }

    /**
     * 获取订单商品抵扣金额
     * @return float
     */
    public function getDeductionAmount()
    {

        if ($this->deductionCount != $this->orderGoods->getOrderGoodsDeductions()->count()) {
            $this->deductionCount = $this->orderGoods->getOrderGoodsDeductions()->count();
            trace_log()->deduction('订单商品计算者', "订单商品计算所有已用的抵扣金额");
            $this->deductionAmount = $this->orderGoods->getOrderGoodsDeductions()->getUsedPoint()->getMoney();

        }
        return $this->deductionAmount;
    }

    protected $vipDiscountAmount;
    protected $vipDiscountLog;

    public function getMemberLevelDiscountAmount()
    {
        if (!isset($this->vipDiscountAmount)) {
            $this->vipDiscountAmount = $this->_getVipDiscountAmount($this->goodsPriceManager());
            $this->vipDiscountLog = $this->goods()->vipDiscountLog;
        }
        return $this->vipDiscountAmount;
    }
    public function getVipDiscountLog()
    {
        if (!isset($this->vipDiscountLog)) {
            $this->getMemberLevelDiscountAmount();
        }
        return $this->vipDiscountLog;

    }

    /**
     * 商品的会员等级折扣金额
     * @return mixed
     */
    protected function _getVipDiscountAmount($price)
    {

        return $this->goods()->getVipDiscountAmount($price) * $this->orderGoods->total;
    }

    /**
     * 不可用
     * 商品的会员等级折扣金额(缓存)
     * @return mixed
     */
    public function getVipDiscountAmount($price)
    {

        return 0;

        if (!isset($this->vipDiscountAmount)) {
            $this->vipDiscountAmount = $this->_getVipDiscountAmount($price);
            if ($this->vipDiscountAmount) {
                $preOrderGoodsDiscount = new PreOrderGoodsDiscount([
                    'discount_code' => $this->goods()->vipDiscountLog->code,
                    'amount' => $this->vipDiscountAmount ?: 0,
                    'name' => $this->goods()->vipDiscountLog->name,
                ]);
                $preOrderGoodsDiscount->setOrderGoods($this->orderGoods);
            }
        }
        return $this->vipDiscountAmount;
    }

}