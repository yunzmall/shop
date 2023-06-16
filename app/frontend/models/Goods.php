<?php
/**
 * Created by PhpStorm.
 * User: shenyang
 * Date: 2017/3/31
 * Time: 下午5:55
 */

namespace app\frontend\models;



use app\framework\Database\Eloquent\Collection;
use app\frontend\models\goods\Privilege;
use app\frontend\models\goods\Sale;
use app\common\models\Coupon;
use Illuminate\Database\Eloquent\Builder;
use Yunshop\StoreCashier\common\models\StoreGoods;
use Yunshop\Supplier\admin\models\SupplierGoods;

/**
 * Class Goods
 * @package app\frontend\models
 * @property int id
 * @property string goods_sn
 * @property string title
 * @property string thumb
 * @property float price
 * @property float weight
 * @property int is_plugin
 * @property int plugin_id

 * @property Sale hasOneSale
 * @property GoodsOption has_option
 * @property Privilege hasOnePrivilege
 * @property SupplierGoods supplierGoods
 * @property StoreGoods storeGoods
 * @property Collection belongsToCategorys
 * @method static self search(array $search)
 */
class Goods extends \app\common\models\Goods
{
    public $hidden = ['content', 'description'];
    public $appends = ['vip_price','next_level_price'];




    //todo 为什么要获取单体商品规格？？？
    public function hasOneOptions()
    {
        return $this->hasOne(GoodsOption::class);
    }

    public function hasOneSale()
    {
        return $this->hasOne(Sale::class);
    }

    /**
     * @param Builder $query
     * @param $filters
     */
    public function scopeSearch(Builder $query, $filters)
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
                case 'plugin_id':
                    $query->where('plugin_id', $value);
                    break;
                case 'keyword':
                    $query->where('title', 'LIKE', "%{$value}%");
                    break;
                case 'brand_id':
                    $query->where('brand_id', $value);
                    break;
                case 'product_attr':
                    foreach ($value as $attr) {
                        $query->where($attr, 1);
                    }
                    break;
                case 'status':
                    $query->where('status', $value);
                    break;
                case 'min_price':
                    $query->where('price', '>', $value);
                    break;
                case 'max_price':
                    $query->where('price', '<', $value);
                    break;
                case 'category':
                    if (array_key_exists('parentid', $value) || array_key_exists('childid', $value) || array_key_exists('thirdid', $value)) {
                        $id = $value['parentid'] ? $value['parentid'] : '';
                        $id = $value['childid'] ? $value['childid'] : $id;
                        $id = $value['thirdid'] ? $value['thirdid'] : $id;

                        $query->select([
                            'yz_goods.*',
                            'yz_goods_category.id as goods_category_id',
                            'yz_goods_category.goods_id as goods_id',
                            'yz_goods_category.category_id as category_id',
                            'yz_goods_category.category_ids as category_ids'
                        ])->join('yz_goods_category', 'yz_goods_category.goods_id', '=', 'yz_goods.id')->whereRaw('FIND_IN_SET(?,category_ids)', [$id]);
                    } elseif (strpos($value, ',')) {
                        $scope = explode(',', $value);
                        $query->select([
                            'yz_goods.*',
                            'yz_goods_category.id as goods_category_id',
                            'yz_goods_category.goods_id as goods_id',
                            'yz_goods_category.category_id as category_id',
                            'yz_goods_category.category_ids as category_ids'
                        ])->join('yz_goods_category', function ($join) use ($scope) {
                            $join->on('yz_goods_category.goods_id', '=', 'yz_goods.id')
                                ->whereIn('yz_goods_category.category_id', $scope);
                        });
                    } else {
                        $query->select([
                            'yz_goods.*',
                            'yz_goods_category.id as goods_category_id',
                            'yz_goods_category.goods_id as goods_id',
                            'yz_goods_category.category_id as category_id',
                            'yz_goods_category.category_ids as category_ids'
                        ])->join('yz_goods_category', function ($join) use ($value) {
                            $join->on('yz_goods_category.goods_id', '=', 'yz_goods.id')
                                ->whereRaw('FIND_IN_SET(?,category_ids)', [$value]);
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
                        case Coupon::COUPON_CATEGORY_USE: //优惠券适用于指定商品分类
                            if (is_array($res['scope'])) {
                                $query->join('yz_goods_category', function ($join) use ($res) {
                                    $join->on('yz_goods_category.goods_id', '=', 'yz_goods.id')
                                        ->whereIn('yz_goods_category.category_id', $res['scope']);
                                });
                            } else {
                                $query->join('yz_goods_category', function ($join) use ($res) {
                                    $join->on('yz_goods_category.goods_id', '=', 'yz_goods.id')
                                        ->where('yz_goods_category.category_id', $res['scope']);
                                });
                            }
                            break;
                        default: //优惠券适用于整个商城
                            break;
                    }
                    break;
                default:
                    break;
            }
        }
    }

    public function getVipLevelStatusAttribute()
    {
        $vip_status = [
            'status'  => 0,
            'word' => '',
            'tips' => ''
        ];
        if (!app('plugins')->isEnabled('price-authority')) {
            return $vip_status;
        }
        //查询会员等级
        $level = MemberShopInfo::select('level_id')->where('member_id',\YunShop::app()->getMemberId())->first();

        $set = \Setting::get('plugin.price_authority');

        if (!empty($set['is_jurisdiction']) && $set['is_jurisdiction']){

            $goods = \Yunshop\PriceAuthority\model\Goods::find($this->id);

            if (($goods->plugin_id == 0 || $goods->plugin_id == 92)) {
                if ($set['supplier_vip_level'] == '' || empty($set['supplier_vip_level'])){
                    $vip_status = [
                        'status'  => 0,
                        'word' => '',
                        'tips' => ''
                    ];
                }else if (!in_array($level->level_id, $set['supplier_vip_level'])) {
                    $level_name = '';
                    foreach ($set['supplier_vip_level'] as $item) {
                        $level = MemberLevel::find($item);
                        if (!$level) {
                            $level->level_name = '普通会员';
                        }
                        $level_name .= '/' . $level->level_name;
                    }
                    $vip_status['status'] = 1;
                    $vip_status['word'] = $set['supplier_jurisdiction_word'] ?: '无权限';
                    $vip_status['tips'] = '该商品仅限' . $level_name . '等级购买';
                }
            }

            if (($goods->plugin_id == 31 || $goods->plugin_id == 32)){

                if ($set['store_vip_level'] == ''){
                    $vip_status = [
                        'status'  => 0,
                        'word' => '',
                        'tips' => ''
                    ];
                }else if (!in_array($level->level_id, $set['store_vip_level'])){
                    $level_name = '';
                    foreach ($set['store_vip_level'] as $item){
                        $level = MemberLevel::find($item);
                        if (!$level){
                            $level->level_name = '普通会员';
                        }
                        $level_name .= '/'.$level->level_name ;
                    }
                    $vip_status['status'] = 1;
                    $vip_status['word'] = $set['store_jurisdiction_word'] ?: '无权限';
                    $vip_status['tips'] = '该商品仅限'.$level_name.'等级购买';
                }
            }
        }

        return ($vip_status);
    }
}
