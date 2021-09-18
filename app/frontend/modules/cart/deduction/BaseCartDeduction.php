<?php
/**
 * Created by PhpStorm.
 * Name: 芸众商城系统
 * Author: 广州市芸众信息科技有限公司
 * Profile: 广州市芸众信息科技有限公司位于国际商贸中心的广州，专注于移动电子商务生态系统打造，拥有芸众社交电商系统、区块链数字资产管理系统、供应链管理系统、电子合同等产品/服务。官网 ：www.yunzmall.com  www.yunzshop.com
 * Date: 2021/5/25
 * Time: 18:44
 */

namespace app\frontend\modules\cart\deduction;

use app\frontend\modules\cart\deduction\models\PreCartGoodsDeduction;
use app\frontend\modules\cart\models\CartGoods;

abstract class BaseCartDeduction
{
    /**
     * @var CartGoods
     */
    protected $cartGoods;
    /**
     * 优惠名
     * @var string
     */
    protected $name;
    /**
     * 优惠码
     * @var
     */
    protected $code;
    /**
     * @var float
     */
    private $amount;


    protected $weight = 0;

    public function __construct(CartGoods $cartGoods)
    {
        $this->cartGoods = $cartGoods;
    }

    public function setWeight($weight)
    {
        $this->weight = $weight;
    }

    public function getWeight()
    {
        return $this->weight;

    }

    public function getCode()
    {
        return $this->code;
    }

    public function getName()
    {
        return $this->name;
    }

    /**
     * 获取总金额
     * @return float|int
     */
    public function getAmount()
    {
        if (isset($this->amount)) {
            return $this->amount;
        }

        $this->amount = $this->_getAmount();
        if ($this->amount) {
            $preModel = new PreCartGoodsDeduction([
                'code' => $this->code,
                'amount' => $this->amount ?: 0,
                'name' => $this->getName(),
            ]);
            $preModel->setCartGoods($this->cartGoods);
        }


        return $this->amount ?: 0;
    }


    /**
     * @return float
     */
    abstract protected function _getAmount();

}