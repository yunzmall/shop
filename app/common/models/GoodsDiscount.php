<?php
/**
 * Created by PhpStorm.
 * Author: 芸众商城 www.yunzshop.com
 * Date: 2017/4/27
 * Time: 下午1:58
 */

namespace app\common\models;
use app\frontend\modules\orderGoods\price\adapter\BaseGoodsPriceAdapter;


/**
 * Class GoodsDiscount
 * @package app\common\models
 * @property int discount_method
 * @property int discount_value
 */
class GoodsDiscount extends BaseModel
{
    public $table = 'yz_goods_discount';
    public $guarded = [];
    const MONEY_OFF = 1;//折扣
    const DISCOUNT = 2;//固定金额
    const COST_RATE = 3;//成本比例
    public $amount;

    /**
     * 开启商品独立优惠
     * @return bool
     */
    public function enable()
    {
        //设置了折扣方式 并且 设置了折扣值
        return $this->discount_method != 0 && $this->discount_value !== '';
    }

    /**
     * @param $price
     * @return int|mixed
     * @throws \app\common\exceptions\AppException
     */
    public function getAmount($price,$member = null)
    {

        if(array_key_exists('amount',$this->attributes)){
            return $this->amount;
        }
        if ($this->enable()) {
            $this->amount =  $this->getIndependentDiscountAmount($price);
        } else {
            $this->amount =  $this->getGlobalDiscountAmount($price,$member);
        }
        return $this->amount;
    }

    /**
     * @param BaseGoodsPriceAdapter $price
     * @param \app\common\models\MemberLevel $nextLevel
     */
    public function getNextAmount($price,$nextLevel)
    {
        if(array_key_exists('amount',$this->attributes)){
            return $this->amount;
        }
        if ($this->enable()) {
            return  $this->getIndependentDiscountAmount($price);
        } else {

            $price = $nextLevel->getDiscountCalculation($price);

            return round($price,2);

//            // 商品折扣 默认 10折
//            $discount = trim($nextLevel->discount);
//            $discount = ($discount == false) ? 10 : $discount;
//            // 折扣/10 得到折扣百分比
//            return round((1 - $discount / 10) * $price,2);
        }
    }

    /**
     * @param $price
     * @return int
     * @throws \app\common\exceptions\AppException
     */
    public function getGlobalDiscountAmount($price,$member = null)
    {
        //$member = \app\frontend\models\Member::current();
        if (!isset($member->yzMember->level)) {
            return 0;
        }
        return $member->yzMember->level->getMemberLevelGoodsDiscountAmount($price);
    }

    /**
     * 获取等级优惠金额
     * @param BaseGoodsPriceAdapter $price
     * @return int|mixed
     */
    public function getIndependentDiscountAmount($price)
    {
        //其次等级商品全局设置
        switch ($this->discount_method) {
            case self::DISCOUNT:
                $result = $this->getMoneyAmount();
                break;
            case self::MONEY_OFF:
                $result = $this->getMemberLevelGoodsPriceDiscountAmount($price->getDealPrice());
                break;
            case self::COST_RATE:
                $result = $this->getMemberLevelGoodsCostPriceDiscountAmount($price->getCostPrice());
                break;
            default:
                $result = $price;
                break;
        }
        return $result ? $result : 0;
    }

    /**
     * 商品独立等级立减后优惠金额
     * @return mixed
     */
    private function getMoneyAmount()
    {
        if ($this->discount_value == 0) {
            return 0;
        }
        return $this->discount_value;
    }



    /**
     * 商品独立等级折扣优惠金额
     * @param $price
     * @return mixed
     */
    private function getDiscountAmount($price)
    {

        if ($this->discount_value == 0) {
            return 0;
        }
        return $price * (1 - $this->discount_value / 10);
    }

    /**
     * @param BaseGoodsPriceAdapter $priceClass
     */
    public function getDiscountCalculation($priceClass)
    {
        //获取设置的计算方式
        $level_discount_calculation = \Setting::get('shop.member.level_discount_calculation');

        switch ($level_discount_calculation) {
            case 1:
                //取商品成本价
                $discountAmount =  $this->getMemberLevelGoodsCostPriceDiscountAmount($priceClass->getCostPrice());
                break;
            default:
                //为空为0,取商品现价
                $discountAmount = $this->getMemberLevelGoodsPriceDiscountAmount($priceClass->getPrice());
                break;
        }

        return max($discountAmount, 0);
    }

    protected function getMemberLevelGoodsPriceDiscountAmount($goodsPrice)
    {
        if ($this->discount_value == 0) {
            return 0;
        }
        return $goodsPrice * (1 - $this->discount_value / 10);

    }

    protected function getMemberLevelGoodsCostPriceDiscountAmount($goodsCostPrice)
    {
        if ($this->discount_value == 0) {
            return 0;
        }
        return $goodsCostPrice * ($this->discount_value / 100);

    }




    public function goods()
    {
        return $this->belongsTo(Goods::class);
    }
}