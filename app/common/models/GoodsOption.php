<?php
/**
 * Created by PhpStorm.
 * Author:
 * Date: 2017/2/22
 * Time: 19:35
 */

namespace app\common\models;

use app\common\events\goods\GoodsStockNotEnoughEvent;
use app\common\exceptions\AppException;
use app\frontend\modules\goods\models\Goods;
use app\frontend\modules\goods\services\TradeGoodsPointsServer;
use app\frontend\modules\goods\stock\GoodsStock;
use app\frontend\modules\orderGoods\price\adapter\GoodsOptionPriceAdapter;

/**
 * Class GoodsOption
 * @package app\common\models
 * @property int uniacid
 * @property int goods_id
 * @property int product_price
 * @property int market_price
 * @property int title
 * @property int stock
 */
class GoodsOption extends \app\common\models\BaseModel
{
    public $table = 'yz_goods_option';

    public $guarded = [];
    public $timestamps = false;

    protected $hidden = [
        "created_at",
        "updated_at",
        "deleted_at",
    ];

    /**
     * 库存是否充足
     * @param $num
     * @return bool
     * @author shenyang
     */
    public function stockEnough($num)
    {
        if($this->goods->reduce_stock_method == 2){
            return true;
        }
        return $this->goodsStock()->enough($num);
    }

    public function goods()
    {
        return $this->belongsTo(app('GoodsManager')->make('Goods'), 'goods_id', 'id');
    }

    public static function boot()
    {
        parent::boot();


        static::observe(new \app\common\modules\goods\GoodsOptionObserverBase);
    }


    public function save()
    {
        if ($this->attributes['reduce_stock_method'] != $this->original['reduce_stock_method']) {
            if ($this->withhold_stock > 0) {
                throw new AppException('商品规格[' . $this->title . ']存在预扣库存，无法修改减库存方式设置。');
            }
        }
        // 提交的库存是扣除预扣的,保存的时候必须将预扣的数量加回来
        if(isset($this->attributes['stock']) && $this->attributes['stock'] != $this->original['stock']){
            $this->attributes['stock'] = $this->attributes['stock'] + $this->withhold_stock;
        }

        $result = parent::save();


        return $result;
    }

    private $goodsStock;

    public function goodsStock()
    {
        if (!isset($this->goodsStock)) {
            $this->goodsStock = new GoodsStock($this);
        }
        return $this->goodsStock;
    }

    public function getStockAttribute()
    {
        return $this->goodsStock()->usableStock();
    }

    public function getWithholdStockAttribute()
    {
        return $this->goodsStock()->withholdStock();
    }

    public function fireStockNotEnoughtEvent($goods)
    {
        event(new GoodsStockNotEnoughEvent([],$goods));
    }


    //todo blank 商品价格适配器
    public function getGoodsPriceAdapter()
    {
        return new GoodsOptionPriceAdapter($this);
    }

    /**
     * @description 下单页积分
     * @return string
     */
    public function getPointsAttribute()
    {
        $tradeGoodsPointsServer = app(TradeGoodsPointsServer::class);

        if ($tradeGoodsPointsServer->close(TradeGoodsPointsServer::GOODS_PAGE)){
            return '';
        }

        $points = $tradeGoodsPointsServer->finalSetPoint();

        return $tradeGoodsPointsServer->getPoint($points, $this->product_price, $this->cost_price);
    }

    /**
     * @return int
     * @throws AppException
     */
    public function getVipPriceAttribute()
    {
        if (!\YunShop::app()->getMemberId()) {
            return $this->product_price;
        }
        $member = \app\frontend\models\Member::current();
        $level_id = $member->yzMember->level_id;

        $goods = $this->goods->hasManyGoodsDiscount->where('level_id', $level_id)->first();
        if ($goods) {
            return sprintf('%.2f', max($this->product_price - $goods->getAmount($this->getGoodsPriceAdapter(), $member), 0));
        }

        $level = MemberLevel::getMemberLevel($level_id);
        if (empty($level)) {
            return $this->product_price;
        }

        $price = $level->getDiscountCalculation($this->getGoodsPriceAdapter());
        $return_price = sprintf('%.2f', $this->product_price - $price);
        return $return_price >= 0 ? $return_price : 0;
    }

