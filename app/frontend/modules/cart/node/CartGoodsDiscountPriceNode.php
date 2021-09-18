<?php
/**
 * Created by PhpStorm.
 * Name: 芸众商城系统
 * Author: 广州市芸众信息科技有限公司
 * Profile: 广州市芸众信息科技有限公司位于国际商贸中心的广州，专注于移动电子商务生态系统打造，拥有芸众社交电商系统、区块链数字资产管理系统、供应链管理系统、电子合同等产品/服务。官网 ：www.yunzmall.com  www.yunzshop.com
 * Date: 2021/4/29
 * Time: 16:28
 */

namespace app\frontend\modules\cart\node;


use app\frontend\modules\cart\discount\BaseCartDiscount;
use app\frontend\modules\cart\models\CartGoods;
use app\frontend\modules\order\PriceNode;

class CartGoodsDiscountPriceNode extends PriceNode
{
    private $discount;

    private $cartGoods;

    public function __construct(CartGoods $cartGoods,BaseCartDiscount $discount, $weight)
    {
        $this->cartGoods = $cartGoods;

        $this->discount = $discount;

        parent::__construct($weight);
    }

    public function getKey()
    {
        return $this->discount->getCode();
    }

    /**
     * @return float|int|mixed
     * @throws \app\common\exceptions\AppException
     */
    public function getPrice()
    {

        if ($this->cartGoods->isChecked()) {
            return $this->cartGoods->getPriceBefore($this->getKey()) - $this->discount->getAmount();
        } else {
            return $this->cartGoods->getPriceBefore($this->getKey());
        }
    }
}