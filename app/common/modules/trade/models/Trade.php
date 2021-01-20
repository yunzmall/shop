<?php
/**
 * Created by PhpStorm.
 * User: shenyang
 * Date: 2018/11/13
 * Time: 5:07 PM
 */

namespace app\common\modules\trade\models;

use app\common\events\order\AfterTradeCreatedEvent;
use app\common\events\order\AfterTradeCreatingEvent;
use app\common\models\BaseModel;
use app\common\modules\memberCart\MemberCartCollection;
use app\common\modules\order\OrderCollection;
use app\framework\Http\Request;
use app\frontend\models\Member;
use app\frontend\modules\order\models\PreOrder;
use Illuminate\Support\Facades\DB;

/**
 * Class Trade
 * @package app\common\modules\trade\models
 * @property OrderCollection orders
 * @property TradeDiscount discount
 * @property float total_deduction_price
 * @property float total_discount_price
 * @property float total_dispatch_price
 * @property float total_goods_price
 * @property float total_price
 */
class Trade extends BaseModel
{
    /**
     * @var MemberCartCollection
     */
    private $memberCartCollection;
    /**
     * @var Member
     */
    private $member;
    /**
     * @var Request
     */
    private $request;

    public function init(MemberCartCollection $memberCartCollection, $member = null, $request = null)
    {
        $this->request = $request ?: request();
        $this->memberCartCollection = $memberCartCollection;
        $this->member = $member;
        event(new AfterTradeCreatingEvent($this));
        $this->setRelation('orders', $this->getOrderCollection($memberCartCollection, $member, $this->request));
        $this->setRelation('discount', $this->getDiscount());
        $this->setRelation('dispatch', $this->getDispatch());
        $this->amount_items = $this->getAmountItems();
        $this->discount_amount_items = $this->getDiscountAmountItems();
        $this->fee_items = $this->getFeeItems();
        $this->service_fee_items = $this->getServiceFeeItems();
        $this->total_price = $this->orders->sum('price');
        event(new AfterTradeCreatedEvent($this));

    }

    public function getMemberCartCollection()
    {
        return $this->memberCartCollection;
    }

    public function getServiceFeeItems()
    {
        // 按照code合并
        $orderFeesItems = [];
        foreach ($this->orders as $order) {
            foreach ($order->orderServiceFees as $orderServiceFee) {
                if ($orderServiceFee->checked) {
                    if (isset($orderFeesItems[$orderServiceFee['code']])) {
                        $orderFeesItems[$orderServiceFee['code']]['amount'] += $orderServiceFee['amount'];
                    } else {
                        $orderFeesItems[$orderServiceFee['code']] = [
                            'code' => $orderServiceFee['code'],
                            'name' => $orderServiceFee['name'],
                            'amount' => $orderServiceFee['amount'],
                        ];
                    }
                }
            }
        }
        return array_values($orderFeesItems);
    }

    public function getFeeItems()
    {
        // 按照code合并
        $orderFeesItems = [];
        foreach ($this->orders as $order) {
            foreach ($order->orderFees as $orderFee) {
                if (isset($orderFeesItems[$orderFee['fee_code']])) {
                    $orderFeesItems[$orderFee['fee_code']]['amount'] += $orderFee['amount'];
                } else {
                    $orderFeesItems[$orderFee['fee_code']] = [
                        'code' => $orderFee['fee_code'],
                        'name' => $orderFee['name'],
                        'amount' => $orderFee['amount'],
                    ];
                }
            }
        }
        foreach ($orderFeesItems as &$item) {
            $item['amount'] = sprintf('%.2f', $item['amount']);
        }
        return array_values($orderFeesItems);
    }

    private function getAmountItems()
    {
        $items = [
            [
                'code' => 'total_goods_price',
                'name' => '订单总金额',
                'amount' => $this->orders->sum('order_goods_price'),
            ], [
                'code' => 'total_dispatch_price',
                'name' => '总运费',
                'amount' => $this->orders->sum('dispatch_price'),
            ]
        ];
        if ($this->orders->sum('deduction_price')) {
            $items[] = [
                'code' => 'total_deduction_price',
                'name' => '总抵扣',
                'amount' => $this->orders->sum('deduction_price'),
            ];
        }

        return $items;
    }

    /**
     * @return mixed
     */
    private function getDiscountAmountItems()
    {
        // 按照code合并
        $orderDiscountsItems = [];
        foreach ($this->orders as $order) {
            foreach ($order->orderDiscounts as $orderDiscount) {
                if (isset($orderDiscountsItems[$orderDiscount['discount_code']])) {
                    $orderDiscountsItems[$orderDiscount['discount_code']]['amount'] += $orderDiscount['amount'];
                } else {
                    if ($orderDiscount['amount'] > 0) {
                        $orderDiscountsItems[$orderDiscount['discount_code']] = [
                            'code' => $orderDiscount['discount_code'],
                            'name' => $orderDiscount['name'],
                            'amount' => $orderDiscount['amount'],
                        ];
                    }
                }
            }
        }
        foreach ($orderDiscountsItems as &$item) {
            $item['amount'] = sprintf('%.2f', $item['amount']);
        }
        return array_values($orderDiscountsItems);
    }

    /**
     * 显示订单数据
     * @return array
     */
    public function toArray()
    {
        $attributes = parent::toArray();
        $attributes = $this->formatAmountAttributes($attributes);
        return $attributes;
    }

    private function getOrderCollection(MemberCartCollection $memberCartCollection, $member = null, $request = null)
    {
        // 按插件分组
        $groups = $memberCartCollection->groupByGroupId()->values();
        // 分组下单
        $orderCollection = $groups->map(function (MemberCartCollection $memberCartCollection) use ($member, $request) {
            return $memberCartCollection->getOrder($memberCartCollection->getPlugin(), $member, $request);
        });
        return app('OrderManager')->make(OrderCollection::class,$orderCollection->all());
    }

    /**
     * @return TradeDiscount
     */
    private function getDiscount()
    {
        $tradeDiscount = new TradeDiscount();
        $tradeDiscount->init($this);
        return $tradeDiscount;
    }

    private function getDispatch()
    {
        $tradeDispatch = new TradeDispatch();
        $tradeDispatch->init($this);
        return $tradeDispatch;
    }

    public function generate()
    {
        DB::transaction(function () {
            return $this->orders->map(function (PreOrder $order) {
                /**
                 * @var $order
                 */
                $order->generate();
                $order->fireCreatedEvent();
            });
        });
    }
}