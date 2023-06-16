<?php
/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 2021/4/29
 * Time: 13:50
 */

namespace app\frontend\modules\cart\discount;


class MemberLevelDiscount extends BaseCartDiscount
{
    protected $code = 'memberLevel';
    protected $name = '会员等级优惠';

    /**
     * @return float
     * @throws \app\common\exceptions\AppException
     */
    protected function _getAmount()
    {
        return $this->cartGoods->getVipDiscountAmount();
    }

    public function getKey()
    {
        return 'independentGoodsMemberLevel';
    }
    public function getName()
    {
        return '商品会员等级优惠';
    }
}