<?php
/**
 * Created by PhpStorm.
 * User: shenyang
 * Date: 2018/5/23
 * Time: 下午3:55
 */

namespace app\frontend\modules\orderGoods\coin_exchange;

use app\frontend\models\orderGoods\PreOrderGoodsDiscount;
use app\frontend\modules\order\models\PreOrder;
use app\frontend\models\order\PreOrderCoinExchange;
use app\frontend\models\orderGoods\PreOrderGoodsCoinExchange;
use app\frontend\modules\orderGoods\price\option\BaseOrderGoodsPrice;

abstract class BaseCoinExchange
{
    /**
     * @var BaseOrderGoodsPrice
     */
    protected $orderGoodsPrice;

    protected $shopSet;

    public function __construct(BaseOrderGoodsPrice $orderGoodsPrice)
    {
        $this->orderGoodsPrice = $orderGoodsPrice;

        //获取商城设置: 判断 积分、余额 是否有自定义名称
        $this->shopSet = \Setting::get('shop.shop');
    }

    public function setLog()
    {
        // 优惠记录
        $preOrderGoodsDiscount = new PreOrderGoodsDiscount([
            'discount_code' => 'coinExchange',
            'amount' => $this->orderGoodsPrice->getGoodsPrice() ?: 0,
            'name' => $this->shopSet['credit1'] ? $this->shopSet['credit1'] . '全额抵扣' : '积分全额抵扣',
        ]);
        $preOrderGoodsDiscount->setOrderGoods($this->orderGoodsPrice->orderGoods);
        // 全额抵扣记录
        $orderGoodsCoinExchange = new PreOrderGoodsCoinExchange([
            'code' => 'point',
            'amount' => $this->orderGoodsPrice->getGoodsPrice() ?: 0,
            'coin' => $this->orderGoodsPrice->orderGoods->goods->hasOneSale->all_point_deduct * $this->orderGoodsPrice->orderGoods->total,
            'name' => $this->shopSet['credit1'] ? $this->shopSet['credit1'] . '全额抵扣' : '积分全额抵扣',
        ]);
        $orderGoodsCoinExchange->setOrderGoods($this->orderGoodsPrice->orderGoods);
        $orderCoinExchange = new PreOrderCoinExchange([
            'code' => 'point',
            'amount' => $this->orderGoodsPrice->getGoodsPrice() ?: 0,
            'coin' => $this->orderGoodsPrice->orderGoods->goods->hasOneSale->all_point_deduct * $this->orderGoodsPrice->orderGoods->total,
            'name' => $this->shopSet['credit1'] ? $this->shopSet['credit1'] . '全额' : '积分全额',
            'uid' => $this->orderGoodsPrice->orderGoods->uid,
        ]);
        return $orderCoinExchange;
    }

    public function validate()
    {
        return true;
    }
}