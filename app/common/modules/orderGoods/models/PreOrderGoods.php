<?php
/**
 * Created by PhpStorm.
 * User: shenyang
 * Date: 2018/11/26
 * Time: 3:54 PM
 */

namespace app\common\modules\orderGoods\models;

use app\backend\modules\goods\models\GoodsTradeSet;
use app\common\exceptions\AppException;
use app\common\models\OrderGoods;
use app\common\models\BaseModel;
use app\common\modules\shop\ShopConfig;
use app\frontend\models\Goods;
use app\frontend\models\goods\Sale;
use app\frontend\models\GoodsOption;
use app\frontend\modules\deduction\OrderGoodsDeductManager;
use app\frontend\modules\deduction\OrderGoodsDeductionCollection;
use app\frontend\modules\goods\services\TradeGoodsPointsServer;
use app\frontend\modules\orderGoods\price\option\NormalOrderGoodsOptionPrice;
use app\frontend\modules\orderGoods\price\option\NormalOrderGoodsPrice;
use app\frontend\modules\order\models\PreOrder;
use app\frontend\modules\orderGoods\taxFee\OrderGoodsTaxFeeManager;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Class PreOrderGoods
 * @package app\frontend\modules\orderGoods\models
 * @property float price
 * @property float goods_price
 * @property float coupon_price
 * @property float discount_price
 * @property float goods_cost_price
 * @property float goods_market_price
 * @property float $deduction_amount
 * @property float payment_amount
 * @property int goods_id
 * @property Goods goods
 * @property int id
 * @property int pre_id
 * @property int order_id
 * @property int uid
 * @property int total
 * @property int uniacid
 * @property int goods_option_id
 * @property string goods_option_title
 * @property string goods_sn
 * @property string thumb
 * @property int type
 * @property string title
 * @property GoodsOption goodsOption
 * @property OrderGoodsDeductionCollection orderGoodsDeductions
 * @property Collection orderGoodsDiscounts
 * @property Collection orderGoodsTaxFees
 * @property Sale sale
 */
class PreOrderGoods extends OrderGoods
{
    use PreOrderGoodsTrait;

    protected $hidden = ['goods', 'sale', 'belongsToGood', 'hasOneGoodsDispatch'];
    /**
     * @var PreOrder
     */
    public $order;
    /**
     * @var Collection
     */
    public $coupons;

    protected $appends = ['pre_id', 'points', 'show_time_word'];

    /**
     * @param $key
     * @return mixed
     * @throws AppException
     */
    public function getPriceBefore($key)
    {
        return $this->getPriceCalculator()->getPriceBefore($key);
    }

    /**
     * @param $key
     * @return mixed
     * @throws AppException
     */
    public function getPriceBeforeWeight($key)
    {
        return $this->getPriceCalculator()->getPriceBeforeWeight($key);
    }

    /**
     * @param $key
     * @return mixed
     * @throws AppException
     */
    public function getPriceAfter($key)
    {
        return $this->getPriceCalculator()->getPriceAfter($key);
    }

    /**
     * 为订单model提供的方法 ,设置所属的订单model
     * @param PreOrder $order
     */
    public function init(PreOrder $order)
    {
        $this->order = $order;
    }

    public function touchPreAttributes()
    {
        $this->uid = (int)$this->uid;
        $this->uniacid = (int)$this->uniacid;
        $this->goods_id = (int)$this->goods_id;
        $this->title = (string)$this->title;
        $this->thumb = (string)$this->thumb;
        $this->type = (int)$this->type;
        $this->goods_sn = (string)$this->goods_sn;
        $this->product_sn = (string)$this->product_sn;
        $this->goods_price = (string)$this->goods_price;
        $this->price = (float)$this->price;
        $this->goods_cost_price = (float)$this->goods_cost_price;
        $this->goods_market_price = (float)$this->goods_market_price;
        $this->coupon_price = (float)$this->coupon_price;
        $this->need_address = (float)$this->need_address;
        $this->payment_amount = round($this->getPaymentAmount(),2,PHP_ROUND_HALF_EVEN);
        $this->vip_price = $this->getVipPrice();
        if ($this->isOption()) {
            $this->goods_option_id = (int)$this->goods_option_id;
            $this->goods_option_title = (string)$this->goods_option_title;
            $this->goods_sn = $this->goodsOption->goods_sn ? (string)$this->goodsOption->goods_sn : $this->goods_sn;
            $this->product_sn = $this->goodsOption->product_sn ? (string)$this->goodsOption->product_sn : $this->product_sn;
        }
    }

