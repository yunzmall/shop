<?php
/**
 * Created by PhpStorm.
 *
 * 
 *
 * Date: 2021/11/23
 * Time: 17:07
 */
namespace app\frontend\modules\postageIncludedCategory\models;

use app\common\models\MemberCart as BaseMemberCart;
use app\common\models\VarietyMemberCart;

class CategoryMemberCart extends BaseMemberCart
{
    // 会员购物车关联包邮分类关系
    public function varietiy()
    {
        return $this->hasMany(VarietyMemberCart::class, 'member_cart_id')
            ->where('member_cart_type', VarietyMemberCart::POSTAGE_INCLUDED)
            ->where('uniacid', \YunShop::app()->uniacid)->first();
    }
}