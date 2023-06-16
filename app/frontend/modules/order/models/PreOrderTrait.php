<?php
/**
 * Created by PhpStorm.
 * User: shenyang
 * Date: 2018/8/13
 * Time: 下午4:14
 */

namespace app\frontend\modules\order\models;


use app\common\events\order\BeforeOrderCreateEvent;
use app\common\exceptions\AppException;
use app\frontend\modules\orderGoods\models\PreOrderGoodsCollection;

/**
 * Trait PreOrderTrait
 * @package app\frontend\modules\order\models
 * @property PreOrderGoodsCollection orderGoods
 */
trait PreOrderTrait
{
    /**
     * 订单插入数据库,触发订单生成事件
     * @return mixed
     * @throws AppException
     */
    public function generate()
    {
        event(new BeforeOrderCreateEvent($this));
        $this->beforeSaving();
        $this->save();
        $this->afterSaving();
        $result = $this->push();

        if ($result === false) {

            throw new AppException('订单相关信息保存失败');
        }
        return $this->id;
    }


    /**
     * 统计商品总数
     * @return int
     */
    protected function getGoodsTotal()
    {
        //累加所有商品数量
        $result = $this->orderGoods->sum(function ($aOrderGoods) {
            return $aOrderGoods->total;
        });

        return $result;
    }

    /**
     * 统计订单商品成交金额
     * @return int
     */
    public function getOrderGoodsPrice()
    {
        return $this->goods_price = $this->orderGoods->getPrice();
    }

    /**
     * 统计订单商品会员价金额
     * @return int
     */
    public function getVipOrderGoodsPrice()
    {
        //订单禁用优惠返回，商品现价
        if ($this->isDiscountDisable()) {
            return $this->orderGoods->getPrice();
        }

        return $this->orderGoods->getVipPrice();
    }


    /**
     * 统计订单商品原价
     * @return int
     */
    public function getGoodsPrice()
    {
        return $this->orderGoods->getGoodsPrice();
    }

    public function getPriceAttribute()
    {
        return $this->getPrice();
    }

    public function getDispatchPriceAttribute()
    {
        return $this->getDispatchAmount();
    }
}