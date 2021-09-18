<?php

/**
 * Created by PhpStorm.
 * Author: 芸众商城 www.yunzshop.com
 * Date: 2017/3/3
 * Time: 下午2:29
 */

namespace app\frontend\modules\goods\controllers;

use app\common\components\ApiController;
use app\common\exceptions\AppException;
use app\common\models\GoodsCategory;
use app\common\models\Slide;
use app\common\services\goods\LeaseToyGoods;
use Illuminate\Support\Facades\Cookie;
use app\common\components\BaseController;
use app\common\helpers\PaginationHelper;
use app\common\helpers\Url;
use Setting;
use Illuminate\Support\Facades\DB;
use Illuminate\Session\Store;
use app\frontend\modules\goods\models\Category;
use app\frontend\modules\goods\services\CategoryService;
use app\common\models\Goods;
use app\common\models\GoodsSpecItem;
use Yunshop\Designer\models\ViewSet;
use Yunshop\MemberPrice\models\IndependentGoods;
use Yunshop\GoodsLink\services\GetGoodsDocService;
use Yunshop\TeamSales\common\services\GoodsListService;

class CategoryController extends BaseController
{
    public function getCategory()
    {
        $pageSize = 100;
        $parent_id = \YunShop::request()->parent_id ?: '0';
        $list = Category::getCategorys($parent_id)->pluginId()->where('enabled', 1)->paginate($pageSize)->toArray();

        if (!$list['data']) {
            return $this->errorJson('未检测到分类数据!');
        }

        foreach ($list['data'] as &$item) {
            $item['thumb'] = replace_yunshop(yz_tomedia($item['thumb']));
            $item['adv_img'] = replace_yunshop(yz_tomedia($item['adv_img']));
        }

        return $this->successJson('获取分类数据成功!', $list);
    }

    public function categoryHome()
    {
        $res = app('plugins')->isEnabled('designer');
        $category_data = [
            'names'     => '02',
            'type'      => 'category',
        ];
        $category_template = $category_data;
        if ($res){
            $category_template = ViewSet::uniacid()->where('type','category')->select('names','type')->first();
            $category_template = $category_template ?: $category_data;
        }
        $set = \Setting::get('shop.category');
        $pageSize = 100;
        $parent_id = \YunShop::request()->parent_id ?: '0';
        $list = Category::getCategorys($parent_id)->pluginId()->where('enabled', 1)->paginate($pageSize)->toArray();

        if (!$list['data']) {
            return $this->errorJson('未检测到分类数据!');
        }

        foreach ($list['data'] as &$item) {
            $item['thumb'] = replace_yunshop(yz_tomedia($item['thumb']));
            $item['adv_img'] = replace_yunshop(yz_tomedia($item['adv_img']));
        }

        $set['cat_adv_img'] = replace_yunshop(yz_tomedia($set['cat_adv_img']));

        if ($category_template['names'] == '03') {
            $recommend = $this->getRecommendCategoryList2();
        } else {
            $recommend = $this->getRecommendCategoryList();
        }

        // 获取推荐分类的第一个分类下的商品返回
        if (!empty($recommend)) {
            $goods_list = $this->getGoodsList($recommend[0]['id'],1);
        } else {
            $goods_list = [];
        }

        return $this->successJson('获取分类数据成功!', [
            'category' => $list,
            'recommend' => $recommend,
            'member_cart' => $this->getMemberCart(),
            'goods_list' => $goods_list,
            'ads' => $this->getAds(),
            'set' => $set,
            'category_template' => $category_template,
        ]);
    }
    protected function getMemberCart()
    {
        // 会员未登录，购物车没数据的
        try{
            $uid = \app\frontend\models\Member::current()->uid;
        } catch (\app\common\exceptions\MemberNotLoginException $e) {
            return [];
        }

        $cartList = app('OrderManager')->make('MemberCart')->carts()->where('member_id', $uid)
            ->pluginId()
            ->orderBy('created_at', 'desc')
            ->get()
            ->toArray();
        foreach ($cartList as $key => $cart) {
            $cartList[$key]['option_str'] = '';
            $cartList[$key]['goods']['thumb'] = yz_tomedia($cart['goods']['thumb']);
            if (!empty($cart['goods_option'])) {
                //规格数据替换商品数据
                if ($cart['goods_option']['title']) {
                    $cartList[$key]['option_str'] = $cart['goods_option']['title'];
                }
                if ($cart['goods_option']['thumb']) {
                    $cartList[$key]['goods']['thumb'] = yz_tomedia($cart['goods_option']['thumb']);
                }
                if ($cart['goods_option']['market_price']) {
                    $cartList[$key]['goods']['price'] = $cart['goods_option']['product_price'];
                }
                if ($cart['goods_option']['market_price']) {
                    $cartList[$key]['goods']['market_price'] = $cart['goods_option']['market_price'];
                }
            }
        }
        return $cartList;
    }

