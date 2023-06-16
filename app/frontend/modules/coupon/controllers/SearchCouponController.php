<?php
/**
 * Created by PhpStorm.
 *
 * 
 *
 * Date: 2021/9/7
 * Time: 16:27
 */

namespace app\frontend\modules\coupon\controllers;


use app\common\components\ApiController;
use app\common\exceptions\AppException;
use app\common\facades\Setting;
use app\common\models\coupon\CouponSlideShow;
use app\common\models\GoodsCategory;
use app\common\modules\coupon\models\PreMemberCoupon;
use app\frontend\models\Goods;
use app\frontend\models\Member;
use app\frontend\modules\coupon\models\Coupon;
use app\frontend\modules\coupon\models\MemberCoupon;
use app\common\models\MemberShopInfo;
use Carbon\Carbon;
use Yunshop\Hotel\common\models\CouponHotel;
use Yunshop\StoreCashier\common\models\Store;

class SearchCouponController  extends ApiController
{

    //"优惠券中心"的优惠券
    const IS_AVAILABLE = 1; //可领取
    const ALREADY_GOT = 2; //已经领取
    const EXHAUST = 3; //已经被抢光

    //"个人拥有的优惠券"的状态
    const NOT_USED = 1; //未使用
    const OVERDUE = 2; //优惠券已经过期
    const IS_USED = 3; //已经使用

    const NO_LIMIT = -1; //没有限制 (比如对会员等级没有限制, 对领取总数没有限制)

    const TEMPLATEID = 'OPENTM200605630'; //成功发放优惠券时, 发送的模板消息的 ID

//    const TEMPLATEID = 'tqsXWjFgDGrlUmiOy0ci6VmVtjYxR7s-4BWtJX6jgeQ'; //临时调试用

