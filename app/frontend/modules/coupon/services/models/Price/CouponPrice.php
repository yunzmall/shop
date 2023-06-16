<?php
/**
 * Created by PhpStorm.
 * Author:
 * Date: 2017/3/25
 * Time: 下午5:14
 */

namespace app\frontend\modules\coupon\services\models\Price;


use app\common\models\coupon\GoodsMemberCoupon;
use app\frontend\modules\coupon\services\models\Coupon;
use app\common\modules\orderGoods\models\PreOrderGoods;
use app\frontend\modules\orderGoods\models\PreOrderGoodsCollection;
use app\frontend\modules\order\models\PreOrder;

abstract class CouponPrice
{
    protected $isSet = false;
    protected $amount;
    /**
     * 优惠券数据库model
     * @var \app\common\models\Coupon
     */
    protected $dbCoupon;
    /**
     * @var Coupon
     */
    protected $coupon;
    /**
     * @var PreOrder
     */
    protected $orderModel;
    /**
     * @var PreOrderGoodsCollection
     */
    protected $orderGoodsModelGroup;

    public function __construct(Coupon $coupon)
    {
        $this->coupon = $coupon;
        $this->dbCoupon = $coupon->getMemberCoupon()->belongsToCoupon;
        $this->orderModel = $coupon->getPreOrder();
        //dd($this->orderModel);
    }

    /**
     * 有效的
     * @return bool
     */
    public function valid()
    {
        // 商品价格中未使用优惠的金额 不小于 满减额度
        $unusedEnoughMoney = $this->getOrderGoodsCollectionUnusedEnoughMoney();
        if (!float_lesser($unusedEnoughMoney, $this->dbCoupon->enough)) {
            return true;
        }
        trace_log()->coupon("优惠券{$this->dbCoupon->id}","不满足额度({$unusedEnoughMoney}<{$this->dbCoupon->enough})");
        return false;
    }

    /**
     * 有效的
     * @return bool
     */
    public function isOptional()
    {
        $orderGoodsCollectionPrice = $this->getOrderGoodsCollectionPrice();
        // 商品价格 不小于 满减额度
        if (!float_lesser($orderGoodsCollectionPrice, $this->dbCoupon->enough)) {
            return true;
        }
        trace_log()->coupon("优惠券{$this->dbCoupon->id}","不满足额度({$orderGoodsCollectionPrice}<{$this->dbCoupon->enough})");

        return false;
    }

    /**
     * 累加所有商品未使用优惠的金额
     * @return mixed
     */
    protected function getOrderGoodsCollectionUnusedEnoughMoney()
    {

        //todo 这里为什么要累加已使用优惠券使用条件的金额，再拿订单商品优惠券节点前金额减去。
        //理解为：防止优惠券选择优先问题，原因是会员可以先用使用条件较大的优惠券再用小的
        //判断一张优惠券是否可以都需要先减去已使用优惠券条件金额，再去对比是否满足
        $enough = $this->coupon->getOrderGoodsInScope()->sum(function ($orderGoods) {
            if (!isset($orderGoods->coupons)) {
                return 0;
            }
            return $orderGoods->coupons->sum('enough');
        });
        return $this->getOrderGoodsCollectionPrice() - $enough;
    }
    public function getPrice(){
        if(!isset($this->amount)){
            $this->amount = $this->_getAmount();
        }
        return $this->amount;
    }
    /**
     * 订单获取优惠券 金额
     * @return mixed
     */
    abstract protected function _getAmount();
    /**
     * 累加所有商品会员价
     * @return int
     */
    protected function getOrderGoodsCollectionPrice()
    {
        return $this->coupon->getOrderGoodsInScope()->sum(function (PreOrderGoods $orderGoods) {
            return $orderGoods->getPriceBefore('coupon');
        });
    }
    /**
     * 累加所有商品支付金额
     * @return int
     */
    protected function getOrderGoodsCollectionPaymentAmount()
    {
        return $this->coupon->getOrderGoodsInScope()->sum(function (PreOrderGoods $preOrderGoods){
            return $preOrderGoods->getPriceBefore('coupon');
        });
    }

    protected function getExchangeOrderGoodsCollectionPayment()
    {
        $goodsModel = $this->coupon->getOrderGoodsInScope()->first();

       return bcdiv($goodsModel->getPriceBefore('coupon'),$goodsModel->total,2) ;
    }
    /**
     * 分配优惠金额 立减折扣券使用 商品折扣后价格计算
     */
    public function setOrderGoodsDiscountPrice()
    {
        if($this->isSet){
            return;
        }
        $this->coupon->getOrderGoodsInScope()->map(function ($orderGoods) {
            /**
             * @var $orderGoods PreOrderGoods
             */

            $goodsMemberCoupon = new GoodsMemberCoupon();

            $goodsMemberCoupon->amount = $orderGoods->getPriceBefore('coupon') / $this->getOrderGoodsCollectionPaymentAmount() * $this->getPrice();
            $goodsMemberCoupon->enough = $orderGoods->getPriceBefore('coupon') / $this->getOrderGoodsCollectionPaymentAmount() * $this->dbCoupon->enough;
            //todo 需要按照订单方式修改
            if (!isset($orderGoods->coupons)) {
                $orderGoods->coupons = collect();
            }

            $orderGoods->coupons->push($goodsMemberCoupon);

        });
        $this->isSet = true;
    }
}