    public function getAds()
    {
        $slide = Slide::getSlidesIsEnabled()->get();
        if (!$slide->isEmpty()) {
            $slide = $slide->toArray();
            foreach ($slide as &$item) {
                $item['thumb'] = replace_yunshop(yz_tomedia($item['thumb']));
            }
        }
        return $slide;
    }

    public function getThirdCategory()
    {

        $names = '02';

        if (app('plugins')->isEnabled('decorate') && \Setting::get('plugin.decorate.is_open') == "1")
        {
            $wheres = ['type'=>3,'is_default'=>1];
            $templetList = \Yunshop\Decorate\models\DecorateTempletModel::getList($wheres,'*',false);
            if ($templetList->code ==='category03')
            {
                $names = '03';
                $goods_style = 'category03';
            }
        }elseif (app('plugins')->isEnabled('designer')) {
            $designer_set = ViewSet::uniacid()->where("type","category")->first();

            if ($designer_set) {
                $names = $designer_set['names'];
            }

            $gooodsSet = ViewSet::uniacid()->where("type","goodsList")->first();

            if ($gooodsSet) {
                $goods_style = $gooodsSet['names'];
            } else {
                $goods_style = "01";
            }

        }

        if ($names != "03") {
            return $this->errorJson("装修分类模板不正确");
        }

        $shop_cate = \Setting::get('shop.category');

        if ($shop_cate['cat_level'] != 3) {
            return $this->errorJson("商城分类级别设置不正确");
        }

        $cate_id = request()->get("cate_id");

        if (empty($cate_id)) {
            return $this->errorJson("分类ID不能为空");
        }

        $cate_name = request()->get("goods_name");

        $sort_name = request()->get("sort_name");
        $sort_name = empty($sort_name) ? 1 : $sort_name ;

        $sort_status = request()->get("sort_status");
        $sort_status = ($sort_status == 2) ? "ASC" : "DESC";

        $superior = Category::uniacid()->where("id",$cate_id)->first();
        $superior = empty($superior) ? [] : $superior->toArray();

        if (empty($superior)) {
            return $this->errorJson("分类不存在");
        }

        $category_child = [];

        if ($superior['level'] == 2) {
            $top_img = Category::uniacid()->where("id",$superior['parent_id'])->value("adv_img");
            $top_img = empty($top_img) ? "" : yz_tomedia($top_img);

            $child = Category::uniacid()->where("parent_id",$cate_id)->select("id","name","uniacid","parent_id","thumb")->where("enabled",1)->get();
            $child = empty($child) ? [] : $child->toArray();

            if (!empty($child)) {
                foreach ($child as $kk=>$vv) {
                    if ($vv['thumb']) {
                        $child[$kk]['thumb'] = yz_tomedia($vv['thumb']);
                    }
                    $category_child[] = $vv["id"];
                }
            }

            $category_child[] = $cate_id;

            $goods_ids = GoodsCategory::whereIn("category_id",$category_child)->pluck("goods_id");
            $goods_ids = empty($goods_ids) ? [] : $goods_ids->toArray();

        } else {
            $parent = Category::uniacid()->where("id",$cate_id)->value("parent_id");
            $top_id = Category::uniacid()->where("id",$parent)->value("parent_id");
            $top_img = Category::uniacid()->where("id",$top_id)->value("adv_img");
            $top_img = empty($top_img) ? "" : yz_tomedia($top_img);

            $parent_id = $superior['parent_id'];

            $third = Category::uniacid()->where("level",3)->select("id","name","uniacid","parent_id","thumb")->where("parent_id",$parent_id)->where("enabled",1)->get();
            $third = empty($third) ? [] : $third->toArray();

            if (!empty($third)) {
                foreach ($third as $kk=>$vv) {
                    if ($vv['thumb']) {
                        $third[$kk]['thumb'] = yz_tomedia($vv['thumb']);
                    }
                }
            }

            $goods_ids = GoodsCategory::where("category_id",$cate_id)->pluck("goods_id");
            $goods_ids = empty($goods_ids) ? [] : $goods_ids->toArray();
        }

        $goods_model = \app\common\modules\shop\ShopConfig::current()->get('goods.models.commodity_classification');
        $goods_model = new $goods_model;

        $goodsModel = $goods_model->uniacid()->select("id","title","thumb","price")->with("hasManyGoodsDiscount")->where("status",1)->whereIn("plugin_id",[0,92]);

        $goodsModel->whereIn("id",$goods_ids);

        if ($cate_name) {
            $goodsModel->where("title","like","%{$cate_name}%");
        }

        if ($sort_name == 2) {
            $goodsModel->orderBy("show_sales",$sort_status);
        } elseif ($sort_name == 3) {
            $goodsModel->orderBy("price",$sort_status);
        } else {
            $goodsModel->orderBy("comment_num",$sort_status);
        }

        $list = $goodsModel->paginate(25);

        $lease_switch = LeaseToyGoods::whetherEnabled();

        //由于之前 foreach 太多了 合在一起
        $list->map(function ($item) use ($lease_switch) {
            $item->goods_id = $item->id;
            $item->thumb = yz_tomedia($item->thumb);
            //租赁商品
            $this->goods_lease_set($item, $lease_switch);
            //会员vip价格
            $item->vip_price = $item->vip_price;
            $item->vip_next_price = $item->vip_next_price;
            $item->vip_level_status = $item->vip_level_status;
            $item->price_level = $item->price_level;
            $item->is_open_micro =  $item->is_open_micro;
            return $item;
        });

        $list = empty($list) ? [] : $list->toArray();

        if (app('plugins')->isEnabled('decorate') && \Setting::get('plugin.decorate.is_open') == "1")
        {
            $$wheres = ['type'=>5,'is_default'=>1];
            $templetList = \Yunshop\Decorate\models\DecorateTempletModel::getList($wheres,'*',false);
            if ($templetList->code ==='goodsList02')
            {
                $list['data'] = $this->getGoodsCouponSale($list['data']);
                //团队销售佣金一级分销
                if (app('plugins')->isEnabled('team-sales')) {
                    $list['data'] = GoodsListService::getFirstDividend($list['data']);
                }
            }
        }elseif (app('plugins')->isEnabled('designer')) {
            //商品分类模板
            $view_set = ViewSet::uniacid()->where('type','goodsList')->select('names','type')->first();

            if (!empty($view_set) && $view_set->names == '02') {
                $list['data'] = $this->getGoodsCouponSale($list['data']);
                //团队销售佣金一级分销
                if (app('plugins')->isEnabled('team-sales')) {
                    $list['data'] = GoodsListService::getFirstDividend($list['data']);
                }
            }
        }

        //增加商品链接
        if (app('plugins')->isEnabled('goods-link')) {
            $list['data'] = GetGoodsDocService::getDoc($list['data']);
        }

        $set = \Setting::get('shop.category');

        $set['cat_adv_img'] = replace_yunshop(yz_tomedia($set['cat_adv_img']));

        $data = [
            "category" => empty($child) ? $third : $child,
            "goods" => $list,
            "cat_set" => $set,
            'top_img' => $top_img,
            'goods_style' => $goods_style
        ];

        return $this->successJson("",$data);
    }

