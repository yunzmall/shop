<?php

namespace app\frontend\modules\goods\controllers;

use app\backend\modules\goods\models\Brand;
use app\common\components\ApiController;
use app\common\exceptions\AppException;
use app\common\facades\Setting;
use app\common\models\Category;
use app\common\models\goods\GoodsFiltering;
use app\common\models\SearchFiltering;
use app\framework\Http\Request;
use app\frontend\modules\goods\models\Goods;
use app\common\services\goods\SaleGoods;
use app\common\services\goods\VideoDemandCourseGoods;
use app\frontend\modules\goods\services\TradeGoodsPointsServer;
use Illuminate\Support\Facades\DB;
use Yunshop\Commission\Common\Services\GoodsDetailService;
use Yunshop\Designer\models\ViewSet;
use Yunshop\GoodsLink\services\GetGoodsDocService;
use Yunshop\Room\models\Room;
use Yunshop\StoreCashier\store\models\StoreGoods;
use Yunshop\TeamDividend\Common\Services\TeamDividendGoodsDetailService;
use Yunshop\Commission\models\Agents;
use Yunshop\Love\Common\Models\GoodsLove;
use app\frontend\modules\coupon\models\Coupon;
use app\frontend\modules\coupon\controllers\MemberCouponController;
use app\common\services\goods\LeaseToyGoods;
use Yunshop\TeamDividend\models\TeamDividendAgencyModel;
use app\common\models\MemberLevel;
use Yunshop\StoreCashier\common\models\StoreSetting;
use app\frontend\modules\goods\models\Comment;
use Yunshop\TeamSales\common\services\GoodsListService;
use Yunshop\Decorate\models\DecorateTempletModel;


class GoodsController extends ApiController
{
    protected $publicAction = ['getRecommendGoods','searchGoods'];
    protected $ignoreAction = ['getRecommendGoods','searchGoods'];

	public function __construct()
	{
		parent::__construct();
		$is_new_goods = 0;
		if (app('plugins')->isEnabled('decorate') && \Setting::get('plugin.decorate.is_open') == "1") {
			//商品模版
			if (!empty(\YunShop::request()->pc) && app('plugins')->isEnabled('pc-terminal')) {
				$pc_status = \Yunshop\PcTerminal\service\SetService::getPcStatus(); //PC端开启状态
			}

			if (!empty($pc_status)) {
				$view_set = DecorateTempletModel::getList(['is_default' => 1, 'type' => 6], '*', false);
				if ($view_set && $view_set->code == 'PCGoods02') {
					$is_new_goods = 1;
				}
			} else {
				$view_set = DecorateTempletModel::getList(['is_default' => 1, 'type' => 4], '*', false);
				if ($view_set && $view_set->code == 'goods02') {
					$is_new_goods = 1;
				}
			}
		}
		if ($is_new_goods == 1) {
			$this->publicAction[] = request()->route()->getActionMethod();
			$this->ignoreAction[] = request()->route()->getActionMethod();
		}
	}

    public function getGoodsPage()
    {

        $goods_id = request()->id;
        //查出商品模型
        $ims = DB::getTablePrefix();
        $goods_model = app('GoodsDetail')->make('Goods')->find($goods_id);
        if (is_null($goods_model)) {
            return $this->errorJson('商品不存在');
        }
        //设置商品详情主类
        app('GoodsDetail')->setDetailInstance($goods_model);
        //获取商品详情主类
        $detail_service = app('GoodsDetail')->make('GoodsDetailInstance');
        //初始化数据
        $detail_service->init($goods_model);

        //获取商品数据
        $detail_service->getData();

        return $this->successJson('成功', $detail_service->detail_data);
    }

    /**
     * todo 此方法需要优化，把插件的内容写到插件里面，通过配置文件读取
     * todo 插件关闭前端商城就不应该显示关闭插件的商品
     * @param $request
     * @param null $integrated
     * @return array|\Illuminate\Http\JsonResponse
     */
    public function getGoodsType(Request $request, $integrated = null)
    {
        app('db')->cacheSelect = true;
        $goods_type = 'goods';//通用
        $id = request()->id;
        if (!$id) {
            if (is_null($integrated)) {
                return $this->errorJson('请传入正确参数.');
            } else {
                return show_json(0, '请传入正确参数.');
            }
        }

        $goodsModel = Goods::uniacid()->find($id);

        $data['title'] = $goodsModel->title;
        // 商品详情挂件
        if (\app\common\modules\shop\ShopConfig::current()->get('goods_detail')) {
            foreach (\app\common\modules\shop\ShopConfig::current()->get('goods_detail') as $key_name => $row) {
                $row_res = $row['class']::{$row['function']}($id, true);
                if ($row_res) {
                    $goodsModel->$key_name = $row_res;
                }
            }
        }
        //判断该商品是否是视频插件商品
        $isCourse = (new VideoDemandCourseGoods())->isCourse($id);
        if ($isCourse) {
            $goods_type = 'course';
        }
        //判断是否酒店商品
        if ($goodsModel->plugin_id == 33) {
            $goods_type = 'hotelGoods';
        }
        if ($goodsModel->plugin_id == 66) {
            $goods_type = 'voiceGoods';
        }
        //门店商品
        if ($goodsModel->plugin_id == 32 && $goodsModel->store_goods) {
            $goods_type = 'store_goods';
            $store_id = $goodsModel->store_goods->store_id;
            $data['store_id'] = $store_id;
            if (!is_null(\app\common\modules\shop\ShopConfig::current()->get('customer_development_judge'))) {
                $class = array_get(\app\common\modules\shop\ShopConfig::current()->get('customer_development_judge'), 'class');
                $function = array_get(\app\common\modules\shop\ShopConfig::current()->get('customer_development_judge'), 'function');
                $judge_res = $class::$function($goodsModel->id);
                if ($judge_res == 1) {
                    $data['customer_development'] = 1;
                }
            }
        }
        //供应商商品
        if ($goodsModel->plugin_id == 92 && $goodsModel->supplier) {
            $goods_type = 'supplierGoods';
        }
        //分期购车插件
        if ($goodsModel->plugin_id == 47) {
            $goods_type = 'staging_buy_car_goods';
        }
        //预约插件
        if ($goodsModel->plugin_id == 101) {
            $goods_type = 'appointment_goods';
        }
        //芸签电子合同插件
        if ($goodsModel->plugin_id == 103) {
            $goods_type = 'yun_sign_goods';
        }
        $data['goods_type'] = $goods_type;

        if (is_null($integrated)) {
            return $this->successJson('成功', $data);
        } else {
            return show_json(1, $data);
        }
    }

    /**
     * @param $goodsModel
     * @param $member
     * @throws \app\common\exceptions\AppException
     */


