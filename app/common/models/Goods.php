<?php
/**
 * Created by PhpStorm.
 * Author: 芸众商城 www.yunzshop.com
 * Date: 2017/2/22
 * Time: 19:35
 */

namespace app\common\models;

use app\common\events\goods\GoodsStockNotEnoughEvent;
use app\common\facades\SiteSetting as SiteSettingFacades;
use app\common\models\goods\GoodsFiltering;
use app\frontend\modules\orderGoods\price\adapter\GoodsAdapterManager;
use app\frontend\modules\orderGoods\price\adapter\GoodsPriceAdapter;
use Illuminate\Support\Facades\Redis;
use app\common\exceptions\AppException;
use app\common\exceptions\GoodsStockNotEnough;
use app\common\models\goods\Discount;
use app\common\models\goods\GoodsDispatch;
use app\common\models\goods\GoodsLimitBuy;
use app\common\models\goods\GoodsVideo;
use app\common\models\goods\Privilege;
use app\common\modules\goods\GoodsPriceManager;
use app\common\models\goods\Share;
use app\common\services\wordanalysis\Analysis;
use app\framework\Database\Eloquent\Collection;
use app\frontend\modules\goods\stock\GoodsStock;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use app\common\modules\discount\GoodsMemberLevelDiscount;


/**
 * Class Goods
 * @package app\common\models
 * @property string status
 * @property string status_name
 * @property string title
 * @property int uniacid
 * @property int id
 * @property int stock
 * @property float max_price
 * @property float min_price
 * @property string thumb
 * @property string thumb_url
 * @property int buyNum
 * @property int has_option
 * @property int virtual_sales
 * @property int plugin_id
 * @property int comment_num
 * @property int is_comment
 * @property int is_recommand
 * @property int is_discount
 * @property int is_hot
 * @property int is_new
 * @property int weight
 * @property int real_sales
 * @property int show_sales
 * @property int reduce_stock_method
 * @property int cost_price
 * @property int price
 * @property int market_price
 * @property int product_sn
 * @property string goods_sn
 * @property string content
 * @property string description
 * @property string sku
 * @property int type
 * @property int brand_id
 * @property int goods_video
 * @property int display_order
 * @property float deal_price
 * @property float vip_price
 * @property float next_level_price
 * @property int is_open_micro
 * @property float vip_next_price
 * @property int price_level
 * @property int withhold_stock
 * @property string next_level_name
 * @property Collection hasManySpecs
 * @property Collection hasManyOptions
 * @property GoodsDiscount hasManyGoodsDiscount
 * @property GoodsDispatch hasOneGoodsDispatch
 * @property Privilege hasOnePrivilege
 * @property Brand hasOneBrand
 * @property GoodsLimitBuy hasOneGoodsLimitBuy
 * @property GoodsAdvertising hasOneGoodsAdvertising
 * @property GoodsVideo hasOneGoodsVideo
 * @property Share hasOneShare
 * @property Sale hasOneSale
 */
class Goods extends BaseModel
{

    use SoftDeletes;

    public $table = 'yz_goods';
    public $attributes = ['display_order' => 0];
    protected $mediaFields = ['thumb', 'thumb_url'];
    protected $dates = ['deleted_at'];
    protected $appends = ['status_name'];

    public $fillable = [];

    protected $guarded = ['widgets'];

    public $widgets = [];

    protected $search_fields = ['title'];

    static protected $needLog = true;
    private $dealPrice;
    protected $vipDiscountAmount;
    public $vipDiscountLog;
    public $nextLevelName = '';
    /**
     * 实物
     */
    const REAL_GOODS = 1;
    /**
     * 虚拟物品
     */
    const VIRTUAL_GOODS = 2;


    /*
        商品 plugin_id ：
            0  => 平台商品
            31 => 门店收银台
            32 => 门店商品
            40 => 租赁商品
            41 => 网约车商品
            42 => 网约车分红
            43 => ps 由于服务站订单使用了
            44 => 京东-供应链
            92 => 供应商商品
     */


    /**
     * 定义字段名
     *
     * @return array
     */
    public function atributeNames()
    {
        return ['title' => '商品名称', 'price' => '价格', 'cost_price' => '成本价', 'sku' => '商品单位', 'thumb' => '图片', 'weight' => '重量', 'stock' => 'required|numeric|min:0',];
    }

