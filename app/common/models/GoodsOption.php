<?php
/**
 * Created by PhpStorm.
 * Author: 芸众商城 www.yunzshop.com
 * Date: 2017/2/22
 * Time: 19:35
 */

namespace app\common\models;

use app\common\events\goods\GoodsStockNotEnoughEvent;
use app\common\exceptions\AppException;
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

}