    private function getGoodsCouponSale($goods)
    {
        $coupon = \app\common\models\Coupon::uniacid()->where('use_type', 2)->get();
        foreach ($goods as $k => &$v){
            $v['coupon']['coupon_method'] = 0;                                   //商品没有折扣
            foreach ($coupon as $key => $value){
                if(in_array($v['goods_id'], $value['goods_ids'])  || $v['goods_id'] == $value['goods_ids']){
                    if($value['coupon_method'] == 1){
                        $v['coupon']['deduct_price'] = bcsub($v['price'] , $value['deduct'],2);  //立减折扣
                        $v['coupon']['coupon_method'] = $value['coupon_method'];
                        $v['coupon']['deduct'] = $value['deduct'];
                        $v['coupon']['discount'] = $value['discount'];
                    }else if($value['coupon_method'] == 2){
                        $v['coupon']['deduct_price'] = bcmul($v['price'] , $value['discount']/10, 2); //打折优惠
                        $v['coupon']['coupon_method'] = $value['coupon_method'];
                        $v['coupon']['discount'] = $value['discount'];
                        $v['coupon']['deduct'] = $value['deduct'];
                    }
                    if ($v['coupon']['deduct_price'] < 0) {
                        $v['coupon']['deduct_price'] = 0;
                    }
                }
            }
        }
        return $goods;
    }

