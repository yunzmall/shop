<?php
/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 2021/8/27
 * Time: 16:24
 */

namespace app\frontend\modules\goods\controllers;


use app\common\components\ApiController;
use app\common\models\Goods;
use app\common\models\OrderGoods;
use app\frontend\models\GoodsOption;

class OtherInfoController extends ApiController
{
    public function optionVpiPrice()
    {
        $option_id =  intval(request()->input('option_id'));

        $goodsOption = GoodsOption::where('id', $option_id)->first();

        if (!$goodsOption) {
            return $this->errorJson('规格不存在或已被删除');
        }

        $vipPrice = $goodsOption->vip_price;

        return $this->successJson('商品规格vip价格', ['vip_price'=> $vipPrice]);
    }

    public function getGoods()
    {

        $goods = OrderGoods::uniacid()->find(intval(request()->input('id')));

//        $goods = Goods::uniacid()
//            ->select('id', 'title', 'thumb', 'price', 'market_price','cost_price','status')
//            ->find(intval(request()->input('goods_id')));
//
//
//        $goods->thumb = yz_tomedia($goods->thumb);

        return $this->successJson('orderGoods', $goods);
    }
}