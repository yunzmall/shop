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

class UnifyFreight extends BaseFreight
{
    protected $code = 'unify';

    protected $name = '统一运费';


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