    private function goods_lease_set(&$goodsModel, $lease_switch)
    {
        if ($lease_switch) {
            //TODO 商品租赁设置 $id
            if (is_array($goodsModel)) {
                $goodsModel['lease_toy'] = LeaseToyGoods::getDate($goodsModel['id']);

            } else {
                $goodsModel->lease_toy = LeaseToyGoods::getDate($goodsModel->id);
            }

        } else {
            if (is_array($goodsModel)) {

                $goodsModel['lease_toy'] = [
                    'is_lease' => $lease_switch,
                    'is_rights' => 0,
                    'immed_goods_id' => 0,
                ];
            } else {
                $goodsModel->lease_toy = [
                    'is_lease' => $lease_switch,
                    'is_rights' => 0,
                    'immed_goods_id' => 0,
                ];
            }
        }
    }

    /*
     * 通过某个分类id获取分类下的商品
     */
    public function getGoodsListByCategoryId()
    {
        $category_id = \YunShop::request()->category_id;
        if (empty($category_id)) {
            return $this->errorJson("分类不能为空",[]);
        }
        $goods_page = \YunShop::request()->goods_page ?: 1;
        return $this->successJson('获取商品成功!', $this->getGoodsList($category_id,$goods_page));
    }

    /**
     * 获取分类下的商品和规格
     */
    public function getGoodsList($category_id,$goods_page)
    {

        $goods_model = \app\common\modules\shop\ShopConfig::current()->get('goods.models.commodity_classification');
        $goods_model = new $goods_model;
        $list = $goods_model->uniacid()->WhereInPluginIds()->select(['yz_goods.id','yz_goods.title','yz_goods.thumb','yz_goods.market_price','yz_goods.price','yz_goods.cost_price','yz_goods.stock','yz_goods.real_sales','yz_goods.show_sales','yz_goods.virtual_sales','yz_goods.has_option', 'yz_goods.plugin_id'])
            ->with(['hasManySpecs' => function ($query) {
                return $query->select('id', 'goods_id', 'title', 'description')->with(['hasManySpecsItem'=>function($query){
                    return $query->select('id', 'title', 'specid', 'thumb');
                }])->orderBy('display_order','asc');
            }, 'hasManyOptions' => function ($query) {
                return $query->select('id', 'goods_id', 'title', 'thumb', 'product_price', 'market_price', 'stock', 'specs', 'weight');
            }])
            //->search(['category'=>$category_id])
            ->join('yz_goods_category', function ($join) use ($category_id) {
                $join->on('yz_goods_category.goods_id', '=', 'yz_goods.id')
                    ->whereRaw('FIND_IN_SET(?,category_ids)', [$category_id]);
            })
            // 由于一个商品可选多种分类，会出现查询商品重复的情况，需要对商品id分组达到去重效果
            ->groupBy('yz_goods.id')
            ->where('yz_goods.status',1)->orderBy('yz_goods.display_order', 'desc')->orderBy('yz_goods.id', 'desc')
            ->paginate(20,['*'],'page',$goods_page);
        $list->vip_level_status;
        foreach ($list as $goodsModel) {
            //前端需要goods_id
            $goodsModel->goods_id = $goodsModel->id;
            $goodsModel->buyNum = 0;
            $goodsModel->thumb = yz_tomedia($goodsModel->thumb);

            foreach ($goodsModel->hasManySpecs as &$spec) {
                foreach ($spec->hasManySpecsItem as &$specitem) {
                    $specitem->thumb = yz_tomedia($specitem->thumb);
                }
            }

            if ($goodsModel->hasManyOptions && $goodsModel->hasManyOptions->toArray()) {
                foreach ($goodsModel->hasManyOptions as &$item) {
                    $item->thumb = replace_yunshop(yz_tomedia($item->thumb));
                }
            }
            if ($goodsModel->has_option) {
                $goodsModel->min_price = $goodsModel->hasManyOptions->min("product_price");
                $goodsModel->max_price = $goodsModel->hasManyOptions->max("product_price");
                $goodsModel->stock = $goodsModel->hasManyOptions->sum('stock');
            }

            $goodsModel->vip_price = $goodsModel->vip_price;
            $goodsModel->vip_next_price = $goodsModel->vip_next_price;
            $goodsModel->price_level = $goodsModel->price_level;
            $goodsModel->is_open_micro = $goodsModel->is_open_micro;

            //特殊商品
            if (app('plugins')->isEnabled('special-goods')) {
                $goodsModel->is_special = \Yunshop\SpecialGoods\services\SpecialGoodsShop::isSpecial($goodsModel->id, $goodsModel->plugin_id);
            }


        }
        $list = $list->toArray();

        //积分商城
        if (app('plugins')->isEnabled('point-mall')) {
            $list['data'] = \Yunshop\PointMall\api\models\PointMallGoodsModel::setPointGoods($list['data']);
        }

        return  $list;
    }

