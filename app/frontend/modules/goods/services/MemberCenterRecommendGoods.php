<?php
/**
 * Created by PhpStorm.
 * Author:
 * Date: 2017/4/1
 * Time: 下午4:37
 */

namespace app\frontend\modules\goods\services;

use app\frontend\models\Goods;
use app\frontend\modules\member\services\MemberCenterPluginBaseService;

class MemberCenterRecommendGoods extends MemberCenterPluginBaseService
{

    public function getEnabled()
    {
        $count = $this->getRedis('member_center_recommend_'.\Yunshop::app()->uniacid);
        if (is_null($count)) {
            $count = Goods::uniacid()
                ->whereIn('yz_goods.plugin_id',[0,92,40,57,58,103,101,113])
                ->where('yz_goods.status',1)
                ->where('yz_goods.is_recommand', 1); //推荐商品

            if (app('plugins')->isEnabled('video-demand')) {
                //排除掉视频点播插件的商品
                $count = $count->join('yz_video_course_goods',function ($join) {
                    $join->on('yz_goods.id','=','yz_video_course_goods.goods_id')
                        ->where('yz_video_course_goods.is_course',0);
                });
            }
            $count = $count->count();
            $this->setRedis('member_center_recommend_'.\Yunshop::app()->uniacid,($count?:0));
        }

        if ($count > 0) {
            return true;
        }
        return false;
    }

    public function getData()
    {
        $size = 20;
        $goods = Goods::uniacid()->select('yz_goods.id','yz_goods.title','yz_goods.thumb','yz_goods.market_price',
            'yz_goods.show_sales','yz_goods.virtual_sales','yz_goods.price','yz_goods.stock','yz_goods.has_option', 'yz_goods.plugin_id')
            ->with(['hasManyOptions' => function($query){
                $query->select('goods_id','product_price','market_price');
            }])
            ->whereIn('yz_goods.plugin_id',[0,92,40,57,58,103,101,113])
            ->where('yz_goods.status',1)
            ->where('yz_goods.is_recommand', 1) //推荐商品
            ->orderBy('yz_goods.id', 'desc');

        if (app('plugins')->isEnabled('video-demand')) {
            //排除掉视频点播插件的商品
            $goods = $goods->join('yz_video_course_goods',function ($join) {
                $join->on('yz_goods.id','=','yz_video_course_goods.goods_id')
                    ->where('yz_video_course_goods.is_course',0);
            });
            $goods->whereNotIn('yz_goods.id',function ($query) {
               $query->from('yz_video_course_goods')->select('goods_id')->where('is_course',1);
            });
        }
        $goods = $goods->paginate($size);
        foreach ($goods as &$good) {
            $good['name'] = $good->title;
            $good['img']  = yz_tomedia($good->thumb);
            $good['stock_status'] = 0;
            $good['price_level'] = $good->vip_next_price?1:0;
            $good['sales'] = $good->show_sales + $good->virtual_sales;
            $good['vip_level_status'] = $good->vip_level_status;
            if ($good->has_option) {
                $minMarketPrice = $good->hasManyOptions->sortBy('market_price')->first()['market_price']?:0;
                $minPrice = $good->hasManyOptions->sortBy('product_price')->first()['product_price']?:0;
                $maxMarketPrice = $good->hasManyOptions->sortByDesc('market_price')->first()['market_price']?:0;
                $maxPrice = $good->hasManyOptions->sortByDesc('product_price')->first()['product_price']?:0;
                if ($minMarketPrice == $maxMarketPrice) {
                    $good['priceold'] = $minMarketPrice;
                }
                $good['priceold'] = ($minMarketPrice == $maxMarketPrice)?$minMarketPrice:($minMarketPrice.'-'.$maxMarketPrice);
                $good['pricenow'] = ($minPrice == $maxPrice)?$minPrice:($minPrice.'-'.$maxPrice);
            } else {
                $good['priceold'] = $good->market_price;
                $good['pricenow'] = $good->price;
            }
        }
        $goods = $goods->toArray();
        foreach ($goods['data'] as &$item) {
            $item['vip_next_price'] = $item['next_level_price'];
            $item['notshow'] = $this->getShowVipPrice();
        }
        unset($item);
        return $goods;
    }
}