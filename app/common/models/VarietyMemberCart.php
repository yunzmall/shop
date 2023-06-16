<?php
/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 2021/11/23
 * Time: 16:59
 */
namespace app\common\models;
use Illuminate\Database\Eloquent\Model;

/**
 * @description 会员购物车关联表(多态性)
 */
class VarietyMemberCart extends Model
{
    protected $table = 'yz_variety_member_cart';
    protected $guarded = [];

    const POSTAGE_INCLUDED = 'PostageIncludedCategory';  // 包邮分类


    /**
     * @description 获得拥有此购物车的模型
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function memberCartType()
    {
        return $this->morphTo();
    }

}
