<?php
/**
 * Created by PhpStorm.
 * Name: 芸众商城系统
 * Author: 广州市芸众信息科技有限公司
 * Profile: 广州市芸众信息科技有限公司位于国际商贸中心的广州，专注于移动电子商务生态系统打造，拥有芸众社交电商系统、区块链数字资产管理系统、供应链管理系统、电子合同等产品/服务。官网 ：www.yunzmall.com  www.yunzshop.com
 * Date: 2021/4/29
 * Time: 14:07
 */

namespace app\frontend\modules\cart\discount;


use app\frontend\modules\cart\models\CartGoods;

class EnoughReduceDiscount extends BaseCartDiscount
{
    protected $code = 'enoughReduce';
    protected $name = '全场满减优惠';

    /**
     * @return float|int
     * @throws \app\common\exceptions\AppException
     */
    protected function _getAmount()
    {
        if ($this->getShopGoodsPrice() == 0) {
            return 0;
        }
        // (订单商品成交金额/订单中同种商品总成交金额 ) * 订单单品满减金额
        return ($this->cartGoods->getPriceBefore($this->getCode()) / $this->getShopGoodsPrice()) * $this->enoughReduce();
    }

    /**
     * @return int|mixed
     * @throws \app\common\exceptions\AppException
     */
    protected function enoughReduce()
    {

        //只有商城订单参加
        if($this->cartGoods->goods()->plugin_id != 0) {
            return 0;
        }


        $shopDiscountAmount =  $this->cartGoods->getShop()->getDiscountAmount($this->getShopGoodsPrice());

        return $shopDiscountAmount;
        //return $this->cartGoods->shop->getDiscount()->getAmountByCode($this->code)->getAmount();
    }

    /**
     * 店铺中能够进行全场满减优惠的商品的价格总和
     * @return float
     */
    protected function getShopGoodsPrice()
    {
        return $this->cartGoods->shop->carts->sum(function (CartGoods $preCartGoods) {
            if ($preCartGoods->isChecked() && $preCartGoods->goods()->plugin_id == 0) {
                return $preCartGoods->getPriceBefore($this->getCode());

            }
            return 0;
//            return $preCartGoods->getPriceBefore($this->getCode());
        });
    }
}