    /**
     * 获取推荐分类
     * @return mixed
     */
    public function getRecommendCategoryList()
    {
        $request = Category::getRecommentCategoryList()
            ->where(['is_home'=> '1','enabled' => '1'])
            ->pluginId()
            ->get()
            ->toArray();
        foreach ($request as &$item) {
            $item['thumb'] = replace_yunshop(yz_tomedia($item['thumb']));
            $item['adv_img'] = replace_yunshop(yz_tomedia($item['adv_img']));
        }

        return $request;
    }

    /**
     * 获取推荐分类
     * @return mixed
     */
    public function getRecommendCategoryList2()
    {
        $request = Category::getRecommentCategoryList()
            ->where(['is_home'=> '1','enabled' => '1'])
            ->where("level","!=",3)
            ->pluginId()
            ->get()
            ->toArray();
        foreach ($request as &$item) {
            $item['thumb'] = replace_yunshop(yz_tomedia($item['thumb']));
            $item['adv_img'] = replace_yunshop(yz_tomedia($item['adv_img']));
        }

        return $request;
    }

    public function getChildrenCategory()
    {
        app('db')->cacheSelect = true;

        $pageSize = 20;
        $set = \Setting::get('shop.category');
        $parent_id = intval(\YunShop::request()->parent_id);
        $list = Category::getChildrenCategorys($parent_id,$set)->where('enabled',1)->paginate($pageSize)->toArray();
        foreach ($list['data'] as &$item) {
            $item['thumb'] = replace_yunshop(yz_tomedia($item['thumb']));
            $item['adv_img'] = replace_yunshop(yz_tomedia($item['adv_img']));
            foreach ($item['has_many_children'] as &$has_many_child) {
                $has_many_child['thumb'] = replace_yunshop(yz_tomedia($has_many_child['thumb']));
                $has_many_child['adv_img'] = replace_yunshop(yz_tomedia($has_many_child['adv_img']));
            }
        }

        // 增加分类下的商品返回。
        // 逻辑为：点击一级分类，如果三级分类未开启，则将一级分类下的第一个二级分类的商品返回
        // 如果开启三级分类，则取三级分类的第一个分类下的商品返回
        if (!empty($list['data'])) {
            if (empty($list['data'][0]['has_many_children'])) {
                $list['goods_list'] = $this->getGoodsList($list['data'][0]['id'],1);
            } else {
                $list['goods_list'] = $this->getGoodsList($list['data'][0]['has_many_children'][0]['id'],1);
            }
        } else {
            $list['goods_list'] = [];
        }
        $set['cat_adv_img'] = replace_yunshop(yz_tomedia($set['cat_adv_img']));
        $list['set'] = $set;

        // 默认返回等级2
        if (empty($list['set']['cat_level'])){
            $list['set']['cat_level'] = 2;
        }

        $superior = Category::uniacid()->where("id",$parent_id)->first();

        if ($superior['parent_id'] == 0) {
            $list['top_img'] = yz_tomedia($superior['adv_img']);
            $list['adv_url'] = $superior['adv_url'];
            $list['small_adv_url'] = $superior['small_adv_url'];
        } else {
            // $top_img = Category::uniacid()->where("id",$superior['parent_id'])->value("adv_img");
            $superior = Category::uniacid()->where("id",$superior['parent_id'])->first();
            $list['top_img'] = yz_tomedia($superior['adv_img']);
            $list['adv_url'] = $superior['adv_url'];
            $list['small_adv_url'] = $superior['small_adv_url'];
            // $list['top_img'] = empty($top_img) ? "" : yz_tomedia($top_img);
        }

        if($list){
            return $this->successJson('获取子分类数据成功!', $list);
        }
        return $this->errorJson('未检测到子分类数据!',$list);
    }

