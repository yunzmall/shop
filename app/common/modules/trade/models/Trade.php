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
use app\common\models\DispatchType;
use app\common\models\order\OrderDeliver;
use app\common\models\order\OrderMergeCreate;
use app\common\modules\memberCart\MemberCartCollection;
use app\common\modules\order\OrderCollection;
use app\framework\Http\Request;
use app\frontend\models\Member;
use app\frontend\modules\order\models\PreOrder;
use Illuminate\Support\Facades\DB;
use Yunshop\PackageDelivery\models\DeliveryOrder;
use Yunshop\StoreCashier\common\models\SelfDelivery;

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
        $this->last_deliver_user_info = $this->getLastDeliverUserInfo();
        $this->amount_items = $this->getAmountItems();
        $this->discount_amount_items = $this->getDiscountAmountItems();
        $this->fee_items = $this->getFeeItems();
        $this->service_fee_items = $this->getServiceFeeItems();
        $this->tax_fee_items = $this->getTaxFeeItems();
        $this->total_price = $this->orders->sum('price');
        $this->is_diy_form_jump = \Setting::get('shop.order.is_diy_form_jump') ?: 0;
        $member = Member::current();
        $this->balance = $member->credit2 ?: 0;
        event(new AfterTradeCreatedEvent($this));
    }

    /**
     * 获取最后一次自提填写的信息
     * @return array
     */
    protected function getLastDeliverUserInfo()
    {
        $uid = \YunShop::app()->getMemberId();
        $dispatch_type_id = $this->request->dispatch_type_id;
        switch ($dispatch_type_id) {
            case DispatchType::PACKAGE_DELIVER :
            case DispatchType::STORE_PACKAGE_DELIVER :
                return $this->packageDeliverUserInfo($uid);
            case DispatchType::PACKAGE_DELIVERY:
                return $this->shopDeliverUserInfo($uid);
            default :
                return $this->storeDeliverUserInfo($uid);
        }
    }

    /**
     * 商城自提
     * @param $uid
     * @return array|null
     */
    private function shopDeliverUserInfo($uid)
    {
        $setting = \Setting::get('plugin.package_delivery');
        if ($setting['open_state']) {
            $delivery_order = DeliveryOrder::where('uid', $uid)
                ->orderBy('id', 'desc')
                ->first(['buyer_name', 'buyer_mobile']);
            if ($delivery_order) {
                return [
                    "realname" => $delivery_order->buyer_name,
                    "mobile" => $delivery_order->buyer_mobile,
                ];
            }
        }
        return null;
    }

    /**
     * 门店自提点用户最后的信息
     * @param $uid
     * @return array|null
     */
    private function storeDeliverUserInfo($uid)
    {
        $is_enabled = app('plugins')->isEnabled("store-cashier");

        //开启插件
        if ($is_enabled) {
            $order_deliver = SelfDelivery::where("uid", $uid)->orderBy('id', 'desc')->first(['member_mobile', 'member_realname']);
            if ($order_deliver) {
                return [
                    "realname" => $order_deliver->member_realname,
                    "mobile" => $order_deliver->member_mobile,
                ];
            }
        }
        return null;
    }

    /**
     * 自提点插件用户最后的信息
     * @param $uid
     * @return array|null
     */
    private function packageDeliverUserInfo($uid)
    {
        $is_enabled = app('plugins')->isEnabled("package-deliver");
        if ($is_enabled) {
            $order_deliver = OrderDeliver::where('uid', $uid)->orderBy('id', 'desc')->first(['order_id']);
            if ($order_deliver) {
                return [
                    "realname" => $order_deliver->hasOneOrderAddress->realname,
                    "mobile" => $order_deliver->hasOneOrderAddress->mobile,
                ];
            }
        }
        return null;
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

    public function getTaxFeeItems()
    {
        // 按照code合并
        $orderTaxFeesItems = [];
        foreach ($this->orders as $order) {
            foreach ($order->orderTaxFees as $orderTaxFee) {
                if ($orderTaxFee->checked) {
                    if (isset($orderTaxFeesItems[$orderTaxFee['code']])) {
                        $orderTaxFeesItems[$orderTaxFee['code']]['amount'] += $orderTaxFee['amount'];
                    } else {
                        $orderTaxFeesItems[$orderTaxFee['code']] = [
                            'code' => $orderTaxFee['code'],
                            'name' => $orderTaxFee['name'],
                            'amount' => $orderTaxFee['amount'],
                        ];
                    }
                }
            }
        }
        return array_values($orderTaxFeesItems);
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
                'name' => '总' . $this->deductionLang(),
                'amount' => $this->orders->sum('deduction_price'),
            ];
        }

        return $items;
    }

    private function getCoinExchanges()
    {
        $point = 0;
        $this->orders->map(function ($order) use (&$point) {
            $order->orderCoinExchanges->map(function ($coinExchange) use (&$point) {
                if (in_array($coinExchange->code, ['point'])) {
                    $point += $coinExchange->coin;
                }
            });
        });
        if (!$point) {
            return 0;
        }
        $this->orders->map(function ($order) use (&$point) {
            $order->orderDeductions->map(function ($deduction) use (&$point) {
                if (in_array($deduction->code, ['point']) && $deduction->checked) {
                    $point += $deduction->coin;
                }
            });
        });

        return $point;
    }

    private function coinExchangeLang()
    {
        $point_name = \Setting::get('shop.shop')['credit1'] ?: '积分';
        return $point_name;
    }

    private function deductionLang()
    {
        $langSetting = \Setting::get('shop.lang');

        return $langSetting[$langSetting['lang']]['order']['deduction_lang'] ?: "抵扣";
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
                    if ($orderDiscount['amount'] > 0 && !in_array($orderDiscount['discount_code'], ['coinExchange'])) {
                        $orderDiscountsItems[$orderDiscount['discount_code']] = [
                            'code' => $orderDiscount['discount_code'],
                            'name' => $orderDiscount['name'],
                            'amount' => $orderDiscount['amount'],
                            'no_show' => $orderDiscount['no_show'],
                        ];
                    }
                }
            }
        }
        foreach ($orderDiscountsItems as &$item) {
            $item['amount'] = sprintf('%.2f', $item['amount']);
        }

        if ($point = $this->getCoinExchanges()) {
            $orderDiscountsItems[] = [
                'code' => 'pointCoinExchanges',
                'name' => '总' . $this->coinExchangeLang(),
                'amount' => $point,
                'no_show' => 0,
            ];
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


        return app('OrderManager')->make(OrderCollection::class, $orderCollection->all());
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
            $this->orders->map(function (PreOrder $order) {
                /**
                 * @var $order
                 */
                $order->generate();
                $order->fireCreatedEvent();
            });
            OrderMergeCreate::saveData($this->orders->pluck('id')->implode(','));
            return $this->orders;
        });
    }
}
