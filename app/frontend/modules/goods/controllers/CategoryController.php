<?php

/**
 * Created by PhpStorm.
 * Author:
 * Date: 2017/3/3
 * Time: 下午2:29
 */

namespace app\frontend\modules\goods\controllers;

use app\backend\modules\goods\models\GoodsTradeSet;
use app\common\components\ApiController;
use app\common\exceptions\AppException;
use app\common\models\goods\GoodsFiltering;
use app\common\models\GoodsCategory;
use app\common\models\SearchFiltering;
use app\common\models\Slide;
use app\common\services\goods\LeaseToyGoods;
use Illuminate\Support\Carbon;
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


    protected function categoryHomeListQuery()
    {
        $parent_id = \YunShop::request()->parent_id ?: '0';
        return Category::getCategorys($parent_id)->pluginId()->where('enabled', 1);
    }

    protected function recommendCategoryList($category_template, $category_list = [])
    {
        if ($category_template['names'] == '03') {
            $recommend = $this->getRecommendCategoryList2();
        } else {
            $recommend = $this->getRecommendCategoryList();
        }
        return $recommend;
    }

    public function categoryHome()
    {
        $res = app('plugins')->isEnabled('designer');
        $category_data = [
            'names' => '02',
            'type'  => 'category',
        ];
        $category_template = $category_data;
        if ($res) {
            $category_template = ViewSet::uniacid()->where('type', 'category')->select('names', 'type')->first();
            $category_template = $category_template ?: $category_data;
        }
        $set = \Setting::get('shop.category');
        if(!$set){
            $set = [
                'cat_brand' => "0",
                'cat_class' => "0",
            ];
        }
        $pageSize = 100;
        $list = $this->categoryHomeListQuery();
//        $parent_id = \YunShop::request()->parent_id ?: '0';
//        $list = Category::getCategorys($parent_id)->pluginId()->where('enabled', 1);

        $list = $list->paginate($pageSize)->toArray();

        if (!$list['data']) {
            return $this->errorJson('未检测到分类数据!');
        }

        foreach ($list['data'] as &$item) {
            $item['thumb'] = replace_yunshop(yz_tomedia($item['thumb']));
            $item['adv_img'] = replace_yunshop(yz_tomedia($item['adv_img']));
        }

        $set['cat_adv_img'] = replace_yunshop(yz_tomedia($set['cat_adv_img']));

        $recommend = $this->recommendCategoryList($category_template, $list);
//        if ($category_template['names'] == '03') {
//            $recommend = $this->getRecommendCategoryList2();
//        } else {
//            $recommend = $this->getRecommendCategoryList();
//        }

        // 获取推荐分类的第一个分类下的商品返回
        if (!empty($recommend)) {
            $goods_list = $this->getGoodsList($recommend[0]['id'], 1);
        } else {
            $goods_list = [];
        }

        // 只有分类中心模板是小程序模板的时候提供给前端进行判断
        $set['web_design_enable'] = app('plugins')->isEnabled('web-design');

        return $this->successJson('获取分类数据成功!', [
            'category'          => $list,
            'recommend'         => $recommend,
            'member_cart'       => $this->getMemberCart(),
            'goods_list'        => $goods_list,
            'ads'               => $this->getAds(),
            'set'               => $set,
            'category_template' => $category_template
        ]);
    }

    protected function getMemberCart()
    {
        // 会员未登录，购物车没数据的
        try {
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

        if (app('plugins')->isEnabled('decorate') && \Setting::get('plugin.decorate.is_open') == "1") {
            $wheres = ['type' => 3, 'is_default' => 1];
            $templetList = \Yunshop\Decorate\models\DecorateTempletModel::getList($wheres, '*', false);
            if ($templetList->code === 'category03') {
                $names = '03';
                $goods_style = 'category03';
            }
        } elseif (app('plugins')->isEnabled('designer')) {
            $designer_set = ViewSet::uniacid()->where("type", "category")->first();

            if ($designer_set) {
                $names = $designer_set['names'];
            }

            $gooodsSet = ViewSet::uniacid()->where("type", "goodsList")->first();

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
        $sort_name = empty($sort_name) ? 1 : $sort_name;

        $sort_status = request()->get("sort_status");
        $sort_status = ($sort_status == 2) ? "ASC" : "DESC";

        $superior = Category::uniacid()->where("id", $cate_id)->first();
        $superior = empty($superior) ? [] : $superior->toArray();

        if (empty($superior)) {
            return $this->errorJson("分类不存在");
        }

        $category_child = [];

        if ($superior['level'] == 2) {
            $top_img = Category::uniacid()->where("id", $superior['parent_id'])->value("adv_img");
            $top_img = empty($top_img) ? "" : yz_tomedia($top_img);

            $child = Category::uniacid()->where("parent_id", $cate_id)->select(
                "id",
                "name",
                "uniacid",
                "parent_id",
                "thumb"
            )->where("enabled", 1)->orderBy('display_order', 'desc')->get();
            $child = empty($child) ? [] : $child->toArray();

            if (!empty($child)) {
                foreach ($child as $kk => $vv) {
                    if ($vv['thumb']) {
                        $child[$kk]['thumb'] = yz_tomedia($vv['thumb']);
                    }
                    $category_child[] = $vv["id"];
                }
            }

            $category_child[] = $cate_id;

            $goods_ids = GoodsCategory::whereIn("category_id", $category_child)->pluck("goods_id");
            $goods_ids = empty($goods_ids) ? [] : $goods_ids->toArray();
        } else {
            $parent = Category::uniacid()->where("id", $cate_id)->value("parent_id");
            $top_id = Category::uniacid()->where("id", $parent)->value("parent_id");
            $top_img = Category::uniacid()->where("id", $top_id)->value("adv_img");
            $top_img = empty($top_img) ? "" : yz_tomedia($top_img);

            $parent_id = $superior['parent_id'];

            $third = Category::uniacid()->where("level", 3)->select(
                "id",
                "name",
                "uniacid",
                "parent_id",
                "thumb"
            )->where("parent_id", $parent_id)->where("enabled", 1)->orderBy('display_order', 'desc')->get();
            $third = empty($third) ? [] : $third->toArray();

            if (!empty($third)) {
                foreach ($third as $kk => $vv) {
                    if ($vv['thumb']) {
                        $third[$kk]['thumb'] = yz_tomedia($vv['thumb']);
                    }
                }
            }

            $goods_ids = GoodsCategory::where("category_id", $cate_id)->pluck("goods_id");
            $goods_ids = empty($goods_ids) ? [] : $goods_ids->toArray();
        }

        $goods_model = \app\common\modules\shop\ShopConfig::current()->get('goods.models.commodity_classification');
        $goods_model = new $goods_model;

        $goodsModel = $goods_model->uniacid()->select("id", "title", "thumb", "price")->with(
            "hasManyGoodsDiscount"
        )->where("status", 1)->whereIn("plugin_id", [0, 92, 120]);

        $goodsModel->whereIn("id", $goods_ids);

        if ($cate_name) {
            $goodsModel->where("title", "like", "%{$cate_name}%");
        }

        if ($sort_name == 2) {
            $goodsModel->orderBy("show_sales", $sort_status);
        } elseif ($sort_name == 3) {
            $goodsModel->orderBy("price", $sort_status);
        } else {
            $goodsModel->orderBy("comment_num", $sort_status);
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
            $item->is_open_micro = $item->is_open_micro;
            return $item;
        });

        $list = empty($list) ? [] : $list->toArray();

        if (app('plugins')->isEnabled('decorate') && \Setting::get('plugin.decorate.is_open') == "1") {
            $wheres = ['type' => 5, 'is_default' => 1];
            $templetList = \Yunshop\Decorate\models\DecorateTempletModel::getList($wheres, '*', false);
            if ($templetList->code === 'goodsList02') {
                $list['data'] = $this->getGoodsCouponSale($list['data']);
                //团队销售佣金一级分销
                if (app('plugins')->isEnabled('team-sales')) {
                    $list['data'] = GoodsListService::getFirstDividend($list['data']);
                }
            }
        } elseif (app('plugins')->isEnabled('designer')) {
            //商品分类模板
            $view_set = ViewSet::uniacid()->where('type', 'goodsList')->select('names', 'type')->first();

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
            "category"    => empty($child) ? $third : $child,
            "goods"       => $list,
            "cat_set"     => $set,
            'top_img'     => $top_img,
            'goods_style' => $goods_style
        ];

        return $this->successJson("", $data);
    }

    private function getGoodsCouponSale($goods)
    {
        $coupon = \app\common\models\Coupon::uniacid()->where('use_type', 2)->get();
        foreach ($goods as $k => &$v) {
            $v['coupon']['coupon_method'] = 0;                                   //商品没有折扣
            foreach ($coupon as $key => $value) {
                if (in_array($v['goods_id'], $value['goods_ids']) || $v['goods_id'] == $value['goods_ids']) {
                    if ($value['coupon_method'] == 1) {
                        $v['coupon']['deduct_price'] = bcsub($v['price'], $value['deduct'], 2);  //立减折扣
                        $v['coupon']['coupon_method'] = $value['coupon_method'];
                        $v['coupon']['deduct'] = $value['deduct'];
                        $v['coupon']['discount'] = $value['discount'];
                    } else {
                        if ($value['coupon_method'] == 2) {
                            $v['coupon']['deduct_price'] = bcmul($v['price'], $value['discount'] / 10, 2); //打折优惠
                            $v['coupon']['coupon_method'] = $value['coupon_method'];
                            $v['coupon']['discount'] = $value['discount'];
                            $v['coupon']['deduct'] = $value['deduct'];
                        }
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
                    'is_lease'       => $lease_switch,
                    'is_rights'      => 0,
                    'immed_goods_id' => 0,
                ];
            } else {
                $goodsModel->lease_toy = [
                    'is_lease'       => $lease_switch,
                    'is_rights'      => 0,
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
            return $this->errorJson("分类不能为空", []);
        }
        $goods_page = \YunShop::request()->goods_page ?: 1;
        return $this->successJson('获取商品成功!', $this->getGoodsList($category_id, $goods_page));
    }

    protected function getGoodsModel()
    {
        $goods_model = \app\common\modules\shop\ShopConfig::current()->get('goods.models.commodity_classification');
        $goods_model = new $goods_model;
        return $goods_model;
    }

    /**
     * 获取分类下的商品和规格
     */
    public function getGoodsList($category_id, $goods_page)
    {
        $order_filed = request()->input('order_field');
        $order_by = request()->input('order_by','asc');
        $search = request()->input('search');
        $goods_model = $this->getGoodsModel();
//        $goods_model = \app\common\modules\shop\ShopConfig::current()->get('goods.models.commodity_classification');
//        $goods_model = new $goods_model;
        $notDisplay = [
            121, // 话费充值不展示分类里面
            131, // 电费充值不展示分类里面
        ];
        $search = array_filter($search, function ($item) {
            return !empty($item) && $item !== 0  && $item !== "undefined";
        });

        //字句剥离出来，不然count统计与查询都去执行这个字句
        $goods_ids = \app\common\models\GoodsCategory::select('goods_id')
            ->whereRaw(
                'FIND_IN_SET(?,category_ids)',
                [$category_id]
            )->pluck('goods_id')->all() ? : [0];

        $list = $goods_model
            ->SearchList($search)
            ->whereIn('yz_goods.id', $goods_ids)
            ->uniacid()
            ->WhereInPluginIds()
            ->whereNotIn('plugin_id', $notDisplay)
            ->select(
                [
                    'yz_goods.id',
                    'yz_goods.title',
                    'yz_goods.thumb',
                    'yz_goods.market_price',
                    'yz_goods.price',
                    'yz_goods.min_price',
                    'yz_goods.max_price',
                    'yz_goods.cost_price',
                    'yz_goods.stock',
                    'yz_goods.real_sales',
                    'yz_goods.show_sales',
                    'yz_goods.virtual_sales',
                    'yz_goods.has_option',
                    'yz_goods.plugin_id',
                    DB::raw('show_sales+virtual_sales as total_sales'),
                ]
            )
            ->with([
                'hasManySpecs'   => function ($query) {
                    return $query->select('id', 'goods_id', 'title', 'description')->with([
                        'hasManySpecsItem' => function ($query) {
                            return $query->select('id', 'title', 'specid', 'thumb');
                        }
                    ])->orderBy('display_order', 'asc');
                },
                'hasManyOptions' => function ($query) {
                    return $query->select(
                        'id',
                        'goods_id',
                        'title',
                        'thumb',
                        'product_price',
                        'market_price',
                        'stock',
                        'specs',
                        'weight'
                    );
                },
                'hasManyGoodsFilter' => function ($query) {
                    return $query->select(
                        'goods_id','filtering_id'
                    );
                },
            ])
            ->where('yz_goods.is_hide', 1)
            ->where('yz_goods.status', 1)
            ->when($order_filed,function ($query) use ($order_filed,$order_by) {
                if ($order_filed == 'show_sales') {
                    $order_filed = 'total_sales';
                    return $query->orderBy($order_filed,$order_by);
                }
                return $query->orderBy('yz_goods.'.$order_filed,$order_by);
            })
            ->orderBy('yz_goods.display_order', 'desc')
            ->orderBy('yz_goods.id', 'desc')
            ->paginate(15, ['*'], 'page', $goods_page);
        $list->vip_level_status;

        $goodsIds = $list->pluck('id')->all();
        $goodsCategory = [];
        $category_to_option_open = \Setting::get('shop.category.category_to_option') ?: 0;
        if ($goodsIds && $category_to_option_open) {
            $goodsCategory = GoodsCategory::select('goods_id', 'goods_option_id')
                ->with('goodsOption')
                ->whereRaw('FIND_IN_SET(?,category_ids)', [$category_id])
                ->whereIn('goods_id', $goodsIds)
                ->orderBy('goods_option_id', 'desc')
                ->groupBy('goods_id')
                ->get()->toArray();
            $goodsCategory = collect($goodsCategory)->keyBy('goods_id')->all();
        }

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
            if ($goodsModel->has_option && $goodsModel->hasManyOptions) {
                $goodsModel->stock = $goodsModel->hasManyOptions->sum('stock');
            }

            $goodsModel->vip_price = $goodsModel->vip_price;
            $goodsModel->vip_next_price = $goodsModel->vip_next_price;
            $goodsModel->price_level = $goodsModel->price_level;
            $goodsModel->is_open_micro = $goodsModel->is_open_micro;
            if ($goodsCategory[$goodsModel->id] && $goodsCategory[$goodsModel->id]['goods_option_id'] && $goodsCategory[$goodsModel->id]['goods_option']) {
                $goodsModel->offsetSet('category_option_id', $goodsCategory[$goodsModel->id]['goods_option_id']);
                $goodsModel->offsetSet(
                    'goods_option_ids',
                    explode('_', $goodsCategory[$goodsModel->id]['goods_option']['specs'])
                );
                $goodsModel->thumb = yz_tomedia(
                    $goodsCategory[$goodsModel->id]['goods_option']['thumb']
                ) ?: $goodsModel->thumb;
            }

            //特殊商品
            if (app('plugins')->isEnabled('special-goods')) {
                $goodsModel->is_special = \Yunshop\SpecialGoods\services\SpecialGoodsShop::isSpecial(
                    $goodsModel->id,
                    $goodsModel->plugin_id
                );
            }

            //通证价
            if (app('plugins')->isEnabled('pass-price') && \Setting::get('pass-price.set.plugin_enable')) {
                $goodsModel->pass_price = [
                    'price' => bcmul($goodsModel->price, \Setting::get('pass-price.set.pass_price'), 2),
                    'name'  => \Setting::get('pass-price.set.diy_name') . '价',
                ];
            }

            $this->setGoodsLabel($goodsModel);
            $goodsModel->show_time_word = $this->setGoodsTradeSet($goodsModel->id);
        }
        $list = $list->toArray();

        //积分商城
        if (app('plugins')->isEnabled('point-mall')) {
            $list['data'] = \Yunshop\PointMall\api\models\PointMallGoodsModel::setPointGoods($list['data']);
        }

        return $list;
    }

    /**
     * 获取推荐分类
     * @return mixed
     */
    public function getRecommendCategoryList()
    {
        $request = Category::getRecommentCategoryList()
            ->where(['is_home' => '1', 'enabled' => '1'])
            ->orderBy('display_order', 'desc')
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
            ->where(['is_home' => '1', 'enabled' => '1'])
            ->where("level", "!=", 3)
            ->orderBy('display_order', 'desc')
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
        $list = Category::getChildrenCategorys($parent_id, $set)->where('enabled', 1);

        if (app('plugins')->isEnabled('address-code') && \Yunshop\AddressCode\services\SetService::pluginSwitch() && \YunShop::app()->getMemberId()) {
            $parent = Category::find($parent_id);
            //地址二维码筛选分类
            if ($parent->level == 1) {
                $categoryIds = \Yunshop\AddressCode\services\AddressService::addressCategoryIds(\Yunshop\AddressCode\services\AddressService::memberScanLog(\YunShop::app()->getMemberId()));
                if ($categoryIds) {
                    $list->whereIn('id',$categoryIds);
                }
            }
        }
        $list = $list->paginate($pageSize)->toArray();

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
                $list['goods_list'] = $this->getGoodsList($list['data'][0]['id'], 1);
            } else {
                $list['goods_list'] = $this->getGoodsList($list['data'][0]['has_many_children'][0]['id'], 1);
            }
        } else {
            $list['goods_list'] = [];
        }
        $set['cat_adv_img'] = replace_yunshop(yz_tomedia($set['cat_adv_img']));
        $list['set'] = $set;

        // 默认返回等级2
        if (empty($list['set']['cat_level'])) {
            $list['set']['cat_level'] = 2;
        }

        $superior = Category::uniacid()->where("id", $parent_id)->first();

        if ($superior['parent_id'] == 0) {
            $list['top_img'] = yz_tomedia($superior['adv_img']);
            $list['adv_url'] = $superior['adv_url'];
            $list['small_adv_url'] = $superior['small_adv_url'];
        } else {
            // $top_img = Category::uniacid()->where("id",$superior['parent_id'])->value("adv_img");
            $superior = Category::uniacid()->where("id", $superior['parent_id'])->first();
            $list['top_img'] = yz_tomedia($superior['adv_img']);
            $list['adv_url'] = $superior['adv_url'];
            $list['small_adv_url'] = $superior['small_adv_url'];
            // $list['top_img'] = empty($top_img) ? "" : yz_tomedia($top_img);
        }

        if ($list) {
            return $this->successJson('获取子分类数据成功!', $list);
        }
        return $this->errorJson('未检测到子分类数据!', $list);
    }

    public function searchGoodsCategory()
    {
        app('db')->cacheSelect = true;

        $set = \Setting::get('shop.category');
        $json_data = [];
        $list = Category::getCategorys(0)->pluginId()->where('enabled', 1)->get()->toArray();
        foreach ($list as &$parent) {
            $parent['son'] = Category::getChildrenCategorys($parent['id'], $set)->get()->toArray();
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
    public function fastCategory()
    {
        $list = Category::select('id', 'name', 'thumb', 'adv_img', 'adv_url')->uniacid()->where('level', 1)->where(
            'parent_id',
            0
        )->get();
        $list->map(function ($category) {
            $category->childrens = Category::select('id', 'name', 'thumb', 'adv_img', 'adv_url')->where(
                'level',
                2
            )->where('parent_id', $category->id)->get();
        });

        if ($list->isEmpty()) {
            throw new AppException('未检测到分类数据');
        }
        return $this->successJson('获取分类成功!', ['list' => $list->toArray()]);
    }

    private function setGoodsLabel(&$goods)
    {
        $goods['label_list'] = array();
        $filter_ids = $goods->hasManyGoodsFilter->pluck('filtering_id')->all();
//        $filter_ids = GoodsFiltering::where('goods_id', $goods['id'])->get()->pluck('filtering_id')->toArray();
        if (empty($filter_ids)) {
            return;
        }

        $goods['label_list'] = SearchFiltering::getAllEnableFiltering()->whereIn('id', $filter_ids)->where(
            'is_front_show',
            1
        )->values()->toArray();
        unset($goods->hasManyGoodsFilter);
    }

    private function setGoodsTradeSet($goods_id)
    {
        if (!app('plugins')->isEnabled('address-code')) {
            return '';
        }
        $goods_trade_set = GoodsTradeSet::where('goods_id', $goods_id)->first();
        if (!$goods_trade_set || !$goods_trade_set->arrived_day /*|| !app('plugins')->isEnabled('address-code')*/) {
            return '';
        }
        $arrived_day = $goods_trade_set->arrived_day;
        $arrived_word = $goods_trade_set->arrived_word;
        if ($arrived_day > 1) {
            $arrived_day -= 1;
            $time_format = Carbon::createFromTimestamp(time())->addDays($arrived_day)->format('Y-m-d');
        } else {
            $time_format = Carbon::createFromTimestamp(time())->format('Y-m-d');
        }
        $time_format .= " {$goods_trade_set->arrived_time}:00";
        $timestamp = strtotime($time_format);
        if ($timestamp < time()) {
            $timestamp += 86400;
        }
        $show_time = ltrim(date('m', $timestamp), '0').'月';
        $show_time .= ltrim(date('d', $timestamp), '0').'日';
        $show_time .= $goods_trade_set->arrived_time;
        return str_replace('[送达时间]', $show_time, $arrived_word);
    }
}
