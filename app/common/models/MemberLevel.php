<?php
/**
 * Created by PhpStorm.
 * Author: 芸众商城 www.yunzshop.com
 * Date: 2017/2/27
 * Time: 上午11:18
 */

namespace app\common\models;



use app\frontend\modules\orderGoods\price\adapter\BaseGoodsPriceAdapter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;

class MemberLevel extends BaseModel
{
    use SoftDeletes;

    public $table = 'yz_member_level';

    protected $guarded = [''];

    /**
     * 设置全局作用域 拼接 uniacid()
     */
    public static function boot()
    {
        parent::boot();
        static::addGlobalScope('uniacid',function (Builder $builder) {
            return $builder->uniacid();
        });
    }

    public function scopeRecords($query)
    {
        return $query->select('id','level','level_name');
    }

    public static function getMemberLevel($level_id)
    {
        return  self::uniacid()->where("id",$level_id)->first();
    }

    public static  function getNextMemberLevel($level)
    {
        return self::uniacid()->where('level',">",$level->level)->orderBy('level','ASC')->first();
    }

    public static function getFirstLevel()
    {
        return self::uniacid()->orderBy('level','ASC')->first();
    }

    /**
     * 获取默认等级
     *
     * @return mixed
     */
    public static function getDefaultLevelId()
    {
        return self::select('id')
            ->uniacid()
            ->where('is_default', 1);
    }

    /**
     * @param BaseGoodsPriceAdapter $priceClass
     */
    public function getDiscountCalculation($priceClass)
    {

        //获取设置的计算方式
        $level_discount_calculation = \Setting::get('shop.member.level_discount_calculation');

        switch ($level_discount_calculation) {
            case 1:
                //取商品成本价
                $discountAmount =  $this->getMemberLevelGoodsCostPriceDiscountAmount($priceClass->getCostPrice());
                break;
            default:
                ///为空为0,默认取商品现价
                $discountAmount = $this->getMemberLevelGoodsPriceDiscountAmount($priceClass->getDealPrice());
                break;
        }

        return max($discountAmount, 0);
    }

    public function getMemberLevelGoodsPriceDiscountAmount($goodsPrice)
    {
        // 商品折扣 默认 10折
        $this->discount = trim($this->discount);
        $this->discount = $this->discount == false ? 10 : $this->discount;

        if ($this->distinct > 10) {
            $this->distinct = 10;
        }
        // 折扣/10 得到折扣百分比
        return (1 - $this->discount / 10) * $goodsPrice;
    }

    public function getMemberLevelGoodsCostPriceDiscountAmount($goodsCostPrice)
    {
        $discount = trim($this->discount);

        if (empty($discount)) {
            return 0;
        }

        return  ($discount / 100) * $goodsCostPrice;
    }

    /**
     * 商品全局等级折扣后价格
     * @param $goodsPrice
     * @return float|int
     */
    public function getMemberLevelGoodsDiscountAmount($priceClass)
    {
        return $this->getDiscountCalculation($priceClass);
    }


}
