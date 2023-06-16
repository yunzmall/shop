<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/8/18
 * Time: 11:45
 */

namespace app\frontend\modules\dispatch\freight;

use app\common\models\goods\GoodsDispatch;
use app\frontend\models\OrderGoods;
use app\frontend\modules\order\models\PreOrder;

class UnifyFreight
{
    protected $code = 'unify';

    protected $name = '统一运费';


    /**
     * @var PreOrder
     */
    protected $order;

    /**
     * 金额
     * @var float
     */
    protected $freightAmount;


    /*
     * 排序：数值越低权重越大
     */
    protected $weight;

    /**
     * BaseFreight constructor.
     * @param PreOrder $order
     * @param $weight
     */
    public function __construct(PreOrder $order, $weight = 0)
    {
        $this->order = $order;

        $this->weight = $weight;
    }



    /**
     * 返回运费金额
     * @return float|mixed
     */
    public function getAmount()
    {
        if (!isset($this->freightAmount)) {
            $this->freightAmount = $this->_getAmount();
        }
        return $this->freightAmount;
    }

    protected function _getAmount()
    {
        // 统一运费取所有商品统一运费的最大值
        $price = $this->order->orderGoods->unique('goods_id')->max(function ($orderGoods) {
            /**
             * @var $orderGoods OrderGoods
             */
            if($orderGoods->isFreeShipping())
            {
                // 免邮费
                return 0;
            }

            if(!isset($orderGoods->goods->hasOneGoodsDispatch)){
                // 没有找到商品配送关联模型
                return 0;
            }
            if ($orderGoods->goods->hasOneGoodsDispatch->dispatch_type == GoodsDispatch::UNIFY_TYPE) {
                // 商品配送类型为 统一运费
                return $orderGoods->goods->hasOneGoodsDispatch->dispatch_price;
            }
            return 0;
        });

        return $price;

    }

    public function needDispatch()
    {
        // 虚拟物品不需要配送
        if ($this->order->is_virtual) {
            return false;
        }

        return true;
    }
}