<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/8/20
 * Time: 16:55
 */

namespace app\frontend\modules\order\taxFee;


use app\common\modules\shop\ShopConfig;
use Illuminate\Support\Collection;
use app\frontend\models\order\PreOrderTaxFee;
use app\frontend\modules\order\models\PreOrder;
use app\frontend\modules\orderGoods\models\PreOrderGoods;

class OrderTaxFeeManager
{
    public $orderTaxFee;
    /**
     * @var PreOrder
     */
    protected $order;

    protected $taxFee;

    /**
     * OrderFee constructor.
     * @param PreOrder $order
     */
    public function __construct(PreOrder $order)
    {
        $this->order = $order;

        // 订单含税费集合
        $order->setRelation('orderTaxFees', new Collection());

    }

    public function getOrderTaxFees()
    {
        if (!isset($this->orderTaxFee)) {
            $orderTaxFees = $this->getEnableTaxFee()->map(function (BaseOrderTaxFee $taxFee) {
                $orderServiceFee = new PreOrderTaxFee();
                $orderServiceFee->init($taxFee, $this->order);
                return $orderServiceFee;
            });
            $this->orderTaxFee = $orderTaxFees;
        }
        return $this->orderTaxFee;
    }

    public function getEnableTaxFee()
    {
        if (!isset($this->taxFee)) {
            $this->taxFee = collect();
            $configs = ShopConfig::current()->get('shop-foundation.order-tax-fee');
            foreach ($configs as $configItem) {
                $class = call_user_func($configItem['class'], $this->order);
                if ($class->enable()) {
                    $this->taxFee->put($configItem['key'], $class);
                }
            }
        }
        return $this->taxFee;
    }

    public function getAmount()
    {
        $this->addGoodsTaxFee();
        return $this->getOrderTaxFees()->sum(function (PreOrderTaxFee $orderTaxFee) {
            if ($orderTaxFee->getTaxFee()->isChecked()) {
                // 每一种含税费（可能是负数的优惠，也可能是正数的加钱,一切看配置）
                return $orderTaxFee->getTaxFee()->getAmount();
            }
            return 0;
        });
    }

    private function addGoodsTaxFee()
    {
        // 将所有订单商品的优惠
        $orderGoodsTaxFees = $this->order->orderGoods->reduce(function (Collection $result, PreOrderGoods $aOrderGoods) {
            return $result->merge($aOrderGoods->getOrderGoodsTaxFees());
        }, collect());

        $preOrderTaxFee = collect([]);

        // 按每个种类的优惠分组 求金额的和
        $orderGoodsTaxFees->each(function ($orderGoodsTaxFee) use ($preOrderTaxFee) {
            // 新类型添加
            if ($this->order->orderTaxFees->where('fee_code', $orderGoodsTaxFee->fee_code)->isEmpty()) {
                if ($preOrderTaxFee->where('discount_code', $orderGoodsTaxFee->fee_code)->isEmpty()) {
                    $preTaxFee = new PreOrderTaxFee([
                        'fee_code' => $orderGoodsTaxFee->fee_code,
                        'amount' => $orderGoodsTaxFee->amount,
                        'name' => $orderGoodsTaxFee->name,
                    ]);
                    $preOrderTaxFee->push($preTaxFee);
                    return;
                }
                // 已存在的类型累加
                $preOrderTaxFee->where('fee_code', $orderGoodsTaxFee->fee_code)->first()->amount += $orderGoodsTaxFee->amount;
            }

        });

        $preOrderTaxFee->each(function (PreOrderTaxFee $orderTaxFee) {
            $orderTaxFee->setOrder($this->order);
        });
    }

    /**
     * @param $code
     * @return BaseOrderTaxFee
     */
    public function getAmountByCode($code)
    {
        return $this->getEnableTaxFee()[$code];
    }
}