    public function searchGoodsCategory()
    {
        app('db')->cacheSelect = true;

        $set = \Setting::get('shop.category');
        $json_data = [];
        $list = Category::getCategorys(0)->pluginId()->where('enabled', 1)->get()->toArray();
        foreach ($list as &$parent) {
            $parent['son'] = Category::getChildrenCategorys($parent['id'],$set)->get()->toArray();
            foreach ($parent['son'] as &$value) {
                $value['thumb'] = replace_yunshop(yz_tomedia($value['thumb']));
                $value['adv_img'] = replace_yunshop(yz_tomedia($value['adv_img']));
                if (!is_null($value['has_many_children'])) {
                    foreach ($value['has_many_children'] as &$has_many_child) {
                        $has_many_child['thumb'] = replace_yunshop(yz_tomedia($has_many_child['thumb']));
                        $has_many_child['adv_img'] = replace_yunshop(yz_tomedia($has_many_child['adv_img']));
                    }
                } else {
                    $value['has_many_children'] = [];
                }
            }
            $parent['thumb'] = replace_yunshop(yz_tomedia($parent['thumb']));
            $parent['adv_img'] = replace_yunshop(yz_tomedia($parent['adv_img']));
        }

        return $this->successJson('获取子分类数据成功!', $list);
    }

//    public function getCategorySetting()
//    {
//        $set = Setting::get('shop.category');
//        if($set){
//            return $this->successJson('获取分类设置数据成功!', $set);
//        }
//        return $this->errorJson('未检测到分类设置数据!',$set);
//    }
    /**
     * 商城快速选购展示分类
     * @return \Illuminate\Http\JsonResponse
     * @throws AppException
     */
    public function fastCategory(){

        $list = Category::select('id', 'name', 'thumb', 'adv_img', 'adv_url')->uniacid()->where('level',1)->where('parent_id',0)->get();
        $list->map(function($category){
            $category->childrens = Category::select('id', 'name', 'thumb', 'adv_img', 'adv_url')->where('level',2)->where('parent_id',$category->id)->get();
        });

        if($list->isEmpty()){
            throw new AppException('未检测到分类数据');
        }
        return $this->successJson('获取分类成功!',['list'=>$list->toArray()]);
    }
}