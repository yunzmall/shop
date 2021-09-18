<?php
/**
 * Created by PhpStorm.
 * Author: 芸众商城 www.yunzshop.com
 * Date: 2017/4/1
 * Time: 下午4:37
 */

namespace app\frontend\modules\goods\services;

use app\common\models\goods\GoodsLimitBuy;
use app\frontend\models\Goods;
use app\frontend\modules\member\services\MemberCenterPluginBaseService;
use Illuminate\Support\Facades\Redis;

class MemberCenterLimitBuyGoods extends MemberCenterPluginBaseService
{

    public function getEnabled()
    {
        $count = $this->getRedis('member_center_limit_buy_'.\Yunshop::app()->uniacid);
        if (is_null($count)) {
            $count = Goods::uniacid()->join('yz_goods_limitbuy',function ($join) {
                $join->on('yz_goods.id','=','yz_goods_limitbuy.goods_id')->where(function ($where) {
                    return $where->where('yz_goods_limitbuy.status',1)
                        ->where('yz_goods_limitbuy.start_time' ,'<=',time())
                        ->where('yz_goods_limitbuy.end_time' ,'>',time());
                });
            });

            if (app('plugins')->isEnabled('video-demand')) {
                //排除掉视频点播插件的商品
                $count = $count->join('yz_video_course_goods',function ($join) {
                    $join->on('yz_goods.id','=','yz_video_course_goods.goods_id')
                        ->where('yz_video_course_goods.is_course',0);
                });
            }

            $count = $count->whereIn('yz_goods.plugin_id',[0,92,40,57,58,103,101,113])
                ->where('yz_goods.status',1)
                ->count();
            $this->setRedis('member_center_limit_buy_'.\Yunshop::app()->uniacid,($count?:0));
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
            'yz_goods.show_sales','yz_goods.virtual_sales','yz_goods.price','yz_goods.stock','yz_goods.has_option', 'yz_goods.plugin_id','yz_goods_limitbuy.start_time','yz_goods_limitbuy.end_time')
            ->join('yz_goods_limitbuy',function ($join) {
                $join->on('yz_goods.id','=','yz_goods_limitbuy.goods_id')->where(function ($where) {
                    return $where->where('yz_goods_limitbuy.status',1)
                        ->where('yz_goods_limitbuy.start_time' ,'<=',time())
                        ->where('yz_goods_limitbuy.end_time' ,'>',time());
                });
            })
            ->with(['hasManyOptions' => function($query){
                $query->select('goods_id','product_price','market_price');
            }])
            ->whereIn('yz_goods.plugin_id',[0,92,40,57,58,103,101,113])
            ->where('yz_goods.status',1)
            ->orderBy('yz_goods.id', 'desc');

        if (app('plugins')->isEnabled('video-demand')) {
            //排除掉视频点播插件的商品
            $goods = $goods->join('yz_video_course_goods',function ($join) {
                $join->on('yz_goods.id','=','yz_video_course_goods.goods_id')
                    ->where('yz_video_course_goods.is_course',0);
            });
        }

        $goods = $goods->paginate($size)->toArray();
        foreach ($goods['data'] as &$item) {
            $item['name'] = $item['title'];
            $item['img']  = yz_tomedia($item['thumb']);
            $item['stock_status'] = 0;
            $item['vip_next_price'] = $item['next_level_price'];
            $item['price_level'] = $item['vip_next_price']?1:0;
            $item['sales'] =  $item['show_sales'] + $item['virtual_sales'];
            if ($item['has_option']) {
                $minMarketPrice = collect($item['has_many_options'])->sortBy('market_price')->first()['market_price']?:0;
                $minPrice = collect($item['has_many_options'])->sortBy('product_price')->first()['product_price']?:0;
                $maxMarketPrice = collect($item['has_many_options'])->sortByDesc('market_price')->first()['market_price']?:0;
                $maxPrice = collect($item['has_many_options'])->sortByDesc('product_price')->first()['product_price']?:0;
                if ($minMarketPrice == $maxMarketPrice) {
                    $item['priceold'] = $minMarketPrice;
                }
                $item['priceold'] = ($minMarketPrice == $maxMarketPrice)?$minMarketPrice:($minMarketPrice.'-'.$maxMarketPrice);
                $item['pricenow'] = ($minPrice == $maxPrice)?$minPrice:($minPrice.'-'.$maxPrice);
            } else {
                $item['priceold'] = $item['market_price'];
                $item['pricenow'] = $item['price'];
            }
            unset($item['has_many_options'],$item['has_many_goods_discount']);
        }
        return $goods;
    }
}