<?php
/**
 * Created by PhpStorm.
 * 
 * 
 *
 * Date: 2021/7/16
 * Time: 14:50
 */

namespace app\frontend\modules\order\discount;

use app\common\modules\orderGoods\models\PreOrderGoods;

class MemberLevel extends BaseDiscount
{
    protected $code = 'memberLevel';
    protected $name = '会员等级优惠';


    /**
     * 订单中订单商品会员等级的总金额
     * @return float
     */
    protected function _getAmount()
    {
        $result = $this->order->orderGoods->sum(function (PreOrderGoods $preOrderGoods) {
            return $preOrderGoods->getVipDiscountAmount();

        });

        return $result;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        $name = $this->name;
        $this->order->orderGoods->each(function (PreOrderGoods $preOrderGoods) use (&$name){

            // 如果是一卡通, 那边优惠的集合名称就是消费券优惠
            if ($preOrderGoods->getVipDiscountLog('code') == 'store_privilege') {
                $name = $preOrderGoods->getVipDiscountLog('name');
                return false;
            }

            return true;
        });

        return $name;
    }

    public function preSave()
    {
        return false;
    }

}