<?php
/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 2021/7/16
 * Time: 13:56
 */

namespace app\frontend\modules\orderGoods\discount;


class MemberLevel extends BaseDiscount
{
    protected $code = 'memberLevel';
    protected $name = '会员等级优惠';

    /**
     * @return float
     * @throws \app\common\exceptions\AppException
     */
    protected function _getAmount()
    {
        return $this->orderGoods->getVipDiscountAmount();
    }


    public function getName()
    {
        return $this->orderGoods->getVipDiscountLog('name');
    }

    public function getCode()
    {
        return $this->orderGoods->getVipDiscountLog('code');
    }

}