    private function getShowTimeWord()
    {
        //商品交易设置
        $goods_trade_set = GoodsTradeSet::where('goods_id', $this->goods_id)->first();
        if (!$goods_trade_set || !$goods_trade_set->arrived_day || !app('plugins')->isEnabled('address-code')) {
            return '';
        } else {
            $arrived_day = $goods_trade_set->arrived_day;
            $arrived_word = $goods_trade_set->arrived_word;
            if ($arrived_day > 1) {
                $arrived_day -= 1;
                $time_format = Carbon::createFromTimestamp(time())->addDays($arrived_day)->format('Y-m-d');
            } else {
                $time_format = Carbon::createFromTimestamp(time())->format('Y-m-d');
            }
            $time_format .= " {$goods_trade_set->arrived_time}:00";
            $timestamp = strtotime($time_format);
            if ($timestamp < time()) {
                $timestamp += 86400;
            }
            $show_time = ltrim(date('m', $timestamp), '0').'月';
            $show_time .= ltrim(date('d', $timestamp), '0').'日';
            $show_time .= $goods_trade_set->arrived_time;
            return str_replace('[送达时间]', $show_time, $arrived_word);
        }
    }

    public function getUidAttribute()
    {
        return $this->order->uid;
    }

    public function getUniacidAttribute()
    {
        return $this->order->uniacid;

    }

    /**
     * @return PreOrder
     * @throws AppException
     */
    public function getOrder()
    {
        if (!isset($this->order)) {
            throw new AppException('调用顺序错误,Order对象还没有载入');
        }
        return $this->order;
    }

    public function getDiscounts()
    {
        //blank not discount
        if ($this->order->isDiscountDisable()) {
            return collect();
        }

        $discounts = collect();
        foreach (\app\common\modules\shop\ShopConfig::current()->get('shop-foundation.goods-discount') as $configItem) {
            $discount = call_user_func($configItem['class'], $this);
            $discount->setWeight($configItem['weight']);
            $discounts->push($discount);
        }
        return $discounts;
    }

    public function getOrderGoodsDiscounts()
    {
        if (!$this->getRelation('orderGoodsDiscounts')) {
            $this->setRelation('orderGoodsDiscounts', $this->newCollection());


        }
        return $this->orderGoodsDiscounts;
    }

    public function getTaxFees()
    {
        $taxFees = collect();
        foreach (\app\common\modules\shop\ShopConfig::current()->get('shop-foundation.goods-tax-fee') as $configItem) {
            $taxFee = call_user_func($configItem['class'], $this);
            $taxFee->setWeight($configItem['weight']);
            $taxFees->push($taxFee);
        }
        return $taxFees;
    }

    public function getOrderGoodsTaxFees()
    {
        if (!$this->getRelation('orderGoodsTaxFees')) {
            $this->setRelation('orderGoodsTaxFees', $this->newCollection());
        }
        return $this->orderGoodsTaxFees;
    }

    public function getOrderGoodsDeductions()
    {
        if (!$this->getRelation('orderGoodsDeductions')) {
            $preOrderGoodsDeduction = new OrderGoodsDeductManager($this);
            $this->setRelation('orderGoodsDeductions', $preOrderGoodsDeduction->getOrderGoodsDeductions());

        }
        return $this->orderGoodsDeductions;
    }

    public function getCouponPriceAttribute()
    {
        return $this->getCouponAmount();
    }

    /**
     * @throws \Exception
     */
    public function afterSaving()
    {
        foreach ($this->relations as $models) {
            $models = $models instanceof Collection
                ? $models->all() : [$models];

            foreach (array_filter($models) as $model) {
                /**
                 * @var BaseModel $model
                 */
                // 添加 order_goods_id 外键
                if (!isset($model->order_goods_id) && $model->hasColumn('order_goods_id')) {
                    $model->order_goods_id = $this->id;
                }
                // 添加 order_id 外键

                if (!isset($model->order_id) && $model->hasColumn('order_id')) {
                    $model->order_id = $this->order_id;
                }
            }

        }
        $this->push();
    }

    public function save(array $options = [])
    {
        if (isset($this->id)) {
            return true;
        }
        return parent::save($options);
    }

