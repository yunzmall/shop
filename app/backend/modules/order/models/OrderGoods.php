<?php
/**
 * Created by PhpStorm.
 * Author: 芸众商城 www.yunzshop.com
 * Date: 2017/5/8
 * Time: 下午5:06
 */

namespace app\backend\modules\order\models;


use app\backend\modules\goods\models\Goods;

class OrderGoods extends \app\common\models\OrderGoods
{
    static protected $needLog = true;
    protected $with = ['goods'];
    protected $appends = [
        'goods_thumb', 'buttons'
    ];

    public function getGoodsThumbAttribute()
    {
        return yz_tomedia($this->goods->thumb);
    }

    public function scopeOrderDetailGoods($query)
    {
        $orderDetailGoods = $query->select(['id', 'order_id', 'goods_id', 'goods_price', 'total', 'goods_option_title', 'price', 'goods_market_price', 'goods_cost_price', 'thumb', 'title', 'goods_sn','payment_amount','deduction_amount'])
            ->with([
                'goods'=>function ($query) {
                return $query->select(['id','title','status','type','thumb','sku','market_price','price','cost_price','weight','product_sn','goods_sn']);
                },
                'orderGoodsDeductions',
                'orderGoodsDiscounts'
            ]);

        return $orderDetailGoods;
    }

    public function goods()
    {
        return $this->hasOne(Goods::class, 'id', 'goods_id');
    }
}