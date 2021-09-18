<?php
/**
 * Created by PhpStorm.
 * Author: 芸众商城 www.yunzshop.com
 * Date: 2017/3/15
 * Time: 下午4:29
 */

namespace app\frontend\modules\order;

use app\common\helpers\Serializer;
use app\frontend\models\order\PreOrderDiscount;
use app\frontend\modules\order\discount\BaseDiscount;
use app\frontend\modules\order\discount\CouponDiscount;
use app\frontend\modules\order\models\PreOrder;
use app\frontend\modules\orderGoods\models\PreOrderGoods;
use Illuminate\Support\Collection;

class OrderDiscount
{
    public $orderCoupons;
    public $orderDiscounts;
    /**
     * @var Collection
     */
    private $discounts;
    /**
     * @var PreOrder
     */
    protected $order;

    /**
     * 优惠券类
     * @var CouponDiscount
     */

    public function __construct(PreOrder $order)
    {
        $this->order = $order;

        // 订单优惠券使用记录集合
        $this->orderCoupons = $order->newCollection();
        $order->setRelation('orderCoupons', $this->orderCoupons);
        // 订单优惠使用记录集合
        $this->orderDiscounts = $order->newCollection();
        $order->setRelation('orderDiscounts', $this->orderDiscounts);

    }

    public function getDiscounts()
    {
        //blank not discount
        if ($this->order->isDiscountDisable()) {
            return collect();
        }

        if (!isset($this->discounts)) {
            $this->discounts = collect();
            foreach (\app\common\modules\shop\ShopConfig::current()->get('shop-foundation.order-discount') as $configItem) {
                $this->discounts->put($configItem['key'], call_user_func($configItem['class'], $this->order));
            }
            //todo 加载顺序问题，这里只是注入订单优惠项还没计算，所以没有等级优惠金额
            //$this->setOrderDiscounts();

        }
        return $this->discounts;
    }

    public function getAmount()
    {
        //todo 想放到 OrderGoodsPriceNode 节点里的但节点会重复调用，这里只有获取一次
        $this->addGoodsDiscounts();
        return $this->getDiscounts()->sum(function (BaseDiscount $discount) {
            // 每一种订单优惠
            return $discount->getAmount();
        });
    }

    private function addGoodsDiscounts()
    {
        // 将所有订单商品的优惠
        $orderGoodsDiscounts = $this->order->orderGoods->reduce(function (Collection $result, PreOrderGoods $aOrderGoods) {
            return $result->merge($aOrderGoods->getOrderGoodsDiscounts());
        }, collect());

        $preOrderDiscount = collect([]);

        // 按每个种类的优惠分组 求金额的和
        $orderGoodsDiscounts->each(function ($orderGoodsDiscount) use ($preOrderDiscount) {
            // 新类型添加
            if ($this->order->orderDiscounts->where('discount_code', $orderGoodsDiscount->discount_code)->isEmpty()) {
                if ($preOrderDiscount->where('discount_code', $orderGoodsDiscount->discount_code)->isEmpty()) {
                    $preDiscount = new PreOrderDiscount([
                        'discount_code' => $orderGoodsDiscount->discount_code,
                        'amount' => $orderGoodsDiscount->amount,
                        'name' => $orderGoodsDiscount->name,
                        'no_show' => isset($orderGoodsDiscount->no_show)?$orderGoodsDiscount->no_show:0,
                    ]);
                    $preOrderDiscount->push($preDiscount);
                    return;
                }
                // 已存在的类型累加
                $preOrderDiscount->where('discount_code', $orderGoodsDiscount->discount_code)->first()->amount += $orderGoodsDiscount->amount;
            }

        });

        $preOrderDiscount->each(function (PreOrderDiscount $orderDiscount) {
            $orderDiscount->setOrder($this->order);
        });
    }

    private function setOrderDiscounts()
    {
        // 将所有订单商品的优惠
        $orderGoodsDiscounts = $this->order->orderGoods->reduce(function (Collection $result, PreOrderGoods $aOrderGoods) {
            return $result->merge($aOrderGoods->getOrderGoodsDiscounts());
        }, collect());

        // 按每个种类的优惠分组 求金额的和
        $orderGoodsDiscounts->each(function ($orderGoodsDiscount) {
            // 新类型添加
            if ($this->order->orderDiscounts->where('discount_code', $orderGoodsDiscount->discount_code)->isEmpty()) {
                $preOrderDiscount = new PreOrderDiscount([
                    'discount_code' => $orderGoodsDiscount->discount_code,
                    'amount' => $orderGoodsDiscount->amount,
                    'name' => $orderGoodsDiscount->name,

                ]);
                $preOrderDiscount->setOrder($this->order);
                return;
            }
            // 已存在的类型累加
            $this->order->orderDiscounts->where('discount_code', $orderGoodsDiscount->discount_code)->first()->amount += $orderGoodsDiscount->amount;
        });
    }

    /**
     * @param $code
     * @return BaseDiscount
     */
    public function getAmountByCode($code)
    {
        return $this->discounts[$code];
    }
}