    /**
     * 字段规则
     *
     * @return array
     */
    public function rules()
    {
        return ['title' => 'required', 'price' => 'required|numeric|min:0', 'cost_price' => 'required|numeric|min:0', 'sku' => 'required', 'thumb' => 'required', 'weight' => 'required', 'stock' => 'required|numeric|min:0',];
    }


    public static function getList()
    {
        return static::uniacid();
    }

    public static function getGoodsById($id)
    {
        return static::find($id);
    }

    public static function getGoodsByIds($ids)
    {
        return self::whereIn('id', $ids)->get();
    }

    public function hasManyParams()
    {
        return $this->hasMany('app\common\models\GoodsParam');
    }

    public function belongsToCategorys()
    {
        return $this->hasMany('app\common\models\GoodsCategory', 'goods_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function hasManyGoodsDiscount()
    {
        return $this->hasMany('app\common\models\GoodsDiscount');
    }

    public function hasManyOptions()
    {
        return $this->hasMany('app\common\models\GoodsOption');
    }

    public function hasOneBrand()
    {
        return $this->hasOne('app\common\models\Brand', 'id', 'brand_id');
    }

    public function hasOneShare()
    {
        return $this->hasOne('app\common\models\goods\Share');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     * @throws \app\common\exceptions\ShopException
     */
    public function hasOnePrivilege()
    {
        return $this->hasOne($this->getNearestModel('goods\Privilege'));
    }

    public function hasOneGoodsDispatch()
    {
        return $this->hasOne('app\common\models\goods\GoodsDispatch');
    }

    //该条关联可能出错了不是一对一关系 是一对多
    public function hasOneDiscount()
    {
        return $this->hasOne('app\common\models\goods\Discount');
    }

    public function hasManyDiscount()
    {
        return $this->hasMany(Discount::class, 'goods_id', 'id');
    }

    public function hasManyGoodsCategory()
    {
        return $this->hasMany('app\common\models\GoodsCategory', 'goods_id', 'id');
    }

    public function hasManySpecs()
    {
        return $this->hasMany('app\common\models\GoodsSpec');
    }

    public function hasOneSale()
    {
        return $this->hasOne(app('GoodsManager')->make('GoodsSale'), 'goods_id', 'id');
    }

    public function hasOneGoodsCoupon()
    {
        return $this->hasOne('app\common\models\goods\GoodsCoupon', 'goods_id', 'id');
    }

    public function hasOneGoodsLimitBuy()
    {
        return $this->hasOne('app\common\models\goods\GoodsLimitBuy', 'goods_id', 'id');
    }

    public function hasOneGoodsAdvertising()
    {
        return $this->hasOne('app\common\models\goods\GoodsAdvertising', 'goods_id', 'id');
    }

    public function hasOneInvitePage()
    {
        return $this->hasOne('app\common\models\goods\InvitePage', 'goods_id', 'id');
    }

    public function hasOnePointActivity()
    {
        return $this->hasOne('Yunshop\PointActivity\Backend\Models\GoodsPointActivity', 'goods_id', 'id');
    }

    public function hasOneGoodsService()
    {
        return $this->hasOne('app\common\models\goods\GoodsService', 'goods_id', 'id');
    }

    public function hasOneGoodsVideo()
    {
        return $this->hasOne('app\common\models\goods\GoodsVideo', 'goods_id', 'id');
    }

    public function hasOneSmallCodeUrl()
    {
        return $this->hasOne(GoodsSmallUrl::class, 'goods_id', 'id');
    }

    public function scopePluginIdShow($query, $pluginId = [0])
    {
        return $query->whereIn('plugin_id', $pluginId);
    }

    public function scopeIsPlugin($query)
    {
        return $query->where('is_plugin', 0);
    }

    public function scopeWhereInPluginIds($query, $pluginIds = [])
    {
        if (empty($pluginIds)) {
            //标准商城默认都会显示下面这几种类型的商品
            $pluginIds = $this->showPluginGoods();
        }
        return $query->whereIn('plugin_id', $pluginIds);
    }

    protected function showPluginGoods()
    {
        $pluginIds = [0, 40, 41, 44, 52, 53, 92, 103];//todo 这些都要写到对应的插件里

        $plugin = \app\common\modules\shop\ShopConfig::current()->get('shop-foundation.goods.plugin');
        $plugin = array_merge($plugin, $pluginIds);
        return $plugin;
    }

    public function scopeSearch($query, $filters)
    {
        $query->uniacid();

        if (!$filters) {
            return;
        }
        foreach ($filters as $key => $value) {
            switch ($key) {
                /*case 'category':
                    $category[] = ['id' => $value * 1];
                    $query->with("")->where('category_id', $category);
                    break;*/
                //上架商品库存筛选
                case 'sell_stock':
                    if ($value) {
                        $query->where('yz_goods.status', 1)->where('yz_goods.stock', '>', 0);
                    } else {
                        $query->where('yz_goods.status', 1)->where('yz_goods.stock', '=', 0);
                    }
                    break;
                //新加过滤搜索
                case 'filtering':
                    $scope = explode(',', rtrim($value, ','));

                    $goodsFiltering = GoodsFiltering::select('goods_id')->whereIn('filtering_id', $scope)->get();
                    $goods_ids = $goodsFiltering->pluck('goods_id')->unique()->toArray();
                    if ($goods_ids) {
                        $query->whereIn('yz_goods.id', $goods_ids);
                    }
                    break;
                case 'keyword':
                    $splice_word = Analysis::getKeywords($value, 10);
                    $ims = DB::getConfig('prefix');//获取前缀
                    if (empty($splice_word)) {
                        $splice_search = $ims.'yz_goods.title like "%' . $value . '%"';
                    } else {
                        $splice_search = '';
                        $splice_array = explode(',', $splice_word);

                        foreach ($splice_array as $val) {
                            $splice_search .= $ims.'yz_goods.title like "%' . $val . '%" and ';
                        }

                        $splice_search = '(' . rtrim($splice_search, 'and ') . ')';
                    }

                    $query->whereRaw('(' . $splice_search . ' or  ' . DB::getTablePrefix() . 'yz_goods.`id` = ?)', [$value]);
                    break;
                case 'goods_id':
                    $query->where('yz_goods.id', '=', $value);
                    break;
                case 'brand_id':
                    $query->where('brand_id', $value);
                    break;
                case 'product_attr':
                    //前端传参是 string 类型，后端传参是 array 类型
                    if (!is_array($value)) {
                        $value = explode(',', rtrim($value, ','));
                    }
                    //$value = explode(',', rtrim($value, ','));
                    foreach ($value as $attr) {
                        if ($attr == 'limit_buy') {
                            $query->join('yz_goods_limitbuy', function ($join) {
                                $join->on('yz_goods_limitbuy.goods_id', '=', 'yz_goods.id')->where('yz_goods_limitbuy.status', 1);
                            });
//                            $query->whereHas('hasOneGoodsLimitBuy', function (BaseModel $q) {
//                                $q->where('status', 1);
//                            });
                        } else {
                            $query->where($attr, 1);
                        }
                    }
                    break;
                case 'status':
                    $query->where('yz_goods.status', $value);
                    break;
                case 'min_price':
                    $query->where('price', '>', $value);
                    break;
                case 'max_price':
                    $query->where('price', '<', $value);
                    break;
                case 'category':
                    if (array_key_exists('parentid', $value) || array_key_exists('childid', $value) || array_key_exists('thirdid', $value)) {
//                        $id = $value['parentid'][0] ? $value['parentid'][0] : '';
//                        $id = $value['childid'][0] ? $value['childid'][0] : $id;
//                        $id = $value['thirdid'][0] ? $value['thirdid'][0] : $id;
                        $id = $value['parentid'] ? $value['parentid'] : '';
                        $id = $value['childid'] ? $value['childid'] : $id;
                        $id = $value['thirdid'] ? $value['thirdid'] : $id;
                        $query->select(['yz_goods.*', 'yz_goods_category.id as goods_category_id', 'yz_goods_category.goods_id as goods_id', 'yz_goods_category.category_id as category_id', 'yz_goods_category.category_ids as category_ids'])->join('yz_goods_category', 'yz_goods_category.goods_id', '=', 'yz_goods.id')->whereRaw('FIND_IN_SET(?,category_ids)', [$id]);
                    } elseif (strpos($value, ',')) {
                        $scope = explode(',', $value);
                        $query->select(['yz_goods.*', 'yz_goods_category.id as goods_category_id', 'yz_goods_category.goods_id as goods_id', 'yz_goods_category.category_id as category_id', 'yz_goods_category.category_ids as category_ids'])->join('yz_goods_category', function ($join) use ($scope) {
                            $join->on('yz_goods_category.goods_id', '=', 'yz_goods.id');
                            $join->where(function ($join) use ($scope) {
                                foreach ($scope as $s) {
                                    $join->orWhereRaw('FIND_IN_SET(?,category_ids)', [$s]);
                                }
                            });
                        });
                    } else {
                        $query->select(['yz_goods.*', 'yz_goods_category.id as goods_category_id', 'yz_goods_category.goods_id as goods_id', 'yz_goods_category.category_id as category_id', 'yz_goods_category.category_ids as category_ids'])->join('yz_goods_category', function ($join) use ($value) {
                            $join->on('yz_goods_category.goods_id', '=', 'yz_goods.id')->whereRaw('FIND_IN_SET(?,category_ids)', [$value]);
//                                ->where('yz_goods_category.category_id', $value);
                        });
                    }
                    break;
                case 'couponid': //搜索指定优惠券适用的商品
                    $res = Coupon::getApplicableScope($value);
                    switch ($res['type']) {
                        case Coupon::COUPON_GOODS_USE: //优惠券适用于指定商品
                            if (is_array($res['scope'])) {
                                $query->whereIn('id', $res['scope']);
                            } else {
                                $query->where('id', $res['scope']);
                            }
                            break;
                        case 8:
                            if (is_array($res['scope'])) {
                                $query->whereIn('id', $res['scope']);
                            } else {
                                $query->where('id', $res['scope']);
                            }
                            break;
                        case Coupon::COUPON_CATEGORY_USE: //优惠券适用于指定商品分类
                            if (is_array($res['scope'])) {
                                $query->join('yz_goods_category', function ($join) use ($res) {
                                    $join->on('yz_goods_category.goods_id', '=', 'yz_goods.id')->whereIn('yz_goods_category.category_id', $res['scope']);
                                });
                            } else {
                                $query->join('yz_goods_category', function ($join) use ($res) {
                                    $join->on('yz_goods_category.goods_id', '=', 'yz_goods.id')->where('yz_goods_category.category_id', $res['scope']);
                                });
                            }
                            break;
                        default: //优惠券适用于整个商城
                            break;
                    }
                    break;
                case 'is_spec':
                    if ($value !== ''){
                        if ($value == 1){
                            $query->where('has_option',1)->select('yz_goods.*');
//                            $query->whereIn('yz_goods.id',function ($query) {
//                                $query->select('goods_id')->from('yz_goods_spec')->where('uniacid',\Yunshop::app()->uniacid);
//                            })->where('has_option',1)->select('yz_goods.*');
//                            $query->join('yz_goods_spec','yz_goods.id','yz_goods_spec.goods_id')->select('yz_goods.*','yz_goods_spec.goods_id');
                        }else{

                            $query->where('has_option',0)->select('yz_goods.*');
                        }
                    }
                    break;
                default:
                    break;
            }
        }
    }

    /**
     * @param $keyword
     * @return mixed
     */
    public static function getGoodsByName($keyword)
    {
        return static::uniacid()->select('id', 'title', 'thumb', 'market_price', 'price', 'real_sales', 'sku', 'plugin_id', 'stock')->where('title', 'like', '%' . $keyword . '%')->where('status', 1)//->where('is_plugin', 0)
            ->whereNotIn('plugin_id', [20, 31, 60])//屏蔽门店、码上点餐、第三方插件接口的虚拟商品
            ->get();
    }

    public static function getGoodsLevelByName($keyword)
    {
        return static::uniacid()->select('id', 'title', 'thumb', 'market_price', 'price', 'real_sales', 'sku', 'plugin_id', 'stock')->where('title', 'like', '%' . $keyword . '%')->where('status', 1)//->where('is_plugin', 0)
//            ->whereIn('plugin_id', ['0', '32', '92'])//屏蔽门店、码上点餐、第三方插件接口的虚拟商品
            ->get();
    }

    public static function getGoodsByNameLevel($keyword)
    {
        $where = function($query)use($keyword){
            if (!empty($keyword) && intval($keyword) == $keyword){
                return $query->where('title', 'like', '%' . $keyword . '%')->orWhere('id',$keyword);
            } else{
               return $query->where('title', 'like', '%' . $keyword . '%');
            }
        };

        return \app\common\models\Goods::select('id', 'title', 'thumb', 'market_price', 'price', 'real_sales', 'sku', 'plugin_id', 'stock')
            ->where($where)
//            ->where('title', 'like', '%' . $keyword . '%')
            ->where('status', 1)
            ->whereNotIn('plugin_id', [20, 60])//屏蔽门店、码上点餐、第三方插件接口的虚拟商品
            ->get();
    }


    /**
     * @param $keyword
     * @return mixed
     */
    public static function getGoodsByNames($keyword)
    {
        return static::uniacid()->select('id', 'title', 'thumb', 'market_price', 'price', 'real_sales', 'virtual_sales', 'sku', 'plugin_id', 'stock')->where('title', 'like', '%' . $keyword . '%')->where('status', 1)//->where('is_plugin', 0)
            ->whereNotIn('plugin_id', [20, 31, 60])//屏蔽门店、码上点餐、第三方插件接口的虚拟商品
            ->get();
    }

    /**
     * @param $keyword
     * @return mixed
     */
    public static function getGoodsByNameForLimitBuy($keyword)
    {

        return static::uniacid()->select('id', 'title', 'thumb', 'market_price', 'price', 'real_sales', 'sku', 'plugin_id', 'stock')->where('title', 'like', '%' . $keyword . '%')->where('status', 1)->with(['hasOneGoodsLimitBuy' => function ($query) {
                return $query->where('status', 1)->select('goods_id', 'start_time', 'end_time');
            }])->whereHas('hasOneGoodsLimitBuy', function ($query) {
                return $query->where('status', 1);
            })->whereNotIn('plugin_id', [20, 31, 60])//屏蔽门店、码上点餐、第三方插件接口的虚拟商品
            ->get();
    }

    /**
     * @param $goodsId
     * @return mixed
     */
    public static function updatedComment($goodsId)
    {

        return self::where('id', $goodsId)->update(['comment_num' => DB::raw('`comment_num` + 1')]);
    }

    /**
     * 判断实物
     * @return bool
     * @author shenyang
     */
    public function isRealGoods()
    {
        if (!isset($this->type)) {
            return false;
        }
        return $this->type == self::REAL_GOODS;
    }

    /**
     * 推广商品
     * @param $goodsIds
     * @return array
     */
    public static function getPushGoods($goodsIds)
    {
        return self::select('id', 'title', 'thumb', 'price', 'market_price')->whereIn('id', $goodsIds)->where('status', 1)->get()->toArray();
    }

    public static function boot()
    {
        parent::boot();

        static::addGlobalScope(function ($builder) {
            $builder->uniacid();
        });
    }

    public static function getGoodsByIdAll($goodsId)
    {
        $model = static::where('id', $goodsId);


        return $model;
    }

    public function getStatusNameAttribute()
    {

        return [0 => '下架', 1 => '上架'][$this->status];
    }

    /**
     * 商品购买验证
     * @param Member $member
     * @param $total
     * @throws AppException
     */
    public function generalValidate(Member $member, $total)
    {
        if (empty($this->status)) {
            throw new AppException('(ID:' . $this->id . ')商品已下架');
        }
//        if (!isset($this->hasOneSale)) {
//            throw new AppException('(ID:' . $this->id . ')商品优惠信息数据已损坏');
//        }
//        if (!isset($this->hasOneGoodsDispatch)) {
//            throw new AppException('(ID:' . $this->id . ')商品配送信息数据已损坏');
//        }
        if (isset($this->hasOnePrivilege)) {
            $this->hasOnePrivilege->validate($member, $total);
        }
        if (isset($this->hasOneGoodsLimitBuy)) {
            $this->hasOneGoodsLimitBuy->check();
        }
    }

    /**
     * 是否启用规格
     * @param $option_id
     * @throws AppException
     */
    public function verifyOption($option_id)
    {
        if ($this->has_option && empty($option_id)) {
            throw new AppException($this->title.'(ID:' . $this->id . ')商品未选择规格');
        }

    }

    /**
     * 获取商品名称
     * @return html
     */
    public static function getSearchOrder($keyword, $pluginId)
    {
//        $keyword = \YunShop::request()->keyword;
        return Goods::select(['id', 'title', 'thumb', 'plugin_id'])->pluginId($pluginId)->where('title', 'like', '%' . $keyword . '%')->get();
    }

    private $priceManager;

    public function getPriceManager()
    {
        if (!isset($this->priceManager)) {
            $this->priceManager = new GoodsPriceManager($this);
        }
        return $this->priceManager;
    }

    /**
     * 获取交易价(实际参与交易的商品价格)
     * @return float|int
     * @throws \app\common\exceptions\MemberNotLoginException
     */
    public function getDealPriceAttribute()
    {
        if (!isset($this->dealPrice)) {
//            $level_discount_set = SiteSettingFacades::get('discount.all_set');
//            if (
//                isset($level_discount_set['type'])
//                && $level_discount_set['type'] == 1
//                && $this->memberLevelDiscount()->getAmount($this->market_price)
//            ) {
//                // 如果开启了原价计算会员折扣,并且存在等级优惠金额
//                $this->dealPrice = $this->market_price;
//            } else {
//                // 默认使用现价
//                $this->dealPrice = $this->price;
//            }
            $this->dealPrice = $this->getPriceManager()->getDealPrice();
        }

        return $this->dealPrice;
    }


    /**
     * @var GoodsMemberLevelDiscount
     */
    private $memberLevelDiscount;

    /**
     * @return GoodsMemberLevelDiscount
     * @throws AppException
     */
    public function memberLevelDiscount()
    {
        if (!isset($this->memberLevelDiscount)) {
            if (\YunShop::app()->getMemberId()) {
                $member = \app\frontend\models\Member::current();
            } else {
                $member = new \app\frontend\models\Member();
            }
            $this->memberLevelDiscount = new GoodsMemberLevelDiscount($this, $member);
        }
        return $this->memberLevelDiscount;
    }


    //todo blank 商品价格适配器
    public function getGoodsPriceAdapter()
    {
        return new GoodsPriceAdapter($this);
    }


    /**
     * 缓存等级折金额
     * @param $price
     * @return float
     * @throws AppException
     */

    public function getVipDiscountAmount($price)
    {

        if (isset($this->vipDiscountAmount)) {

            return $this->vipDiscountAmount;
        }

        $this->vipDiscountAmount = $this->memberLevelDiscount()->getAmount($price);
        $this->vipDiscountLog = $this->memberLevelDiscount()->getLog($this->vipDiscountAmount);
        return $this->vipDiscountAmount;
    }

    /**
     * 获取商品的会员价格
     * @return float|mixed
     * @throws AppException
     */
    public function getVipPriceAttribute()
    {

        return sprintf('%.2f', $this->deal_price - $this->getVipDiscountAmount($this->getGoodsPriceAdapter()));
    }


    /**
     * 获取下一等级的会员价
     * @return int|string
     * @throws AppException
     */
    public function getNextLevelPriceAttribute()
    {
        if (\YunShop::app()->getMemberId()) {
            $member = \app\frontend\models\Member::current();
        } else {
            $member = new \app\frontend\models\Member();
        }

        $level_id = $member->yzMember->level_id;


        if (empty($level_id)) {
            $nextLevel = MemberLevel::getFirstLevel();
            $this->nextLevelName = $nextLevel->level_name;
        } else {
            $level = MemberLevel::getMemberLevel($level_id);

            $nextLevel = MemberLevel::getNextMemberLevel($level);
            $this->nextLevelName = $nextLevel->level_name;
        }

        $priceClass = $this->getGoodsPriceAdapter();

        /**
         * @param \app\common\models\MemberLevel $nextLevel
         * @param \app\common\models\GoodsDiscount $goods
         */
        if ($nextLevel) {
            $goods = $this->hasManyGoodsDiscount->where('level_id', $nextLevel->id)->first();
            if (!$goods) {

                /**
                 * @param \app\common\models\MemberLevel $nextLevel
                 */
                $price = $nextLevel->getDiscountCalculation($priceClass);

                return sprintf('%.2f', $this->deal_price - $price);

//                $nextLevel->discount = trim($nextLevel->discount);
//                $nextLevel->discount = $nextLevel->discount == false ? 10 : $nextLevel->discount;
//                $price = (1 - $nextLevel->discount / 10) * $this->deal_price;
//                return sprintf('%.2f', $this->deal_price - $price);

            }

            return sprintf('%.2f', $this->deal_price - $goods->getNextAmount($priceClass, $nextLevel));

        } else {
            $goods = $this->hasManyGoodsDiscount->where('level_id', $level->id)->first();
            if (!$goods) {

                if (is_null($level)) {
                    return $this->deal_price;
                }

                $price = $level->getDiscountCalculation($priceClass);
                return sprintf('%.2f', $this->deal_price - $price);

//                $level->discount = trim($level->discount);
//                $level->discount = $level->discount == false ? 10 : $level->discount;
//                $price = (1 - $level->discount / 10) * $this->deal_price;
//                return sprintf('%.2f', $this->deal_price - $price);
            }

            return sprintf('%.2f', $this->deal_price - $goods->getAmount($priceClass, $member));
        }
    }

    public function getVipNextPriceAttribute()
    {
        return 0;
    }

    public function getPriceLevelAttribute()
    {
        if (app('plugins')->isEnabled('member-price')) {
            $set = \Setting::get('plugin.member-price');
            return $set['price_level'];
        }

        return 1;
    }

    public function getIsOpenMicroAttribute()
    {
        if (app('plugins')->isEnabled('member-price')) {
            $set = \Setting::get('plugin.member-price');
            return $set['is_open_micro'];
        }

        return 2;
    }

    public function getNextLevelNameAttribute()
    {
        if (isset($this->nextLevelName)) {
            return $this->nextLevelName;
        }

        return '';
    }

    public function dispatchTypeSetting()
    {
        if ($this->hasOneGoodsDispatch) {
            return $this->hasOneGoodsDispatch->dispatchTypesSetting();
        }
        return [];
    }
    public function goodsDispatchTypeIds()
    {
        if ($this->hasOneGoodsDispatch) {
            return $this->hasOneGoodsDispatch->getEnableDispatchTypeIds();
            //return $this->hasOneGoodsDispatch->getDispatchTypeIds();
        }
        return [];
    }

    private $goodsStock;

    public function goodsStock()
    {
        if (!isset($this->goodsStock)) {
            $this->goodsStock = new GoodsStock($this);
        }
        return $this->goodsStock;
    }

    public function save($options = [])
    {
        if ($this->attributes['reduce_stock_method'] != $this->original['reduce_stock_method']) {
            if ($this->withhold_stock > 0) {
                throw new AppException('存在预扣库存时，无法修改减库存方式设置。');
            }
        }
        // 提交的库存是扣除预扣的,保存的时候必须将预扣的数量加回来
        if(isset($this->attributes['stock']) && $this->attributes['stock'] != $this->original['stock']){
            $this->attributes['stock'] = $this->attributes['stock'] + $this->withhold_stock;
        }

        $result = parent::save($options);

        return $result;
    }

    public function getStockAttribute()
    {
        return $this->goodsStock()->usableStock();

    }


    public function getWithholdStockAttribute()
    {
        return $this->goodsStock()->withholdStock();
    }

    /**
     * 库存是否充足
     * @param $num
     * @return bool
     * @author shenyang
     */
    public function stockEnough($num)
    {
        if($this->reduce_stock_method == 2){
            return true;
        }
        return $this->goodsStock()->enough($num);
    }


    /**
     * 增加销量
     * @param $num
     * @author shenyang
     */
    public function addSales($num)
    {
        $this->increment('real_sales', $num);
        $this->increment('show_sales', $num);
    }

    public function fireStockNotEnoughtEvent($goods)
    {
        event(new GoodsStockNotEnoughEvent($goods));
    }
}