    /**
     * @return int
     * @throws AppException
     */
    public function getNextVipPriceAttribute()
    {
        if (!\YunShop::app()->getMemberId()) {
            return $this->product_price;
        }
        $member = \app\frontend\models\Member::current();
        $level_id = $member->yzMember->level_id;

        if (empty($level_id)) {
            $nextLevel = MemberLevel::getFirstLevel();
        } else {
            $level = MemberLevel::getMemberLevel($level_id);
            if ($level) {
                $nextLevel = MemberLevel::getNextMemberLevel($level);
                $this->nextLevelName = $nextLevel->level_name;
            }
        }

        $priceClass = $this->getGoodsPriceAdapter();

        if ($nextLevel) {
            $goods = $this->goods->hasManyGoodsDiscount->where('level_id', $nextLevel->id)->first();
            if (!$goods) {
                /**
                 * @param \app\common\models\MemberLevel $nextLevel
                 */
                $price = $nextLevel->getDiscountCalculation($priceClass);

                return sprintf('%.2f', max($this->product_price - $price,0));
            }

            return sprintf('%.2f', max($this->product_price - $goods->getNextAmount($priceClass, $nextLevel),0));
        } else {

            $goods = $this->goods->hasManyGoodsDiscount->where('level_id', $level->id)->first();
            if (!$goods) {


                if (!is_null($level) && method_exists($level, 'getDiscountCalculation')) {
                    $price = $level->getDiscountCalculation($priceClass);
                    return sprintf('%.2f', max($this->product_price - $price,0));
                }

                return $this->product_price;

//                if ($level === null || empty($level->id)) {
//                    return $this->product_price;
//                } else {
//                    $price = $level->getDiscountCalculation($priceClass);
//                    return sprintf('%.2f', max($this->product_price - $price,0));
//                }
            }

            return sprintf('%.2f', max($this->product_price - $goods->getAmount($priceClass, $member), 0));
        }
    }

    public function getAllLevelPriceAttribute()
    {
        if (\YunShop::app()->getMemberId()) {
            $member = \app\frontend\models\Member::current();
        } else {
            $member = new \app\frontend\models\Member();
        }
        $level_id = $member->yzMember->level_id;
        $all_level_price = [];
        $priceClass = $this->getGoodsPriceAdapter();
//        if (!$level_id) {
//            $nextLevel = MemberLevel::getFirstLevel();
//            if ($nextLevel) {
//                $goods = $this->hasManyGoodsDiscount->where('level_id', $nextLevel->id)->first();
//                if (!$goods) {
//                    /**
//                     * @param \app\common\models\MemberLevel $nextLevel
//                     */
//                    $price = $nextLevel->getDiscountCalculation($priceClass);
//                    $deal_price = sprintf('%.2f', $this->deal_price - $price);
//                } else {
//                    $deal_price = sprintf('%.2f', $this->deal_price - $goods->getNextAmount($priceClass, $nextLevel));
//                }
//            } else {
//                $deal_price = $this->deal_price;
//            }
//            $all_level_price[] = [
//                'level_name' => $nextLevel->level_name,
//                'level_id' => $nextLevel->id,
//                'level' => $nextLevel->level,
//                'price' => $deal_price,
//            ];
//        } else {
        $member_levels = MemberLevel::uniacid()->groupBy('level')->orderBy('level', 'asc')->get();
        if ($member_levels->isEmpty()) {
            $default_level = \Setting::get('shop.member.level_name');
            $level_name = $default_level ?: '普通会员';
            $all_level_price[] = [
                'level_name' => $level_name,
                'level_id' => 0,
                'level' => 0,
                'price' => $this->product_price,
                'is_select' => true,
                'is_next' => false,
            ];
            return $all_level_price;
        }
        $is_check_next = true;
        $level_count = $member_levels->count();
        $i = 0;
        $can_upgrade = false;
        foreach ($member_levels as $level) {
            $goods = $this->goods->hasManyGoodsDiscount->where('level_id', $level->id)->first();
            if (!$goods) {
                $price = $level->getDiscountCalculation($priceClass);
                $deal_price = sprintf('%.2f', $this->product_price - $price);
            } else {
                $deal_price = sprintf('%.2f', $this->product_price - $goods->getNextAmount($priceClass, $level));
            }
            $level_data = [
                'level_name' => $level->level_name,
                'level_id' => $level->id,
                'level' => $level->level,
                'price' => $deal_price,
                'is_select' => false,
                'is_next' => false,
                'is_last' => false,
            ];
            if ($level_id == $level->id) {
                $level_data['is_select'] = true;
            }
            if ($level_id < $level->id && $is_check_next) {
                $level_data['is_next'] = true;
                $is_check_next = false;
                $can_upgrade = true;
            }
            $i++;
            if ($level_count == $i && $level->id == $level_id) {
                $level_data['is_last'] = true;
            }
            array_push($all_level_price, $level_data);
        }
//        }
        return [$all_level_price, $can_upgrade];
    }
}