<?php
/**
 * Created by PhpStorm.
 * User: shenyang
 * Date: 2018/5/23
 * Time: 下午3:55
 */

namespace app\frontend\modules\orderGoods\taxFee;

use app\frontend\models\orderGoods\PreOrderGoodsDiscount;
use app\frontend\models\orderGoods\PreOrderGoodsTaxFee;
use app\frontend\modules\order\models\PreOrder;
use app\common\modules\orderGoods\models\PreOrderGoods;

abstract class BaseTaxFee
{
    /**
     * @var PreOrder
     */
    protected $orderGoods;
    /**
     * 税费名
     * @var string
     */
    protected $name;
    /**
     * 税费码
     * @var
     */
    protected $code;
    /**
     * @var float
     */
    private $amount;
    protected $weight;

    public function __construct(PreOrderGoods $orderGoods)
    {
        $this->orderGoods = $orderGoods;
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
            // 将抵扣总金额保存在订单优惠信息表中
            $preOrderGoodsTaxFee = new PreOrderGoodsTaxFee([
                'uniacid' => \YunShop::app()->uniacid,
                'fee_code' => $this->getCode(),
                'amount' => $this->amount ?: 0,
                'name' => $this->getName(),
            ]);
            $preOrderGoodsTaxFee->setOrderGoods($this->orderGoods);
        }

        return $this->amount ?: 0;
    }

    /**
     * @return float
     */
    abstract protected function _getAmount();

}