    /**
     * coupon.search-coupon.index
     * 提供给用户的"优惠券中心"的数据接口
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {

        $search['goods_id'] = intval(request()->input('goods_id'));

        $uid = \YunShop::app()->getMemberId();

        $member = MemberShopInfo::getMemberShopInfo($uid);

        if (empty($member)) {
            return $this->errorJson('没有找到该用户', []);
        }
        $memberLevel = $member->level_id;

        $now = strtotime('now');
        $coupons = Coupon::centerCouponsForMember($uid, $memberLevel, null, $now,\YunShop::request()->coupon_type?:'')
            ->orderBy('yz_coupon.display_order', 'desc')
            ->orderBy('yz_coupon.updated_at', 'desc')
            ->get();
        if ($coupons->isEmpty()) {
            return $this->errorJson('没有找到记录', []);
        }
        foreach ($coupons as &$item) {
            $item->has_many_member_coupon_count = MemberCoupon::uniacid()->where('coupon_id' ,$item->id)->pluck('uid')->unique()->count();
        }
        //添加"是否可领取" & "是否已抢光" & "是否已领取"的标识
        $couponsData = $this->getCouponData($coupons, $search);

        $slideShows = CouponSlideShow::uniacid()
            ->where('is_show',1)
            ->orderBy('sort','asc')
            ->orderBy('id','asc')
            ->limit(10)
            ->get();

        $data = [
            'data' => $couponsData,
            'search_array' => $this->getSearchArray(Coupon::$typeComment),//Coupon::$typeComment
            'slide_shows' => $slideShows
        ];

        //领券中心表单
        if (!is_null(\app\common\modules\shop\ShopConfig::current()->get('coupon_form'))) {
            $class    = array_get(\app\common\modules\shop\ShopConfig::current()->get('coupon_form'), 'class');
            $function = array_get(\app\common\modules\shop\ShopConfig::current()->get('coupon_form'), 'function');
            $form = $class::$function($uid);
            if($form && $form != -1)
            {
                $data = array_merge($data,['coupon_form' => $form]);
            }
        }

        return $this->successJson('ok', $data);
    }


    protected function filterCoupon($coupon, $search)
    {

        if (empty($search['goods_id'])) {
            return false;
        }

        $goods = Goods::find($search['goods_id']);
        $goodsCategory = GoodsCategory::select('category_id')->where('goods_id', $search['goods_id'])->get()->pluck('category_id')->toArray();
        
        if ($coupon->time_limit == 1 && (time() < $coupon->time_start || time() > $coupon->time_end)) {
            return true;
        }

        switch ($coupon->use_type) {
            case Coupon::COUPON_SHOP_USE: //商城通用
                if (!in_array($goods->plugin_id,[31,32,33,36,92,101])) {
                    $coupon_list[] = $coupon;
                }
                break;
            case Coupon::COUPON_GOODS_USE: //指定商品
                if (in_array($goods->id, $coupon['goods_ids'])) {
                    $coupon_list[] = $coupon;
                }
                break;
            case Coupon::COUPON_GOODS_AND_STORE_USE:  //指定商品+指定门店
                $use_conditions = unserialize($coupon->use_conditions);
                if (($use_conditions['is_all_good'] && $goods->plugin_id == 0) || in_array($goods->id, $use_conditions['good_ids'])) {
                    $coupon_list[] = $coupon;
                }
                break;
            case Coupon::COUPON_CATEGORY_USE:  //指定分类
               
                //商品分类存在该优惠卷中
                if ($coupon['category_ids'] && array_intersect($goodsCategory, $coupon['category_ids'])) {
                    $coupon_list[] = $coupon;
                }
                break;
            default:
        }
        
        return true;
    }


    /**
     * 过滤不满足条件的优惠卷 &
     * 添加"是否可领取" & "是否已抢光" & "是否已领取"的标识
     * @param $coupons
     * @param $search
     * @return mixed
     */
    public function getCouponData($coupons, $search)
    {
        $coupons = $coupons->map(function ($item) use ($search) {

            
            if ($this->filterCoupon($item,$search)) {
                return null;
            }


            if (($item->total != self::NO_LIMIT) && ($item->has_many_member_coupon_count >= $item->total)) {
                $item->api_availability = self::EXHAUST;
            } elseif ($item->member_got_count > 0) {
                $item->api_availability = self::ALREADY_GOT;
            } else {
                $item->api_availability = self::IS_AVAILABLE;
            }

            //增加属性 - 对于该优惠券,用户可领取的数量
            if ($item->get_max != self::NO_LIMIT) {
                $item->api_remaining = $item->get_max - $item->member_got_count;
                if ($item->api_availability < 0) { //考虑到优惠券设置会变更,比如原来允许领取6张,之后修改为3张,那么可领取张数可能会变成负数
                    $item->api_availability = 0;
                }
            } elseif ($item->get_max == self::NO_LIMIT) {
                $item->api_availability = -1;
            }
            //添加优惠券使用范围描述
            switch ($item->use_type) {
                case Coupon::COUPON_SHOP_USE:
                    $item->api_limit = '商城通用';
                    break;
                case Coupon::COUPON_CATEGORY_USE:
                    $item->api_limit = '适用于下列分类: '.implode(',', $item['categorynames']);
                    break;
                case Coupon::COUPON_GOODS_USE:
                    $item->api_limit = '适用于下列商品: '.implode(',', $item['goods_names']);
                    break;
                case 8:
                    $item->api_limit = '适用于下列商品: '.implode(',', $item['goods_names']);
                    break;
                case 9:
                    $item->api_limit = '适用范围: ';
                    $use_condition = unserialize($item['use_conditions']);
                    if (empty($use_condition)) {
                        $item->api_limit .= '无适用范围';
                    }
                    if (app('plugins')->isEnabled('store-cashier')) {
                        if ($use_condition['is_all_store'] == 1) {
                            $item->api_limit .= "全部门店";
                        } else {
                            $item->api_limit .= '门店:'.implode(',', Store::uniacid()->whereIn('id', $use_condition['store_ids'])->pluck('store_name')->all());
                        }
                    }
                    if ($use_condition['is_all_good'] == 1) {
                        $item->api_limit .= "平台自营商品";
                    } else {
                        $item->api_limit .= '商品:'.implode(',', Goods::uniacid()->whereIn('id', $use_condition['good_ids'])->pluck('title')->all());
                    }
                    break;
            }

            return $item;
        })->filter()->values();
        
        return $coupons;
    }

    private function getSearchArray($arr)
    {
        if (!app('plugins')->isEnabled('store-cashier')) {
            unset($arr[Coupon::TYPE_STORE]);
        }
        if (!app('plugins')->isEnabled('hotel')) {
            unset($arr[Coupon::TYPE_HOTEL]);
        }
        return $arr;
    }
}