    private function setGoodsPluginsRelations($goods)
    {
        $goodsRelations = app('GoodsManager')->tagged('GoodsRelations');

        collect($goodsRelations)->each(function ($goodsRelation) use ($goods) {
            $goodsRelation->setGoods($goods);
        });
    }

    protected function getGoodsModel()
    {
        $goods_model = \app\common\modules\shop\ShopConfig::current()->get('goods.models.commodity_classification');
        return new $goods_model;
    }

    public function searchGoods()
    {
        app('db')->cacheSelect = false;
        $requestSearch = \YunShop::request()->search;
        $plugin_id = \YunShop::request()->plugin_id;//搜索特定插件商品
        $order_field = \YunShop::request()->order_field;
        $goods_model = $this->getGoodsModel();
        if (!in_array($order_field, ['price', 'show_sales', 'comment_num', 'min_price', 'max_price'])) {
            $order_field = 'display_order';
        } else {
            if ($order_field == 'show_sales') {
                //排序改为虚拟销量
                $order_field = 'total_sales';
            }
        }
        $order_by = (\YunShop::request()->order_by == 'asc') ? 'asc' : 'desc';
        if ($requestSearch) {
            $requestSearch = array_filter($requestSearch, function ($item) {
                return !empty($item) && $item !== 0  && $item !== "undefined";
            });
            $categorySearch = array_filter(\YunShop::request()->category, function ($item) {
                return !empty($item);
            });
            if ($categorySearch) {
                $requestSearch['category'] = $categorySearch;
            }
        }
        //增加默认搜索不隐藏的商品
        $requestSearch['is_hide'] = 1;
        $ims = DB::getTablePrefix();
        $goods_select = $ims . 'yz_goods.has_option,' . $ims . "yz_goods.id,thumb,plugin_id,real_sales+virtual_sales total_sales,market_price,price,min_price,max_price,cost_price,title," . $ims . "yz_goods.stock, " . $ims . "yz_goods.id as goods_id";
        $option_select = 'goods_id,product_price,market_price,stock,cost_price';
        $build = $goods_model->SearchList($requestSearch)
            ->selectRaw($goods_select)
            ->with(['hasManyOptions' => function ($query) use ($option_select) {
                $query->selectRaw($option_select);
            }])
            ->where("yz_goods.status", 1);
        if (app('plugins')->isEnabled('good-style')) {
            $build = $build->with(['goodStyle' => function ($query) {
                $query->selectRaw('goods_id,current_logo,current_name');
            }]);
        }
        if ($plugin_id) {
            $build->where('plugin_id', $plugin_id);
        } else {
            $build->whereInPluginIds();
        }
        $list = $build->orderBy($order_field, $order_by)
            ->orderBy('yz_goods.id', 'desc')
            ->paginate(20);
        if ($list->isEmpty()) {
            return $this->errorJson('没有找到商品.');
        }
        //TODO 租赁插件是否开启 $lease_switch
        $lease_switch = LeaseToyGoods::whetherEnabled();
        //由于之前 foreach 太多了 合在一起
        $list->map(function ($item) use ($lease_switch) {
            $item->thumb = yz_tomedia($item->thumb);
            //租赁商品
            $this->goods_lease_set($item, $lease_switch);
            // 商品标签
            $this->setGoodsLabel($item);
            $item->id = $item->goods_id;
            if (Setting::get('goods.profit_show_status')) {
                if ($item->has_option) {
                    if ($item->hasManyOptions && $item->hasManyOptions->isNotEmpty()) {
                        $item->hasManyOptions = $item->hasManyOptions->each(function ($option) {
                            $option->goods_profit = bcsub($option->product_price, $option->cost_price, 2);
                            if (bccomp($option->goods_profit, 0, 2) == -1) {
                                $option->goods_profit = 0;
                            }
                        });
                        $item->min_goods_profit = $item->hasManyOptions->min('goods_profit') ?: 0;
                    } else {
                        $item->min_goods_profit = 0;
                    }
                } else {
                    $item->goods_profit = bcsub($item->price, $item->cost_price, 2);
                    if (bccomp($item->goods_profit, 0, 2) == -1) {
                        $item->goods_profit = 0;
                    }
                    $item->min_goods_profit = $item->goods_profit;
                }
            }
            //会员vip价格
            $item->vip_price = $item->vip_price;
            $item->vip_next_price = $item->vip_next_price;
            $item->vip_level_status = $item->vip_level_status;
            $item->price_level = $item->price_level;
            $item->is_open_micro = $item->is_open_micro;
            $item->goods_points = $this->setGoodPoints($item);
            return $item;
        });
        $list = $list->toArray();
        foreach ($list['data'] as &$v){
            if ($v['has_option'] && $v['has_many_options']){
                $v['stock'] = array_sum(array_column($v['has_many_options'],'stock'));
            }
        }
        unset($v);
        $category_data = [
            'names' => '01',
            'type' => 'goodsList',
        ];

        if (app('plugins')->isEnabled('decorate') && \Setting::get('plugin.decorate.is_open') == 1) {
            $view_set = \Yunshop\Decorate\models\DecorateTempletModel::uniacid()
                ->whereIn('code', ['goodsList01', 'goodsList02', 'goodsList03'])
                ->where('type', 5)
                ->where('is_default', 1)
                ->first();
            $tmp = [
                'goodsList01' => '01',
                'goodsList02' => '02',
                'goodsList03' => '03',
            ];
            $category_data = $view_set ? ['names' => $tmp[$view_set->code], 'type' => 'goodsList'] : $category_data;
            if ($view_set) {
                $list['data'] = $this->getGoodsCouponSale($list['data']);
                //团队销售佣金一级分销
                if (app('plugins')->isEnabled('team-sales')) {
                    $list['data'] = GoodsListService::getFirstDividend($list['data']);
                }
            }
            if ($category_data['names'] == '03') {
                if (app('plugins')->isEnabled('love')) {//爱心值天天兑价
                    $list['data'] = \Yunshop\Love\Frontend\Models\GoodsLove::setGoodsLove($list['data']);
                }
            }
        } elseif (app('plugins')->isEnabled('designer')) {
            //商品分类模板
            $view_set = ViewSet::uniacid()->where('type', 'goodsList')->select('names', 'type')->first();
            $category_data = $view_set ?: $category_data;
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
        //通证价计算
        if (app('plugins')->isEnabled('pass-price') && \Setting::get('pass-price.set.plugin_enable')) {
            $list['data'] = \Yunshop\PassPrice\services\PassPriceService::getPass($list['data']);
        }
        //积分商城
        if (app('plugins')->isEnabled('point-mall')) {
            $list['data'] = \Yunshop\PointMall\api\models\PointMallGoodsModel::setPointGoods($list['data']);
        }
        $list['goods_template'] = $category_data;
        $goods_style_set = Setting::get('plugin.good_style');
        if ($goods_style_set) {
            $goods_style_set['current_thumb'] = yz_tomedia($goods_style_set['current_thumb']);
            $goods_style_set['member_thumb'] = yz_tomedia($goods_style_set['member_thumb']);
        }
        $list['goods_style_set'] = $goods_style_set;
        return $this->successJson('成功', $list);
    }

    private function getGoodsCouponSale($goods)
    {
        //$memberLevel = MemberShopInfo::where('member_id', \YunShop::app()->getMemberId())->with(['level'])->first(); 去除优惠券等级限制
        $coupon = \app\common\models\Coupon::uniacid()->where('use_type', 2)->where('get_type', 1)->get();
        foreach ($goods as $k => &$v) {
            $v['coupon']['coupon_method'] = 0;                                   //商品没有折扣
            foreach ($coupon as $key => $value) {
                if ($value->time_limit == 1) {
                    if (time() < $value->time_statt || time() > $value->time_end) continue;
                }
                if (in_array($v['goods_id'], $value['goods_ids']) || $v['goods_id'] == $value['goods_ids']) {
                    if ($value['coupon_method'] == 1) {
                        $v['coupon']['deduct_price'] = bcsub($v['price'], $value['deduct'], 2);  //立减折扣
                        $v['coupon']['coupon_method'] = $value['coupon_method'];
                        $v['coupon']['deduct'] = $value['deduct'];
                        $v['coupon']['discount'] = $value['discount'];
                    } else if ($value['coupon_method'] == 2) {
                        $v['coupon']['deduct_price'] = bcmul($v['price'], $value['discount'] / 10, 2); //打折优惠
                        $v['coupon']['coupon_method'] = $value['coupon_method'];
                        $v['coupon']['discount'] = $value['discount'];
                        $v['coupon']['deduct'] = $value['deduct'];
                    }
                    if ($v['coupon']['deduct_price'] < 0) {
                        $v['coupon']['deduct_price'] = 0;
                    }
                    if ($v['coupon']['enough'] > 0 && $v['price'] < $value['enough']) {
                        $v['coupon']['deduct_price'] = 0;
                    }
                }
            }
        }
        return $goods;
    }

    public function getGoodsCategoryList()
    {
        $category_id = intval(\YunShop::request()->category_id);

        if (empty($category_id)) {
            return $this->errorJson('请输入正确的商品分类.');
        }

        $order_field = \YunShop::request()->order_field;
        if (!in_array($order_field, ['price', 'show_sales', 'comment_num'])) {
            $order_field = 'display_order';
        }

        $order_by = (\YunShop::request()->order_by == 'asc') ? 'asc' : 'desc';

        $categorys = Category::uniacid()->select("name", "thumb", "id")->where(['id' => $category_id])->first();

        if ($categorys) {
            $categorys->thumb = yz_tomedia($categorys->thumb);
        }

        $goodsList = Goods::uniacid()->select('yz_goods.id', 'yz_goods.id as goods_id', 'title', 'thumb', 'price', 'market_price')
            ->join('yz_goods_category', 'yz_goods_category.goods_id', '=', 'yz_goods.id')
            ->where("category_id", $category_id)
            ->where('status', '1')
            ->orderBy($order_field, $order_by)
            ->paginate(20)->toArray();


        if (empty($goodsList)) {
            return $this->errorJson('此分类下没有商品.');
        }
        $goodsList['data'] = set_medias($goodsList['data'], 'thumb');

        $categorys->goods = $goodsList;

        return $this->successJson('成功', $categorys);
    }

    public function getGoodsBrandList()
    {
        $goods_model = \app\common\modules\shop\ShopConfig::current()->get('goods.models.commodity_classification');
        $goods_model = new $goods_model;
        $brand_id = intval(\YunShop::request()->brand_id);
        $order_field = \YunShop::request()->order_field;
        $search['keyword'] = \YunShop::request()->keywords;
        if (!in_array($order_field, ['price', 'show_sales', 'comment_num'])) {
            $order_field = 'display_order';
        }
        $order_by = (\YunShop::request()->order_by == 'asc') ? 'asc' : 'desc';
        if (empty($brand_id)) {
            return $this->errorJson('请输入正确的品牌id.');
        }
        $brand = Brand::uniacid()->select("name", "logo", "id")->where(['id' => $brand_id])->first();

        if (!$brand) {
            return $this->errorJson('没有此品牌.');
        }
        $brand->logo = yz_tomedia($brand->logo);
        $build = $goods_model->uniacid()->select('id', 'id as goods_id', 'title', 'thumb', 'price', 'market_price')
            ->where('status', '1')
            ->where('brand_id', $brand_id)
            ->search($search)
            ->whereInPluginIds();
        if (app('plugins')->isEnabled('good-style')) {
            $build = $build->with(['goodStyle' => function ($query) {
                $query->selectRaw('goods_id,current_logo,current_name');
            }]);
        }
        $goodsList = $build->orderBy($order_field, $order_by)->paginate(20)->toArray();
        $goodsList->vip_level_status;
        if (empty($goodsList)) {
            return $this->errorJson('此品牌下没有商品.');
        }
        $goodsList['data'] = set_medias($goodsList['data'], 'thumb');
        $category_data = [
            'names' => '01',
            'type' => 'goodsList',
        ];
        if (app('plugins')->isEnabled('decorate') && \Setting::get('plugin.decorate.is_open') == 1) {
            $view_set = \Yunshop\Decorate\models\DecorateTempletModel::uniacid()
                ->whereIn('code', ['goodsList01', 'goodsList02', 'goodsList03'])
                ->where('type', 5)
                ->where('is_default', 1)
                ->first();
            $tmp = [
                'goodsList01' => '01',
                'goodsList02' => '02',
                'goodsList03' => '03',
            ];
            $category_data = $view_set ? ['names' => $tmp[$view_set->code], 'type' => 'goodsList'] : $category_data;
            if ($view_set) {
                $goodsList['data'] = $this->getGoodsCouponSale($goodsList['data']);
                //团队销售佣金一级分销
                if (app('plugins')->isEnabled('team-sales')) {
                    $goodsList['data'] = GoodsListService::getFirstDividend($goodsList['data']);
                }
            }
            if ($category_data['names'] == '03') {
                if (app('plugins')->isEnabled('love')) {//爱心值天天兑价
                    $goodsList['data'] = \Yunshop\Love\Frontend\Models\GoodsLove::setGoodsLove($goodsList['data']);
                }
            }
        } elseif (app('plugins')->isEnabled('designer')) {
            //商品分类模板
            // $view_set = ViewSet::uniacid()->where('type', 'goodsList')->first();
            $view_set = ViewSet::uniacid()->where('type', 'goodsList')->select('names', 'type')->first();
            $category_data = $view_set ?: $category_data;
            if (!empty($view_set) && $view_set->names == '02') {
                $goodsList['data'] = $this->getGoodsCouponSale($goodsList['data']);
                //团队销售佣金一级分销
                if (app('plugins')->isEnabled('team-sales')) {
                    $goodsList['data'] = GoodsListService::getFirstDividend($goodsList['data']);
                }
            }
        }
        //增加商品链接
        if (app('plugins')->isEnabled('goods-link')) {
            $goodsList['data'] = GetGoodsDocService::getDoc($goodsList['data']);
        }
        //积分商城
        if (app('plugins')->isEnabled('point-mall')) {
            $goodsList['data'] = \Yunshop\PointMall\api\models\PointMallGoodsModel::setPointGoods($goodsList['data']);
        }
        $goodsList['goods_template'] = $category_data;
        $goods_style_set = Setting::get('plugin.good_style');
        if ($goods_style_set) {
            $goods_style_set['current_thumb'] = yz_tomedia($goods_style_set['current_thumb']);
            $goods_style_set['member_thumb'] = yz_tomedia($goods_style_set['member_thumb']);
        }
        $brand->goods_style_set = $goods_style_set;
        $brand->goods = $goodsList;
        return $this->successJson('成功', $brand);
    }

    public function getRecommendGoods()
    {
        $list = Goods::uniacid()
            ->select('id', 'id as goods_id', 'title', 'thumb', 'price', 'market_price')
            ->where('is_recommand', '1')
            ->whereStatus('1')
            ->orderBy('id', 'desc')
            ->get();

        if (!$list->isEmpty()) {
            $list = set_medias($list->toArray(), 'thumb');
        }

        return $this->successJson('获取推荐商品成功', $list);
    }

    /**
     * 会员折扣后的价格
     * @param Goods $goodsModel
     * @param  [type] $discountModel [description]
     * @return array [type]                [description]
     */
    public function getDiscount($goodsModel, $memberModel)
    {
        if ($goodsModel->vip_price === null) {
            return [];
        }
        $discount_switch = Setting::get('shop.member.discount');
        $level_type = Setting::get('shop.member.level_type');
        $display_page = Setting::get('shop.member.display_page');
        if ($memberModel->level) {
            $data = [
                'level_name' => $memberModel->level->level_name,
                'discount_value' => $goodsModel->vip_price,
                'discount' => $discount_switch,
                'next_level_price' => $goodsModel->next_level_price,
                'next_level_name' => $goodsModel->next_level_name,
                'level_type' => $level_type,
                'display_page' => $display_page
            ];
        } else {
            $level = Setting::get('shop.member.level_name');
            $level_name = $level ?: '普通会员';

            $data = [
                'level_name' => $level_name,
                'discount_value' => $goodsModel->vip_price,
                'discount' => $discount_switch,
                'next_level_price' => $goodsModel->next_level_price,
                'next_level_name' => $goodsModel->next_level_name ?: MemberLevel::value('level_name'),
                'level_type' => $level_type,
                'display_page' => $display_page
            ];
        }

        return $data;
    }

    public function getGoodsSaleV2($goodsModel, $member)
    {
        $sale = [];
        //商城积分设置
        $set = \Setting::get('point.set');

        //获取商城设置: 判断 积分、余额 是否有自定义名称
        $shopSet = \Setting::get('shop.shop');


        if ($goodsModel->hasOneSale->ed_num || $goodsModel->hasOneSale->ed_money) {
            $data['name'] = '包邮';
            $data['key'] = 'ed_num';
            $data['type'] = 'array';
            if ($goodsModel->hasOneSale->ed_num) {
                $data['value'][] = '本商品满' . $goodsModel->hasOneSale->ed_num . '件包邮';
            }

            if ($goodsModel->hasOneSale->ed_money) {
                $data['value'][] = '本商品满￥' . $goodsModel->hasOneSale->ed_money . '包邮';

            }
            array_push($sale, $data);
            $data = [];
        }

        if ($goodsModel->hasOneSale->all_point_deduct && $goodsModel->hasOneSale->has_all_point_deduct) {//商品设置
            $data['name'] = $shopSet['credit1'] ? $shopSet['credit1'] . '全额抵扣' : '积分全额抵扣';
            $data['key'] = 'all_point_deduct';
            $data['type'] = 'string';
            $data['value'] = '可使用' . $goodsModel->hasOneSale->all_point_deduct . '个' . ($shopSet['credit1'] ? $shopSet['credit1'] . '全额抵扣购买' : '积分全额抵扣购买');
            array_push($sale, $data);
            $data = [];
        }


        if ((bccomp($goodsModel->hasOneSale->ed_full, 0.00, 2) == 1) && (bccomp($goodsModel->hasOneSale->ed_reduction, 0.00, 2) == 1)) {
            $data['name'] = '满减';
            $data['key'] = 'ed_full';
            $data['type'] = 'string';
            $data['value'] = '本商品满￥' . $goodsModel->hasOneSale->ed_full . '立减￥' . $goodsModel->hasOneSale->ed_reduction;
            array_push($sale, $data);
            $data = [];
        }

        if ($goodsModel->hasOneSale->award_balance) {
            $data['name'] = $shopSet['credit'] ?: '余额';
            $data['key'] = 'award_balance';
            $data['type'] = 'string';
            $data['value'] = '购买赠送' . $goodsModel->hasOneSale->award_balance . $data['name'];
            array_push($sale, $data);
            $data = [];
        }


        $point = [];
        if (app('plugins')->isEnabled('store-cashier')) {//门店抵扣设置
            $store_goods = StoreGoods::where('goods_id', $goodsModel->id)->first();
            $point = StoreSetting::getStoreSettingByStoreId($store_goods->store_id)->where('key', 'point')->first();
        }

        $data['name'] = $shopSet['credit1'] ?: '积分';
        $data['key'] = 'point';
        $data['type'] = 'array';

        if ((strlen($goodsModel->hasOneSale->point) === 0) || $goodsModel->hasOneSale->point != 0) {
            if ($goodsModel->hasOneSale->point) {
                $points = $goodsModel->hasOneSale->point;
            } elseif (!empty($point['value']['set']['give_point']) && $point['value']['set']['give_point'] != 0) {//门店抵扣设置
                $points = $point['value']['set']['give_point'] . '%';
            } else {
                $points = $set['give_point'] ? $set['give_point'] : 0;
            }
            if (!empty($points)) {
                $data['value'][] = '购买赠送' . $points . $data['name'];
            }
        }
        //设置不等于0,支持积分抵扣
        //积分抵扣优先级 商品独立设置 ---> 门店设置 ---> 积分统一设置

        if ($set['point_deduct'] && (strlen($goodsModel->hasOneSale->max_point_deduct) === 0 || $goodsModel->hasOneSale->max_point_deduct != 0)) {
            if ($goodsModel->hasOneSale->max_point_deduct) {
                $max_point_deduct = $goodsModel->hasOneSale->max_point_deduct . '元';
            } elseif (strlen($point['value']['set']['money_max']) !== 0) {
                if (!($point['value']['set']['money_max'] === 0 || $point['value']['set']['money_max'] === '0')) {
                    $max_point_deduct = $point['value']['set']['money_max'] . '%';
                }
            } else {
                $max_point_deduct = $set['money_max'] ? $set['money_max'] . '%' : 0;
            }

            if (!empty(mb_substr($max_point_deduct, 0, -1))) {
                $data['value'][] = '最高抵扣' . $max_point_deduct;
            }
        }

        if ($set['point_deduct'] && (strlen($goodsModel->hasOneSale->min_point_deduct) === 0 || $goodsModel->hasOneSale->min_point_deduct != 0)) {
            if ($goodsModel->hasOneSale->min_point_deduct) {
                $min_point_deduct = $goodsModel->hasOneSale->min_point_deduct . '元';
            } else {
                $min_point_deduct = $set['money_min'] ? $set['money_min'] . '%' : 0;
            }
            if (!empty(mb_substr($min_point_deduct, 0, -1))) {
                $data['value'][] = '最少抵扣' . $min_point_deduct;
            }
        }


        if (!empty($data['value'])) {
            array_push($sale, $data);
        }
        $data = [];

        if ($goodsModel->hasOneGoodsCoupon->is_give) {
            $data['name'] = '购买返券';
            $data['key'] = 'coupon';
            $data['type'] = 'string';
            $data['value'] = $goodsModel->hasOneGoodsCoupon->send_type ? '商品订单完成返优惠券' : '每月一号返优惠券';
            array_push($sale, $data);
            $data = [];
        }

        //爱心值
        $exist_love = app('plugins')->isEnabled('love');
        if ($exist_love) {

            $love_goods = $this->getLoveSet($goodsModel, $goodsModel->id);
            $data['name'] = $love_goods['name'];
            $deduction_name = \Yunshop\Love\Common\Services\SetService::usableLoveName();
            $reward_name = \Yunshop\Love\Common\Services\SetService::getRewardLoveName();
            $data['key'] = 'love';
            $data['type'] = 'array';
            if ($love_goods['deduction']) {
                $data['value'][] = '最高抵扣' . $love_goods['deduction_proportion'] . $deduction_name;
            }

            if ($love_goods['award'] && \Setting::get('love.goods_detail_show_love') != 2) {
                $data['value'][] = '购买赠送' . $love_goods['award_proportion'] . $reward_name;
            }

            if (!empty($data['value'])) {
                array_push($sale, $data);
            }
            $data = [];
        }

        //佣金
        $exist_commission = app('plugins')->isEnabled('commission');
        if ($exist_commission) {
            $is_agent = $this->isValidateCommission($member);
            if ($is_agent) {
                $commission_data = (new GoodsDetailService($goodsModel))->getGoodsDetailData();
                if ($commission_data['commission_show'] == 1) {
//                    $data['name'] = '佣金';
                    $data['key'] = 'commission';
                    $data['type'] = 'array';
                    $lang = isset($lang) ? $lang : \Setting::get('shop.lang');
                    $commission_alisa = array_get($lang, 'zh_cn.commission.commission_alias', '佣金') ?: '佣金';
                    $data['name'] = $commission_alisa;
                    $data['desc'] = '最终获得' . $commission_alisa . '根据等级、独立' . $commission_alisa . '设置、下单实际支付金额等因数决定,请以最终到账' . $commission_alisa . '为准!';

                    if (!empty($commission_data['first_commission']) && ($commission_data['commission_show_level'] > 0)) {
//                        $data['value'][] = '一级佣金' . $commission_data['first_commission'] . '元';
                        $data['value'][] = '一级' . $commission_alisa . $commission_data['first_commission'] . '元';
                    }
                    if (!empty($commission_data['second_commission']) && ($commission_data['commission_show_level'] > 1)) {
//                        $data['value'][] = '二级佣金' . $commission_data['second_commission'] . '元';
                        $data['value'][] = '二级' . $commission_alisa . $commission_data['second_commission'] . '元';
                    }
                    if (!empty($commission_data['third_commission']) && ($commission_data['commission_show_level'] > 2)) {
//                        $data['value'][] = '三级佣金' . $commission_data['third_commission'] . '元';
                        $data['value'][] = '三级' . $commission_alisa . $commission_data['third_commission'] . '元';
                    }
                    array_push($sale, $data);
                    $data = [];
                }
            }
        }

        //经销商提成
        $exist_team_dividend = app('plugins')->isEnabled('team-dividend');
        if ($exist_team_dividend) {
            //验证是否是经销商及等级
            $is_agent = $this->isValidateTeamDividend($member);
            if ($is_agent) {
//                $lang = \Setting::get('shop.lang');
                $lang = isset($lang) ? $lang : \Setting::get('shop.lang');
                $team_dividend_name = array_get($lang, 'zh_cn.team_dividend.title', '经销商') ?: '经销商';
                $dividend_alisa = array_get($lang, 'zh_cn.team_dividend.dividend_alias', $team_dividend_name . '提成') ?: $team_dividend_name . '提成';
                //返回经销商等级奖励比例  商品等级奖励规则
                $team_dividend_data = (new TeamDividendGoodsDetailService($goodsModel))->getGoodsDetailData();
                if ($team_dividend_data['team_dividend_show'] == 1) {
//                    $data['name'] = $team_dividend_name.'提成';
                    $data['name'] = $dividend_alisa;
                    $data['key'] = 'team-dividend';
                    $data['type'] = 'array';
//                    $data['value'][] = $team_dividend_name.'提成' . $team_dividend_data['team_dividend_royalty'];
                    $data['value'][] = $dividend_alisa . $team_dividend_data['team_dividend_royalty'];
                    array_unshift($sale, $data);
                    $data = [];
                }
            }

        }

        $exist_pending_order = app('plugins')->isEnabled('pending-order');
        if ($exist_pending_order) {
            $pending_order_goods = \Yunshop\PendingOrder\services\PendingOrderGoodsService::getGoodsWholesaleSend($goodsModel->id);
            $pending_order['name'] = '批发劵';
            $pending_order['key'] = 'pending-order';
            $pending_order['type'] = 'array';
            if ($pending_order_goods['send_condition']['code']) {
                $pending_order['value'][] = $pending_order_goods['send_condition']['msg'];
                array_push($sale, $pending_order);
            }
        }

        return [
            'sale_count' => count($sale),
//            'first_strip_key' => $sale ? $sale[rand(0, (count($sale) - 1))] : [],
            'first_strip_key' => $sale[0] ? $sale[0] : [],
            'sale' => $sale,
        ];
    }

    public function isValidateCommission($member)
    {
        return Agents::getAgentByMemberId($member->member_id)->first();
    }

    public function isValidateTeamDividend($member)
    {
        return TeamDividendAgencyModel::getAgencyByMemberId($member->member_id)->first();
    }

    /**
     * 商品的营销
     * @param  [type] $goodsModel [description]
     * @return [type]             [description]
     */
    public function getGoodsSale($goodsModel)
    {
        $set = \Setting::get('point.set');

        $shopSet = \Setting::get('shop.shop');

        if (!empty($shopSet['credit1'])) {
            $point_name = $shopSet['credit1'];
        } else {
            $point_name = '积分';
        }

        $data = [
            'first_strip_key' => 0,
            'point_name' => $point_name, //积分名称
            'love_name' => '爱心值',
            'ed_num' => 0,      //满件包邮
            'ed_money' => 0,    //满额包邮
            'ed_full' => 0,      //单品满额
            'ed_reduction' => 0, //单品立减
            'award_balance' => 0, //赠送余额
            'point' => 0,        //赠送积分
            'max_point_deduct' => 0, //积分最大抵扣
            'min_point_deduct' => 0, //积分最小抵扣
            'coupon' => 0,         //商品优惠券赠送
            'deduction_proportion' => 0, //爱心值最高抵扣
            'award_proportion' => 0, //奖励爱心值
            'sale_count' => 0,      //活动总数
        ];


        if (ceil($goodsModel->hasOneSale->ed_full) && ceil($goodsModel->hasOneSale->ed_reduction)) {
            $data['ed_full'] = $goodsModel->hasOneSale->ed_full;
            $data['ed_reduction'] = $goodsModel->hasOneSale->ed_reduction;

            $data['first_strip_key'] = 'ed_full';
            $data['sale_count'] += 1;

        }

        if ($goodsModel->hasOneSale->award_balance) {
            $data['award_balance'] = $goodsModel->hasOneSale->award_balance;

            $data['first_strip_key'] = 'award_balance';
            $data['sale_count'] += 1;

        }

        if ($goodsModel->hasOneSale->point !== '0') {

            $data['point'] = $set['give_point'] ? $set['give_point'] : 0;

            if ($goodsModel->hasOneSale->point) {
                $data['point'] = $goodsModel->hasOneSale->point;
            }

            if (!empty($data['point'])) {
                $data['first_strip_key'] = 'point';
                $data['sale_count'] += 1;
            }

        }

        if ($set['point_deduct'] && $goodsModel->hasOneSale->max_point_deduct !== '0') {

            $data['max_point_deduct'] = $set['money_max'] ? $set['money_max'] . '%' : 0;

            if ($goodsModel->hasOneSale->max_point_deduct) {

                $data['max_point_deduct'] = $goodsModel->hasOneSale->max_point_deduct;
            }
            if (!empty($data['max_point_deduct'])) {
                $data['first_strip_key'] = 'max_point_deduct';
                $data['sale_count'] += 1;
            }
        }
        if ($set['point_deduct'] && $goodsModel->hasOneSale->min_point_deduct !== '0') {

            $data['min_point_deduct'] = $set['money_min'] ? $set['money_min'] . '%' : 0;

            if ($goodsModel->hasOneSale->min_point_deduct) {

                $data['min_point_deduct'] = $goodsModel->hasOneSale->min_point_deduct;
            }
            if (!empty($data['min_point_deduct'])) {
                $data['first_strip_key'] = 'min_point_deduct';
                $data['sale_count'] += 1;
            }
        }
        if ($goodsModel->hasOneGoodsCoupon->is_give) {

            $data['coupon'] = $goodsModel->hasOneGoodsCoupon->send_type ? '商品订单完成返优惠券' : '每月一号返优惠券';

            $data['first_strip_key'] = 'coupon';
            $data['sale_count'] += 1;
        }

        if ($goodsModel->hasOneSale->ed_num) {
            $data['ed_num'] = $goodsModel->hasOneSale->ed_num;

            $data['first_strip_key'] = 'ed_num';
            $data['sale_count'] += 1;
        }

        if ($goodsModel->hasOneSale->ed_money) {
            $data['ed_money'] = $goodsModel->hasOneSale->ed_money;

            $data['first_strip_key'] = 'ed_money';
            $data['sale_count'] += 1;

        }

        $exist_love = app('plugins')->isEnabled('love');
        if ($exist_love) {
            $love_goods = $this->getLoveSet($goodsModel, $goodsModel->id);
            $data['love_name'] = $love_goods['name'];
            if ($love_goods['deduction']) {
                $data['deduction_proportion'] = $love_goods['deduction_proportion'];
                $data['first_strip_key'] = 'deduction_proportion';
                $data['sale_count'] += 1;
            }

            if ($love_goods['award']) {
                $data['award_proportion'] = $love_goods['award_proportion'];
                $data['first_strip_key'] = 'award_proportion';
                $data['sale_count'] += 1;
            }

        }
        $exist_commission = app('plugins')->isEnabled('commission');
        if ($exist_commission) {
            $commission_data = (new GoodsDetailService($goodsModel))->getGoodsDetailData();
            if ($commission_data['commission_show'] == 1) {
                $data['sale_count'] += 1;
                $data['first_strip_key'] = 'commission_show';
            }
            $data = array_merge($data, $commission_data);
        }
        return $data;
    }

    /**
     * 获取商品爱心值设置
     */
    public function getLoveSet($goods, $goods_id)
    {
        $data = [
            'name' => \Setting::get('love.name') ?: '爱心值',
            'deduction' => 0, //是否开启爱心值抵扣 0否，1是
            'deduction_proportion' => 0, //爱心值最高抵扣
            'award' => 0, //是否开启爱心值奖励 0否，1是
            'award_proportion' => 0, //奖励爱心值
        ];

        $love_set = \Setting::get('love');

        $res = app('plugins')->isEnabled('store-cashier');
        if ($res) {//门店抵扣设置
            $store_goods = StoreGoods::where('goods_id', $goods_id)->first();
            $love = StoreSetting::getStoreSettingByStoreId($store_goods->store_id)->where('key', 'love')->first();
            $set = \Setting::get('plugin.store_widgets', 'deduction_proportion');
//            dd($set['love']['deduction_proportion'],$love->value['deduction_proportion']);
        }

        $item = GoodsLove::ofGoodsId($goods->id)->first();
//         dd($item->deduction);
        $deduction = 0;
//            $deduction_proportion = (bccomp($item->deduction_proportion, 0.00, 2) == 1) ? $item->deduction_proportion : \Setting::get('love.deduction_proportion');
        $deduction_proportion = \Setting::get('love.deduction_proportion');


        if ($item->deduction) {//商品独立设置
            if ($love_set['deduction']) {
                $deduction_proportion = $love_set['deduction_proportion'];
                $deduction = $love_set['deduction'];
            }
//            if (!empty($set['love']['deduction'])){//平台设置
//                $deduction_proportion = $set['love']['deduction_proportion'];
//                $deduction = $set['love']['deduction'];
//            }
            // $price = $goods->price * ($deduction_proportion / 100);love[deduction_proportion_low]
            if (!empty($love) && $love->value['deduction_proportion'] && $love->value['deduction_proportion'] != 0) {//门店设置
                $deduction_proportion = $love->value['deduction_proportion'];
                $deduction = $love->value['deduction'];
            }

            if ($item->deduction_proportion && $item->deduction_proportion != 0) {
                $deduction_proportion = $item->deduction_proportion;
                $deduction = $item->deduction;
            }
            $data['deduction'] = $deduction;//$item->deduction;
            $data['deduction_proportion'] = $deduction_proportion . '%';

        }

//            $award_proportion = (bccomp($item->award_proportion, 0.00, 2) == 1) ? $item->award_proportion : \Setting::get('love.award_proportion');
        if ($item->award) {
            $award = $item->award;
            //爱心值插件设置
            $award_proportion = \Setting::get('love.award_proportion');

            //平台设置
//            if (!empty($set)){
//                $award_proportion = $set['love']['award_proportion'];
//                $award = $set['love']['award'];
//            }

            // $award_price = $goods->price * ($award_proportion / 100);
            //门店设置
            if (!empty($love) && $love->value['award_proportion'] && $love->value['award_proportion'] != 0) {
                $award_proportion = $love->value['award_proportion'];
                $award = $love->value['award'];
            }

            //商品独立设置
            if ($item->award_proportion && $item->award_proportion != 0) {
                $award_proportion = $item->award_proportion;//bccomp($item->award_proportion, 0.00, 2);
                $award = $item->award;
            }

            $data['award'] = $love_set['award'] ? $award : 0;//$item->award;
            $data['award_proportion'] = $award_proportion . '%';
        }
//        dd(\Setting::get('love.award_proportion'),$set['love']['award_proportion'],$love->value['award_proportion'],$item->award_proportion,bccomp(66, 0.00, 2));

        return $data;
    }

    /**
     * 是否开启领优惠卷
     * @param $member
     * @return \Illuminate\Http\JsonResponse|int
     */
    public function couponsMemberLj($member)
    {
        if (empty($member)) {
            throw new AppException('没有找到该用户');
        }
        $memberLevel = $member->level_id;

        $now = strtotime('now');
        $coupons = Coupon::getCouponsForMember($member->member_id, $memberLevel, null, $now)
            ->orderBy('display_order', 'desc')
            ->orderBy('updated_at', 'desc')
            ->get();
        if ($coupons->isEmpty()) {
            return 0;
        }

        foreach ($coupons as $v) {
            if (($v->total == MemberCouponController::NO_LIMIT) || ($v->has_many_member_coupon_count < $v->total)) {
                return 1;
            }
        }

        return 0;
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

    public function loveShoppingGift($goodsModel)
    {

        //爱心值
        $exist_love = app('plugins')->isEnabled('love');
        if ($exist_love) {
            $love_goods = $this->getLoveSet($goodsModel, $goodsModel->id);

            if ($love_goods['award'] && \Setting::get('love.goods_detail_show_love') == 2) {
                return '购买赠送' . $love_goods['award_proportion'] . $love_goods['name'];
            }
        }

        return '';
    }

    public function showPush()
    {
        $id = intval(\YunShop::request()->id);
        $goods = Goods::with('hasOneSale')->find($id);
        $show_push = SaleGoods::getPushGoods($goods->hasOneSale);//SaleGoods::getPushGoods($goodsModel->hasOneSale)
        $data['show_push'] = $show_push ?: [];
        $data['content'] = html_entity_decode($goods->content) ?: '';

        return $this->successJson('返回成功', $data);
    }

    public function getComment($goodsId)
    {
        app('db')->cacheSelect = true;
//        $goodsId = \YunShop::request()->goods_id;
        $pageSize = 5;
        $list = Comment::getCommentsByGoods($goodsId)->paginate($pageSize);//

        if ($list) {
            foreach ($list as &$item) {
                $item->nick_name = self::substrCut($item->nick_name);
                $item->reply_count = $item->hasManyReply->count('id');
                $item->head_img_url = $item->head_img_url ? replace_yunshop(yz_tomedia($item->head_img_url)) : yz_tomedia(\Setting::get('shop.shop.logo'));
            }
            //对评论图片进行处理，反序列化并组装完整图片url
            $list = $list->toArray();
            foreach ($list['data'] as &$item) {
                self::unSerializeImage($item);
            }
            return $list;
        }
        return $list;
    }

    /*
    * 获取商品好评率
    */
    public function favorableRate($id)
    {
        //不跟订单关联的新好评率计算公式
        $total = \app\common\models\Comment::where(['goods_id' => $id, 'type' => 1])->count('id');//总评论数
        if ($total <= 0) return '100%';

        $level_comment = \app\common\models\Comment::where(['goods_id' => $id, 'type' => 1])->sum('level');//已评论的分数
        $mark = bcmul($total, 5, 2);//最高总评分  = 总条数 * 5

        //最终好评率 = （已评论分数/最高总评分）/100
        $have_comment = bcmul(bcdiv($level_comment, $mark, 2), 100, 2);

        return $have_comment . '%';

//        $total = OrderGoods::with(['hasOneOrder',function($q){
//            $q->where('status',3);
//        }])->where('goods_id',$id)->count('id');//总条数
//
//        if ($total <= 0){
//            return '100%';
//        }
//        $level_comment = \app\common\models\Comment::where(['goods_id' => $id,'type'=>  1])->sum('level');//已评论的分数
//        $comment = \app\common\models\Comment::where(['goods_id' => $id,'type'=>1])->count('id');//总评论数
//        $mark = bcmul($total,5,2);//总评分  = 总条数 * 5
//        $no_comment = bcmul(bcsub($total,$comment,2) ,5,2);//未评分 = 总条数 - 已评论条数
//        $have_comment = bcmul(bcdiv(bcadd($level_comment,$no_comment,2),$mark,2),100,2);//最终好评率
//        //最终好评率 = （（已评论分数 + 未评分） / 总评分）/100
//        return $have_comment.'%';
    }

    // 反序列化图片
    public static function unSerializeImage(&$arrComment)
    {
        $arrComment['images'] = unserialize($arrComment['images']);
        foreach ($arrComment['images'] as &$image) {
            $image = yz_tomedia($image);
        }

//        if ($arrComment['append']) {
//            foreach ($arrComment['append'] as &$comment) {
//                $comment['images'] = unserialize($comment['images']);
//                foreach ($comment['images'] as &$image) {
//                    $image = yz_tomedia($image);
//                }
//            }
//        }

        if ($arrComment['append']) {
            $arrComment['append']['images'] = unserialize($arrComment['append']['images']);
            foreach ($arrComment['append']['images'] as &$image) {
                $image = yz_tomedia($image);
            }
        }

        if ($arrComment['has_many_reply']) {
            foreach ($arrComment['has_many_reply'] as &$comment) {
                $comment['images'] = unserialize($comment['images']);
                foreach ($comment['images'] as &$image) {
                    $image = yz_tomedia($image);
                }
            }
        }
    }

    //直播间
    public function getRoom()
    {
        app('db')->cacheSelect = true;
        $goods_id = intval(request()->goods_id);
        $rooms = Room::select('yz_room.*', 'yz_room_record_file.id as back_id')
            ->where(function ($querys) {
                $querys->whereIn('status', [2, 3])
                    ->orwhere(function ($query) {
                        $query->where('status', 4)
                            ->where('yz_room_record_file.id', '>', 0)
                            ->where('yz_room_record_file.is_show', 1);
                    });
            })
            ->leftJoin('yz_room_record_file', function ($join) {
                $join->on('yz_room_record_file.room_id', '=', 'yz_room.id');
            })
            ->with('hasOneMember')
            ->wherehas('hasManyGoods', function ($query) use ($goods_id) {
                $query->where('goods_id', $goods_id);
            })
            ->whereNull('yz_room_record_file.deleted_at')
            ->orderByRaw("FIELD(status, " . implode(", ", [3, 2, 4]) . ")")
            ->orderBy('yz_room.recommend', 'asc')
            ->orderBy('yz_room_record_file.recommend', 'asc')
            ->orderBy('yz_room.id', 'desc')
            ->paginate(10);
        $room = [];
        foreach ($rooms as $key => $val) {
            $room[$key]['avatar'] = $val->hasOneMember['avatar_image'];
            $room[$key]['nickname'] = $val->hasOneMember['nickname'];
            $room[$key]['id'] = $val->id;
            $room[$key]['status'] = $val->status;
            $room[$key]['title'] = $val->title;
            $room[$key]['cover'] = yz_tomedia($val->cover);
            $room[$key]['banner'] = yz_tomedia($val->banner);
            $room[$key]['live_time'] = $val->live_time;
            $room[$key]['view_num'] = $val->view_count + $val->virtual;
            if (empty($val->goods)) {
                $room[$key]['goods_num'] = 0;
            } else {
                $room[$key]['goods_num'] = count(explode(',', $val->goods));
            }
            if ($val->status == 2) {
                $room[$key]['play_type'] = 3;
            } elseif ($val->status == 3) {
                $room[$key]['play_type'] = 1;
            } else {
                $room[$key]['play_type'] = 2;
                $room[$key]['back_id'] = $val->back_id;
            }
        }
        $json = $rooms->toArray();
        $json['data'] = $room;
        return $this->successJson('成功', $json);
    }


    /**
     * 只保留字符串首尾字符，隐藏中间用*代替（两个字符时只显示第一个）
     * @param string $user_name 姓名
     * @return string 格式化后的姓名
     */
    function substrCut($user_name)
    {
        $strlen = mb_strlen($user_name, 'utf-8');
        $firstStr = mb_substr($user_name, 0, 1, 'utf-8');
        $lastStr = mb_substr($user_name, -1, 1, 'utf-8');
        if ($strlen < 2) {
            return $user_name;
        } else {
            return $strlen == 2 ? $firstStr . str_repeat('*', mb_strlen($user_name, 'utf-8') - 1) : $firstStr . str_repeat("*", $strlen - 2) . $lastStr;
        }
    }

    /**
     * @description 获取商品积分
     * @param $goodsModel
     * @return mixed|string
     */
    private function setGoodPoints($goodsModel)
    {
        $points = '';

        $tradeGoodsPointsServer = new TradeGoodsPointsServer();

        if ($tradeGoodsPointsServer->close(TradeGoodsPointsServer::SEARCH_PAGE)) {
            return $points;
        }

        $tradeGoodsPointsServer->getPointSet($goodsModel);
        $points = $tradeGoodsPointsServer->finalSetPoint($points);

        return $tradeGoodsPointsServer->getPoint($points, $goodsModel->price, $goodsModel->cost_price);
    }

    public function getLimitBuyGoods()
    {
        $page_size = request()->page_size ? : 20;
        $goods_model = \app\common\modules\shop\ShopConfig::current()->get('goods.models.commodity_classification');
        $goods_model = new $goods_model;
        $goods = $goods_model->uniacid()->select('yz_goods.id','yz_goods.title','yz_goods.thumb','yz_goods.market_price',
            'yz_goods.show_sales','yz_goods.virtual_sales','yz_goods.price','yz_goods.stock','yz_goods.has_option', 'yz_goods.plugin_id',
            'yz_goods_limitbuy.start_time','yz_goods_limitbuy.end_time')
            ->join('yz_goods_limitbuy',function ($join) {
                $join->on('yz_goods.id','=','yz_goods_limitbuy.goods_id')->where(function ($where) {
                    return $where->where('yz_goods_limitbuy.status',1)
                        ->where('yz_goods_limitbuy.start_time' ,'<=',time())
                        ->where('yz_goods_limitbuy.end_time' ,'>',time());
                });
            })
            ->with(['hasManyOptions' => function($query){
                $query->select('id', 'goods_id', 'title', 'thumb', 'product_price', 'market_price', 'stock', 'specs', 'weight');
            }])
            ->where('yz_goods.status',1)
            ->orderBy('yz_goods.is_recommand','desc')
            ->orderBy('yz_goods.display_order','desc')
            ->orderBy('yz_goods.id', 'desc');

        $goods = $goods->paginate($page_size);

        foreach ($goods as $good) {
            //前端需要goods_id
            $good->goods_id = $good->id;
            $good->buyNum = 0;
            $good->thumb = yz_tomedia($good->thumb);

            if ($good->has_option) {
                $good->min_price = $good->hasManyOptions->min("product_price");
                $good->max_price = $good->hasManyOptions->max("product_price");
                $good->stock = $good->hasManyOptions->sum('stock');
            }
        }
        return $this->successJson('成功', $goods->toArray());
    }

    // 获取商品标签
    private function setGoodsLabel(&$goods)
    {
        $goods['label_list'] = array();
        $filter_ids = GoodsFiltering::where('goods_id',$goods['id'])->get()->pluck('filtering_id')->toArray();
        if (empty($filter_ids)){
            return;
        }

        $goods['label_list'] = SearchFiltering::getAllEnableFiltering()->whereIn('id',$filter_ids)->where('is_front_show',1)->values()->toArray();
    }
}