    private function loadConfigRelations()
    {
        $relations = ShopConfig::current()->get('shop-foundation.order-goods.relations');
        foreach ($relations as $relation) {

            $relationModel = call_user_func($relation['class'], []);
            $relationModel->setOrderGoods($this);
            if (!$relationModel->enable()) {
                continue;
            }
            $this->setRelation($relation['key'], $relationModel);
        }
    }

    public function toArray()
    {

        $this->touchPreAttributes();
        $this->loadConfigRelations();
        return parent::toArray();
    }

    public function beforeSaving()
    {
        $this->touchPreAttributes();
        $this->loadConfigRelations();
        $this->deduction_amount = round($this->getDeductionAmount(),2,PHP_ROUND_HALF_EVEN);
    }

    /**
     * @return mixed
     */
    public function getGoodsPriceAttribute()
    {
        return $this->getGoodsPrice();
    }

    /**
     * @return mixed
     */
    public function getGoodsCostPriceAttribute()
    {
        return $this->getGoodsCostPrice();
    }

    /**
     * @var NormalOrderGoodsPrice
     */
    protected $priceCalculator;


    /**
     * 设置价格计算者
     */
    public function _getPriceCalculator()
    {
        if ($this->isOption()) {
            $priceCalculator = new NormalOrderGoodsOptionPrice($this);

        } else {
            $priceCalculator = new NormalOrderGoodsPrice($this);
        }
        return $priceCalculator;
    }

    /**
     * 获取价格计算者
     * @return NormalOrderGoodsPrice
     */
    public function getPriceCalculator()
    {
        if (!isset($this->priceCalculator)) {
            $this->priceCalculator = $this->_getPriceCalculator();
        }
        return $this->priceCalculator;
    }

    public function getVipPrice()
    {
        //订单禁用优惠返回，商品现价
        if ($this->order->isDiscountDisable()) {
            return $this->getPrice();
        }

        return $this->getPriceCalculator()->getVipPrice();
    }

    /**
     * @return mixed
     */
    public function getVipDiscountAmount()
    {

        $result = $this->getPriceCalculator()->getMemberLevelDiscountAmount();

        return $result;

    }

    public function getVipDiscountLog($key = null)
    {
        if ($key) {
            return $this->getPriceCalculator()->getVipDiscountLog()?$this->getPriceCalculator()->getVipDiscountLog()->$key : null;
        }

        return $this->getPriceCalculator()->getVipDiscountLog();
    }



    /**
     * @return float|mixed
     * @throws AppException
     */
    public function getPaymentAmountAttribute()
    {
        return $this->getPaymentAmount();
    }

    /**
     * 均摊的支付金额
     * @return float|mixed
     * @throws AppException
     */
    public function getPaymentAmount()
    {
        return $this->getPriceCalculator()->getPaymentAmount();
    }

    /**
     * 抵扣金额
     * @return float
     */
    public function getDeductionAmount()
    {
        return $this->getPriceCalculator()->getDeductionAmount();

    }

    /**
     * 优惠券金额
     * @return int
     */
    public function getCouponAmount()
    {
        return $this->getPriceCalculator()->getCouponAmount();

    }

    /**
     * @return string
     */
    public function getPreIdAttribute()
    {
        return $this->goods_id . '-' . $this->goods_option_id;
    }

    /**
     * @return string
     */
    public function getPointsAttribute()
    {
        return $this->getPoints();
    }

    /**
     * @return string
     */
    public function getShowTimeWordAttribute()
    {
        return $this->getShowTimeWord();
    }

    /**
     * @param null $key
     * @return mixed
     */
    public function getParams($key = null)
    {
        $params = is_array($this->order->getRequest()->input('order_goods')) ? $this->order->getRequest()->input('order_goods') : json_decode($this->order->getRequest()->input('order_goods'), true);
        $result = collect($params ?: [])->where('pre_id', $this->pre_id)->first();
        if (isset($key)) {
            return $result[$key];
        }

        return $result;
    }

    /**
     * @description 获取积分
     * @return mixed|string
     */
    public function getPoints()
    {
        $tradeGoodsPointsServer = new TradeGoodsPointsServer();
        $tradeGoodsPointsServer->getPointSet($this->goods);

        if ($tradeGoodsPointsServer->close(TradeGoodsPointsServer::SINGLE_PAGE)) {
            return '';
        }
        $points = $tradeGoodsPointsServer->finalSetPoint();

        return $tradeGoodsPointsServer->getPoint($points, $this->price, $this->goods_cost_price);
    }
}