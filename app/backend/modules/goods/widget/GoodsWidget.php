<?php
/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 2021/9/9
 * Time: 15:38
 */

namespace app\backend\modules\goods\widget;

use app\backend\modules\goods\models\Brand;
use app\backend\modules\goods\models\Category;
use app\backend\modules\goods\models\GoodsOption;

class GoodsWidget extends BaseGoodsWidget
{
    public $group = 'base';

    public $widget_key = 'goods';

    public $code = 'goods';

    public function pluginFileName()
    {
        return 'goods';
    }

    public function getCategoryList()
    {
        return Category::getAllCategoryGroupArray();
    }

    public function categoryLevel()
    {
        return \Setting::get('shop.category')['cat_level']?:2;
    }

    public function getData()
    {

        //商品属性默认值
        $goods = [
            'is_recommand' => 0,
            'is_new' => 0,
            'is_hot' => 0,
            'is_discount' => 0,
            'hide_goods_sales' => 0,
            'hide_goods_sales_alone' => 0,
        ];
        $result['category_level'] = $this->categoryLevel();
        $result['category_list'] = $this->getCategoryList();
        $result['brand'] = Brand::getBrands()->getQuery()->select(['id','name'])->get();

        $result['goods'] = $goods;

        if (is_null($this->goods)) {
            $result['goods']['hide_goods_sales_switch'] = in_array($this->request->route,['goods.goods.widget-column','plugin.auction.admin.auction.widget-column']) ? 1 : 0;
            return $result;
        }

        $goods = array_merge($goods, $this->goods->toArray());
        $goods = array_except($goods,['content','old_id','is_deleted','created_at','updated_at','deleted_at']);

        if ($this->goods->thumb) {
            $goods['thumb_link'] = yz_tomedia($this->goods->thumb);
        }

        //商品其它图片反序列化
        $thumb_url = !empty($this->goods->thumb_url) ? unserialize($this->goods->thumb_url) : [];

        $goods['thumb_url'] = collect($thumb_url)->map(function ($item) {
            return ['thumb_link'=>yz_tomedia($item), 'thumb' => $item];
        })->values()->all();

        //商品视频处理
        $goods['goods_video'] = $this->goods->hasOneGoodsVideo->goods_video?: '';
        $goods['video_image'] = $this->goods->hasOneGoodsVideo->video_image ?: '';
        $goods['goods_video_link'] = $this->goods->hasOneGoodsVideo->goods_video? yz_tomedia($this->goods->hasOneGoodsVideo->goods_video) : '';
        $goods['video_image_link'] = $this->goods->hasOneGoodsVideo->video_image? yz_tomedia($this->goods->hasOneGoodsVideo->video_image) : '';

        $goods['withhold_stock'] = $this->goods->withhold_stock;
        
        //分类
        if (!$this->goods->hasManyGoodsCategory->isEmpty()){

            $category = [];
            $category_to_option = [];
            foreach($this->goods->hasManyGoodsCategory->toArray() as $goods_category){
                $category_ids  = explode(",", $goods_category['category_ids']);

                //商品分类层级大于设置层级
                if (count($category_ids) > $result['category_level']) {
                    array_pop($category_ids);
                }

                $category_item = Category::select('id','name','level')->uniacid()
                    ->whereIn('id', $category_ids)->orderBy('level')->get();

                if (!$category_item->isEmpty() && $category_item->count() > 1) {
                    $category[] = $category_item->toArray();
                    $category_to_option[] = ['goods_option_id'=>$goods_category['goods_option_id']?:''];
                }
            }
            $goods['category'] = $category;
            $goods['category_to_option'] = $category_to_option;
        } else {
            $goods['category'] = [];
            $goods['category_to_option'] = [];
        }

        $result['goods'] = $goods;

        $result['options'] = [];
        $result['category_to_option_open'] = \Setting::get('shop.category.category_to_option') ? : 0;

        if (!in_array($this->getRoute(),['shop','supplier'])) {
            $result['category_to_option_open'] = 0;
        }
        if (!is_null($this->goods) && $this->goods->has_option) {
            $result['options'] = GoodsOption::uniacid()
                ->select('id','title')
                ->where('goods_id',$this->goods->id)
                ->get()->toArray();
        }
        $result['goods']['hide_goods_sales_switch'] = in_array($this->goods->plugin_id, [0, 67]) ? 1 : 0;

        return $result;
    }


    /**
     * hide_status 隐藏状态
     * hide_weight 隐藏重量
     * hide_volume 隐藏体积
     * hide_product_sn 隐藏商品条码
     * appoint_type 指定商品类型
     * appoint_need_address 指定下单是否需要地址
     * appoint_need_address 指定下单是否需要地址
     * @return array
     */
    public function attrHide()
    {
        return [];
    }

    public function pagePath()
    {
        return  $this->getPath('resources/views/goods/assets/js/components/');
    }
}