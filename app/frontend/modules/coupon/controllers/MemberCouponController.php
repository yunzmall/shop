<?php

namespace app\frontend\modules\coupon\controllers;

use app\common\components\ApiController;
use app\common\exceptions\AppException;
use app\common\facades\Setting;
use app\common\models\coupon\CouponSlideShow;
use app\common\modules\coupon\models\PreMemberCoupon;
use app\framework\Http\Request;
use app\frontend\models\Goods;
use app\frontend\models\Member;
use app\frontend\modules\coupon\models\Coupon;
use app\frontend\modules\coupon\models\MemberCoupon;
use app\common\models\MemberShopInfo;
use app\frontend\modules\coupon\services\CouponConditionService;
use Carbon\Carbon;
use Yunshop\Hotel\common\models\CouponHotel;
use Yunshop\Integral\Common\Services\SetService;
use Yunshop\StoreCashier\common\models\Store;


class MemberCouponController extends ApiController
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


    public function couponsOfMemberByStatusV2()
    {
        $status = \YunShop::request()->get('status_request');
        $uid = \YunShop::app()->getMemberId();
        $search_array = [];
        $now = strtotime('now');
        $coupons = [];
        switch ($status) {
            case self::NOT_USED:
                $coupons = self::getAvailableCouponsV3($uid, $now);
                $search_array = array_merge(Coupon::$typeComment, [Coupon::TYPE_OVERDUE => '快过期']);
                break;
            case self::OVERDUE:
                $coupons = self::getOverdueCouponsV3($uid, $now);
                $search_array = Coupon::$typeComment;
                break;
            case self::IS_USED:
                $coupons = self::getUsedCouponsV3($uid);
                $search_array = Coupon::$typeComment;
                break;
        }

        $data = [
            'set' => [
                'transfer' => Setting::get('coupon.transfer') ? true : false,
                'center_show' => !is_numeric(Setting::get('coupon.center_show')) || Setting::get('coupon.center_show') == 1,
            ],
            'data' => $coupons,
            'search_array' => $this->getSearchArray($search_array)
        ];
        if (app('plugins')->isEnabled('share-coupons')) {
            $set = \Setting::get('share-coupons.is_open');
            if ($set == 1) {
                $data = array_merge($data, ['share_coupon' => true]);
            }
        }
        if (app('plugins')->isEnabled('write-off-coupons')) {
            $set = \Setting::get('write-off-coupons.is_open');
            if ($set == 1) {
                $data = array_merge($data, ['write_off_coupon' => true]);
            }
        }
        $coupon_set = \Setting::getByGroup('coupon');
        if ($coupon_set['coupon_show'] == 1) {
            $data = array_merge($data, ['combine_show' => true]);
        };
        return $this->successJson('ok', $data);
    }

    /**
     * 获取用户所拥有的优惠券的数据接口
     * @return \Illuminate\Http\JsonResponse
     */
    public function couponsOfMember()
    {
        $uid = \YunShop::app()->getMemberId();
        $pageSize = \YunShop::request()->get('pagesize');
        $pageSize = $pageSize ? $pageSize : 10;

        $coupons = MemberCoupon::getCouponsOfMember($uid)->paginate($pageSize)->toArray();
        if (empty($coupons['data'])) {
            return $this->errorJson('没有找到记录', []);
        }

        //添加 "是否可用" & "是否已经使用" & "是否过期" 的标识
//        $now = strtotime('now');
        foreach ($coupons['data'] as $k => &$v) {
            $v = $this->getTimeLimit($v);
//            if ($v['used'] == MemberCoupon::USED) { //已使用
//                $coupons['data'][$k]['api_status'] = self::IS_USED;
//            } elseif ($v['used'] == MemberCoupon::NOT_USED) { //未使用
//                if ($v['belongs_to_coupon']['time_limit'] == Coupon::COUPON_SINCE_RECEIVE) { //时间限制类型是"领取后几天有效"
//                    $end = strtotime($v['get_time']) + $v['belongs_to_coupon']['time_days'] * 3600;
//                    if ($now < $end) { //优惠券在有效期内
//                        $coupons['data'][$k]['api_status'] = self::NOT_USED;
//                        $coupons['data'][$k]['start'] = substr($v['get_time'], 0, 10); //前端需要起止时间
//                        $coupons['data'][$k]['end'] = date('Y-m-d', $end); //前端需要起止时间
//                    } else { //优惠券在有效期外
//                        $coupons['data'][$k]['api_status'] = self::OVERDUE;
//                    }
//                } elseif ($v['belongs_to_coupon']['time_limit'] == Coupon::COUPON_DATE_TIME_RANGE) { //时间限制类型是"时间范围"
//                    if (($now > $v['belongs_to_coupon']['time_end'])) { //优惠券在有效期外
//                        $coupons['data'][$k]['api_status'] = self::OVERDUE;
//                        $coupons['data'][$k]['start'] = $coupons['data'][$k]['time_start']; //为了和前面保持一致
//                        $coupons['data'][$k]['end'] = $coupons['data'][$k]['time_end']; //为了和前面保持一致
//                    } else { //优惠券在有效期内
//                        $coupons['data'][$k]['api_status'] = self::NOT_USED;
//                    }
//                }
//            } else {
//                $coupons['data'][$k]['api_availability'] = self::IS_AVAILABLE;
//            }
        }
        return $this->successJson('ok', $coupons);
    }


    /**
     * @param $v
     * @return mixed
     * 获取优惠券有效时间
     */
    protected function getTimeLimit($v)
    {
        $now = strtotime('now');
        if ($v['used'] == MemberCoupon::USED) { //已使用
            $v['api_status'] = self::IS_USED;
        } elseif ($v['used'] == MemberCoupon::NOT_USED) { //未使用
            if ($v['belongs_to_coupon']['time_limit'] == Coupon::COUPON_SINCE_RECEIVE) { //时间限制类型是"领取后几天有效"
                $end = strtotime($v['get_time']) + $v['belongs_to_coupon']['time_days'] * 3600;
                if ($now < $end) { //优惠券在有效期内
                    $v['api_status'] = self::NOT_USED;
                    $v['start'] = substr($v['get_time'], 0, 10); //前端需要起止时间
                    $v['end'] = date('Y-m-d', $end); //前端需要起止时间
                } else { //优惠券在有效期外
                    $v['api_status'] = self::OVERDUE;
                }
            } elseif ($v['belongs_to_coupon']['time_limit'] == Coupon::COUPON_DATE_TIME_RANGE) { //时间限制类型是"时间范围"
                if (($now > $v['belongs_to_coupon']['time_end'])) { //优惠券在有效期外
                    $v['api_status'] = self::OVERDUE;
                    $v['start'] = $v['time_start']; //为了和前面保持一致
                    $v['end'] = $v['time_end']; //为了和前面保持一致
                } else { //优惠券在有效期内
                    $v['api_status'] = self::NOT_USED;
                }
            }
        } else {
            $v['api_availability'] = self::IS_AVAILABLE;
        }
        return $v;
    }

    /**
     * 提供给用户的"优惠券中心"的数据接口
     * @return \Illuminate\Http\JsonResponse
     */
    public function couponsForMember()
    {
        $pageSize = \YunShop::request()->get('pagesize');
        $pageSize = $pageSize ? $pageSize : 10;
        $uid = \YunShop::app()->getMemberId();
        $member = MemberShopInfo::getMemberShopInfo($uid);
        if (empty($member)) {
            return $this->errorJson('没有找到该用户', []);
        }
        $memberLevel = $member->level_id;

        $now = strtotime('now');
        $coupons = Coupon::centerCouponsForMember($uid, $memberLevel, null, $now, \YunShop::request()->coupon_type ?: '')
            ->orderBy('yz_coupon.display_order', 'desc')
            ->orderBy('yz_coupon.updated_at', 'desc');
        $coupons = $coupons->paginate($pageSize)->toArray();

        foreach ($coupons['data'] as &$item) {
            $item['has_many_member_coupon_count'] = MemberCoupon::uniacid()->select('uid')->where('coupon_id', $item['id'])->distinct()->get()->count();
        }
        //添加"是否可领取" & "是否已抢光" & "是否已领取"的标识
        $couponsData = self::getCouponData($coupons, $memberLevel);

        $slideShows = CouponSlideShow::uniacid()
            ->where('is_show', 1)
            ->orderBy('sort', 'asc')
            ->orderBy('id', 'asc')
            ->limit(10)
            ->get();

        if (app('plugins')->isEnabled('integral')) {
            $integral_plugin_name = SetService::getIntegralName();
        }

        $data = [
            'data' => $couponsData,
            'search_array' => $this->getSearchArray(Coupon::$typeComment),//Coupon::$typeComment
            'slide_shows' => $slideShows,
            'integral_plugin_name' => $integral_plugin_name ?? '消费积分',//消费积分自定义名称
        ];

        //领券中心表单
        if (!is_null(\app\common\modules\shop\ShopConfig::current()->get('coupon_form'))) {
            $class = array_get(\app\common\modules\shop\ShopConfig::current()->get('coupon_form'), 'class');
            $function = array_get(\app\common\modules\shop\ShopConfig::current()->get('coupon_form'), 'function');
            $form = $class::$function($uid);
            if ($form && $form != -1) {
                $data = array_merge($data, ['coupon_form' => $form]);
            }
        }

        return $this->successJson('ok', $data);
    }

    /**
     * 提供给店铺装修的"优惠券中心"的数据接口
     * @return \Illuminate\Http\JsonResponse
     */
    public function couponsForDesigner(Request $request, $integrated = null)
    {
        $uid = \YunShop::app()->getMemberId();
        $member = MemberShopInfo::getMemberShopInfo($uid);
        if (empty($member)) {
            if (is_null($integrated)) {
                return $this->errorJson('没有找到该用户', []);
            } else {
                return show_json(0, '没有找到该用户');
            }
        }
        $memberLevel = $member->level_id;

        $now = strtotime('now');
        $coupons = Coupon::getCouponsForMember($uid, $memberLevel, null, $now)
            ->orderBy('display_order', 'desc')
            ->orderBy('updated_at', 'desc');
        if ($coupons->get()->isEmpty()) {
            if (is_null($integrated)) {
                return $this->errorJson('没有找到记录', []);
            } else {
                return show_json(0, '没有找到记录');
            }
        }
        $coupons_data['data'] = $coupons->get()->toArray();

        //添加"是否可领取" & "是否已抢光" & "是否已领取"的标识
        foreach ($coupons_data['data'] as $k => $v) {
            $coupons_data['data'][$k]['coupon_id'] = $coupons_data['data'][$k]['id'];
            if (($v['total'] != self::NO_LIMIT) && ($v['has_many_member_coupon_count'] >= $v['total'])) {
                $coupons_data['data'][$k]['api_availability'] = self::EXHAUST;
            } elseif ($v['get_max'] > 0 && $v['member_got_count'] >= $v['get_max']) {
                $coupons_data['data'][$k]['api_availability'] = self::ALREADY_GOT;
            } else {
                $coupons_data['data'][$k]['api_availability'] = self::IS_AVAILABLE;
            }

            //增加属性 - 对于该优惠券,用户可领取的数量
            if ($v['get_max'] != self::NO_LIMIT) {
                $coupons_data['data'][$k]['api_remaining'] = $v['get_max'] - $v['member_got_count'];
                if ($coupons_data['data'][$k]['api_remaining'] < 0) { //考虑到优惠券设置会变更,比如原来允许领取6张,之后修改为3张,那么可领取张数可能会变成负数
                    $coupons_data['data'][$k]['api_remaining'] = 0;
                }
            } elseif ($v['get_max'] == self::NO_LIMIT) {
                $coupons_data['data'][$k]['api_remaining'] = -1;
            }

            //添加优惠券使用范围描述
            switch ($v['use_type']) {
                case Coupon::COUPON_SHOP_USE:
                    $coupons_data['data'][$k]['api_limit'] = '商城通用';
                    break;
                case Coupon::COUPON_CATEGORY_USE:
                    $coupons_data['data'][$k]['api_limit'] = '适用于下列分类: ';
                    $coupons_data['data'][$k]['api_limit'] = implode(',', $v['categorynames']);
                    break;
                case Coupon::COUPON_GOODS_USE:
                    $coupons_data['data'][$k]['api_limit'] = '适用于下列商品: ';
                    $coupons_data['data'][$k]['api_limit'] = implode(',', $v['goods_names']);
                    break;
                case 8:
                    $coupons_data['data'][$k]['api_limit'] = '适用于下列商品: ';
                    $coupons_data['data'][$k]['api_limit'] = implode(',', $v['goods_names']);
                    break;
                case 9:
                    $coupons_data['data'][$k]['api_limit'] = '适用范围: ';
                    $use_condition = unserialize($v['use_conditions']);
                    if (empty($use_condition)) {
                        $coupons_data['data'][$k]['api_limit'] .= '无适用范围';
                    }
                    if (app('plugins')->isEnabled('store-cashier')) {
                        if ($use_condition['is_all_store'] == 1) {
                            $coupons_data['data'][$k]['api_limit'] .= "全部门店";
                        } else {
                            $coupons_data['data'][$k]['api_limit'] .= '门店:' . implode(',', Store::uniacid()->whereIn('id', $use_condition['store_ids'])->pluck('store_name')->all());
                        }
                    }
                    if ($use_condition['is_all_good'] == 1) {
                        $coupons_data['data'][$k]['api_limit'] .= "平台自营商品";
                    } else {
                        $coupons_data['data'][$k]['api_limit'] .= '商品:' . implode(',', Goods::uniacid()->whereIn('id', $use_condition['good_ids'])->pluck('title')->all());
                    }
                    break;
            }
        }
        if (is_null($integrated)) {
            return $this->successJson('ok', $coupons_data);
        } else {
            return show_json(1, $coupons_data);
        }
    }

    //添加"是否可领取" & "是否已抢光" & "是否已领取"的标识
    public static function getCouponData($coupons, $memberLevel)
    {
        foreach ($coupons['data'] as $k => $v) {
            if (($v['total'] != self::NO_LIMIT) && ($v['has_many_member_coupon_count'] >= $v['total'])) {
                $coupons['data'][$k]['api_availability'] = self::EXHAUST;
            } elseif ($v['get_max'] > 0 && $v['member_got_count'] >= $v['get_max']) {
                $coupons['data'][$k]['api_availability'] = self::ALREADY_GOT;
            } else {
                $coupons['data'][$k]['api_availability'] = self::IS_AVAILABLE;
            }

            //增加属性 - 对于该优惠券,用户可领取的数量
            if ($v['get_max'] != self::NO_LIMIT) {
                $coupons['data'][$k]['api_remaining'] = $v['get_max'] - $v['member_got_count'];
                if ($coupons['data'][$k]['api_remaining'] < 0) { //考虑到优惠券设置会变更,比如原来允许领取6张,之后修改为3张,那么可领取张数可能会变成负数
                    $coupons['data'][$k]['api_remaining'] = 0;
                }
            } elseif ($v['get_max'] == self::NO_LIMIT) {
                $coupons['data'][$k]['api_remaining'] = -1;
            }

            //添加优惠券使用范围描述
            switch ($v['use_type']) {
                case Coupon::COUPON_SHOP_USE:
                    $coupons['data'][$k]['api_limit'] = '商城通用';
                    break;
                case Coupon::COUPON_CATEGORY_USE:
                    $coupons['data'][$k]['api_limit'] = '适用于下列分类: ';
                    $coupons['data'][$k]['api_limit'] = implode(',', $v['categorynames']);
                    break;
                case Coupon::COUPON_GOODS_USE:
                    $coupons['data'][$k]['api_limit'] = '适用于下列商品: ';
                    $coupons['data'][$k]['api_limit'] = implode(',', $v['goods_names']);
                    break;
                case 8:
                    $coupons['data'][$k]['api_limit'] = '适用于下列商品: ';
                    $coupons['data'][$k]['api_limit'] = implode(',', $v['goods_names']);
                    break;
                case 9:
                    $coupons_data['data'][$k]['api_limit'] = '适用范围: ';
                    $use_condition = unserialize($v['use_conditions']);
                    if (empty($use_condition)) {
                        $coupons_data['data'][$k]['api_limit'] .= '无适用范围';
                    }
                    if (app('plugins')->isEnabled('store-cashier')) {
                        if ($use_condition['is_all_store'] == 1) {
                            $coupons_data['data'][$k]['api_limit'] .= "全部门店";
                        } else {
                            $coupons_data['data'][$k]['api_limit'] .= '门店:' . implode(',', Store::uniacid()->whereIn('id', $use_condition['store_ids'])->pluck('store_name')->all());
                        }
                    }
                    if ($use_condition['is_all_good'] == 1) {
                        $coupons_data['data'][$k]['api_limit'] .= "平台自营商品";
                    } else {
                        $coupons_data['data'][$k]['api_limit'] .= '商品:' . implode(',', Goods::uniacid()->whereIn('id', $use_condition['good_ids'])->pluck('title')->all());
                    }
                    break;
            }
        }
        return $coupons;
    }

    //获取用户所拥有的不同状态的优惠券 - 待使用(NOT_USED) & 已过期(OVERDUE) & 已使用(IS_USED)
    public function couponsOfMemberByStatus()
    {
        $status = \YunShop::request()->get('status_request');
        $uid = \YunShop::app()->getMemberId();

        $now = strtotime('now');
        switch ($status) {
            case self::NOT_USED:
                $coupons = self::getAvailableCoupons($uid, $now);
                break;
            case self::OVERDUE:
                $coupons = self::getOverdueCoupons($uid, $now);
                break;
            case self::IS_USED:
                $coupons = self::getUsedCoupons($uid);
                break;
        }

        if (empty($coupons)) {
            return $this->errorJson('没有找到记录', []);
        } else {
            return $this->successJson('ok', $coupons);
        }
    }

    //用户所拥有的可使用的优惠券
    public static function getAvailableCouponsV3($uid, $time)
    {
        $coupons = MemberCoupon::getCouponsOfMember($uid, \YunShop::request()->coupon_type ?: '')
            ->where('used', '=', 0)
            ->where('is_member_deleted', 0)
            ->where('is_expired', 0)
            ->paginate()->toArray();
        $availableCoupons = array();
        foreach ($coupons['data'] as $k => &$coupon) {
            if (app('plugins')->isEnabled('hotel')) {
                if ($coupon['belongs_to_coupon']['use_type'] == Coupon::COUPON_ONE_HOTEL_USE) {
                    $find = CouponHotel::where('coupon_id', $coupon['belongs_to_coupon']['id'])->first();
                    $coupon['belongs_to_coupon']['hotel_ids'] = $find->hotel_id;
                } elseif ($coupon['belongs_to_coupon']['use_type'] == Coupon::COUPON_MORE_HOTEL_USE) {
                    $finds = CouponHotel::where('coupon_id', $coupon['belongs_to_coupon']['id'])->get();
                    $findsArr = [];
                    foreach ($finds as $find_v) {
                        $findsArr[] = $find_v->hotel_id;
                    }
                    $coupon['belongs_to_coupon']['hotel_ids'] = $findsArr;
                }
            }
            if ($coupon['time_end'] != '不限时间') {
                $coupon['time_end'] = $coupon['timestamp_end'];
            }
            $usageLimit = array('api_limit' => self::usageLimitDescription($coupon['belongs_to_coupon'])); //增加属性 - 优惠券的适用范围
            $availableCoupons[] = array_merge($coupon, $usageLimit);
        }
        $coupon_set = \Setting::getByGroup('coupon');
        if ($coupon_set['coupon_show'] == 1) {
            $coupons['data'] = self::handleAvailableCoupons($availableCoupons);
            return $coupons;//多张折叠
        }
        $coupons['data'] = $availableCoupons;
        return $coupons;
    }


    //用户所拥有的可使用的优惠券
    public static function getAvailableCoupons($uid, $time)
    {
        $coupons = MemberCoupon::getCouponsOfMember($uid, \YunShop::request()->coupon_type ?: '')
            ->where('used', '=', 0)
            ->where('is_member_deleted', 0)
            ->where('is_expired', 0)
            ->get()->toArray();
        $availableCoupons = array();
        foreach ($coupons as $k => $v) {
            if (app('plugins')->isEnabled('hotel')) {
                if ($v['belongs_to_coupon']['use_type'] == Coupon::COUPON_ONE_HOTEL_USE) {
                    $find = CouponHotel::where('coupon_id', $v['belongs_to_coupon']['id'])->first();
                    $coupons[$k]['belongs_to_coupon']['hotel_ids'] = $find->hotel_id;
                } elseif ($v['belongs_to_coupon']['use_type'] == Coupon::COUPON_MORE_HOTEL_USE) {
                    $finds = CouponHotel::where('coupon_id', $v['belongs_to_coupon']['id'])->get();
                    $findsArr = [];
                    foreach ($finds as $find_v) {
                        $findsArr[] = $find_v->hotel_id;
                    }
                    $coupons[$k]['belongs_to_coupon']['hotel_ids'] = $findsArr;
                }
            }
            if ($v['time_end'] != '不限时间') {
                $coupons[$k]['time_end'] = $v['timestamp_end'];
            }
            $usageLimit = array('api_limit' => self::usageLimitDescription($v['belongs_to_coupon'])); //增加属性 - 优惠券的适用范围
            $availableCoupons[] = array_merge($coupons[$k], $usageLimit);
        }
        $coupon_set = \Setting::getByGroup('coupon');
        if ($coupon_set['coupon_show'] == 1) {
            return self::handleAvailableCoupons($availableCoupons);//多张折叠
        }
        return $availableCoupons;
    }

    public static function handleAvailableCoupons($coupons)
    {
        $newArr = [];
        $arr = [];
        foreach ($coupons as $v) {
            if ($v['belongs_to_coupon']['time_limit'] == Coupon::COUPON_DATE_TIME_RANGE) {
                if ($newArr[$v['belongs_to_coupon']['id']]) {
                    $newArr[$v['belongs_to_coupon']['id']] += 1;
                } else {
                    $newArr[$v['belongs_to_coupon']['id']] = 1;
                }
            }
        }
        $combineArr = [];
        $markArr = [];
        foreach ($coupons as $v) {
            if ($v['belongs_to_coupon']['time_limit'] == Coupon::COUPON_DATE_TIME_RANGE) {
                if (!in_array($v['belongs_to_coupon']['id'], $markArr)) {
                    $combineArr[] = array_merge($v, ['combine' => true, 'num' => $newArr[$v['belongs_to_coupon']['id']]]);
                }
                $markArr[] = $v['belongs_to_coupon']['id'];
            } else {
                $combineArr[] = array_merge($v, ['combine' => false, 'num' => 1]);
            }
        }
        return $combineArr;
    }

    //用户所拥有的已过期的优惠券
    public static function getOverdueCouponsV3($uid, $time)
    {
        $coupons = MemberCoupon::getCouponsOfMember($uid, \YunShop::request()->coupon_type ?: '')->where('used', '=', 0)->where('is_member_deleted', 0)->where('is_expired', 1)->paginate()->toArray();

        $overdueCoupons = array();
        //获取已经过期的优惠券
        foreach ($coupons['data'] as $k => &$coupon) {
//            $coupons[$k]['belongs_to_coupon']['deduct'] = intval($coupons[$k]['belongs_to_coupon']['deduct']);
//            $coupons[$k]['belongs_to_coupon']['discount'] = intval($coupons[$k]['belongs_to_coupon']['discount']);

            if (app('plugins')->isEnabled('hotel')) {
                if ($coupon['belongs_to_coupon']['use_type'] == Coupon::COUPON_ONE_HOTEL_USE) {
                    $find = CouponHotel::where('coupon_id', $coupon['belongs_to_coupon']['id'])->first();
                    $coupon['belongs_to_coupon']['hotel_ids'] = $find->hotel_id;
                } elseif ($coupon['belongs_to_coupon']['use_type'] == Coupon::COUPON_MORE_HOTEL_USE) {
                    $finds = CouponHotel::where('coupon_id', $coupon['belongs_to_coupon']['id'])->get();
                    $findsArr = [];
                    foreach ($finds as $find_v) {
                        $findsArr[] = $find_v->hotel_id;
                    }
                    $coupon['belongs_to_coupon']['hotel_ids'] = $findsArr;
                }
            }

            $usageLimit = array('api_limit' => self::usageLimitDescription($coupon['belongs_to_coupon']));
            $overdueCoupons[] = array_merge($coupon, $usageLimit);
        }
        $coupons['data'] = $overdueCoupons;
        return $coupons;
    }


    //用户所拥有的已过期的优惠券
    public static function getOverdueCoupons($uid, $time)
    {
        $coupons = MemberCoupon::getCouponsOfMember($uid, \YunShop::request()->coupon_type ?: '')->where('used', '=', 0)->where('is_member_deleted', 0)->where('is_expired', 1)->get()->toArray();

        $overdueCoupons = array();
        //获取已经过期的优惠券
        foreach ($coupons as $k => $v) {
//            $coupons[$k]['belongs_to_coupon']['deduct'] = intval($coupons[$k]['belongs_to_coupon']['deduct']);
//            $coupons[$k]['belongs_to_coupon']['discount'] = intval($coupons[$k]['belongs_to_coupon']['discount']);

            if (app('plugins')->isEnabled('hotel')) {
                if ($v['belongs_to_coupon']['use_type'] == Coupon::COUPON_ONE_HOTEL_USE) {
                    $find = CouponHotel::where('coupon_id', $v['belongs_to_coupon']['id'])->first();
                    $coupons[$k]['belongs_to_coupon']['hotel_ids'] = $find->hotel_id;
                } elseif ($v['belongs_to_coupon']['use_type'] == Coupon::COUPON_MORE_HOTEL_USE) {
                    $finds = CouponHotel::where('coupon_id', $v['belongs_to_coupon']['id'])->get();
                    $findsArr = [];
                    foreach ($finds as $find_v) {
                        $findsArr[] = $find_v->hotel_id;
                    }
                    $coupons[$k]['belongs_to_coupon']['hotel_ids'] = $findsArr;
                }
            }

            $usageLimit = array('api_limit' => self::usageLimitDescription($v['belongs_to_coupon']));
            $overdueCoupons[] = array_merge($coupons[$k], $usageLimit);
        }
        return $overdueCoupons;
    }


    //用户所拥有的已使用的优惠券
    public static function getUsedCouponsV3($uid)
    {
        $coupons = MemberCoupon::getCouponsOfMember($uid, \YunShop::request()->coupon_type ?: '')->where('used', '=', 1)->where('is_member_deleted', 0)->paginate()->toArray();
        $usedCoupons = array();
        //增加属性 - 优惠券的适用范围
        foreach ($coupons['data'] as $k => &$coupon) {
//            $coupons[$k]['belongs_to_coupon']['deduct'] = intval($coupons[$k]['belongs_to_coupon']['deduct']);
//            $coupons[$k]['belongs_to_coupon']['discount'] = intval($coupons[$k]['belongs_to_coupon']['discount']);
            if (app('plugins')->isEnabled('hotel')) {
                if ($coupon['belongs_to_coupon']['use_type'] == Coupon::COUPON_ONE_HOTEL_USE) {
                    $find = CouponHotel::where('coupon_id', $coupon['belongs_to_coupon']['id'])->first();
                    $coupon['belongs_to_coupon']['hotel_ids'] = $find->hotel_id;
                } elseif ($coupon['belongs_to_coupon']['use_type'] == Coupon::COUPON_MORE_HOTEL_USE) {
                    $finds = CouponHotel::where('coupon_id', $coupon['belongs_to_coupon']['id'])->get();
                    $findsArr = [];
                    foreach ($finds as $find_v) {
                        $findsArr[] = $find_v->hotel_id;
                    }
                    $coupon['belongs_to_coupon']['hotel_ids'] = $findsArr;
                }
            }
            $usageLimit = array('api_limit' => self::usageLimitDescription($coupon['belongs_to_coupon']));
            $usedCoupons[] = array_merge($coupon, $usageLimit);
        }
        $coupons['data'] = $usedCoupons;
        return $coupons;
    }


    //用户所拥有的已使用的优惠券
    public static function getUsedCoupons($uid)
    {
        $coupons = MemberCoupon::getCouponsOfMember($uid, \YunShop::request()->coupon_type ?: '')->where('used', '=', 1)->where('is_member_deleted', 0)->get()->toArray();
        $usedCoupons = array();
        //增加属性 - 优惠券的适用范围
        foreach ($coupons as $k => $v) {
//            $coupons[$k]['belongs_to_coupon']['deduct'] = intval($coupons[$k]['belongs_to_coupon']['deduct']);
//            $coupons[$k]['belongs_to_coupon']['discount'] = intval($coupons[$k]['belongs_to_coupon']['discount']);
            if (app('plugins')->isEnabled('hotel')) {
                if ($v['belongs_to_coupon']['use_type'] == Coupon::COUPON_ONE_HOTEL_USE) {
                    $find = CouponHotel::where('coupon_id', $v['belongs_to_coupon']['id'])->first();
                    $coupons[$k]['belongs_to_coupon']['hotel_ids'] = $find->hotel_id;
                } elseif ($v['belongs_to_coupon']['use_type'] == Coupon::COUPON_MORE_HOTEL_USE) {
                    $finds = CouponHotel::where('coupon_id', $v['belongs_to_coupon']['id'])->get();
                    $findsArr = [];
                    foreach ($finds as $find_v) {
                        $findsArr[] = $find_v->hotel_id;
                    }
                    $coupons[$k]['belongs_to_coupon']['hotel_ids'] = $findsArr;
                }
            }
            $usageLimit = array('api_limit' => self::usageLimitDescription($v['belongs_to_coupon']));
            $usedCoupons[] = array_merge($coupons[$k], $usageLimit);
        }
        return $usedCoupons;
    }


    /**
     * @param $couponInArrayFormat array
     * @return string 优惠券适用范围的描述
     */
    public static function usageLimitDescription($couponInArrayFormat)
    {
        switch ($couponInArrayFormat['use_type']) {
            case 0:
                return ('商城通用');
                break;
            case 1:
                $res = '适用于下列分类: ';
                $res .= implode(',', $couponInArrayFormat['categorynames']);
                return $res;
                break;
            case 2:
                $res = '适用于下列商品: ';
                $res .= implode(',', $couponInArrayFormat['goods_names']);
                return $res;
                break;
            case 3:
                $res = '适用于下列供应商: ';
                $res .= implode(',', $couponInArrayFormat['suppliernames']);
                return $res;
                break;
            case 4:
            case 5:
                $res = '适用于下列门店: ';
                $res .= implode(',', $couponInArrayFormat['storenames']);
                return $res;
                break;
            case Coupon::COUPON_ONE_HOTEL_USE:
                $res = '适用于酒店 :';
                if (app('plugins')->isEnabled('hotel')) {
                    $coupon_hotel = CouponHotel::where('coupon_id', $couponInArrayFormat['id'])->with(['hotel' => function ($query) {
                        $query->select('hotel_name');
                    }])->first();
                    $res .= $coupon_hotel->hotel->hotel_name;
                }
                return $res;
                break;
            case Coupon::COUPON_MORE_HOTEL_USE:
                $res = '适用于下列酒店: ';
                if (app('plugins')->isEnabled('hotel')) {
                    $hotel_arr = [];
                    $coupon_hotels = CouponHotel::where('coupon_id', $couponInArrayFormat['id'])->with(['hotel' => function ($query) {
                        $query->select('hotel_name');
                    }])->get();
                    foreach ($coupon_hotels as $v) {
                        $hotel_arr[] = $v->hotel->hotel_name;
                    }
                    $res .= implode(',', $hotel_arr);
                }
                return $res;
                break;
            case 8:
                $res = '适用于下列商品: ';
                $res .= implode(',', $couponInArrayFormat['goods_names']);
                return $res;
                break;
            case Coupon::COUPON_GOODS_AND_STORE_USE:
                $res = '';
                $use_condition = unserialize($couponInArrayFormat['use_conditions']);
                if (empty($use_condition)) {
                    return '无适用范围';
                }
                if (app('plugins')->isEnabled('store-cashier')) {
                    if ($use_condition['is_all_store'] == 1) {
                        $res .= "全部门店、";
                    } else {
                        $res .= '门店:' . implode(',', Store::uniacid()->whereIn('id', $use_condition['store_ids'])->pluck('store_name')->all()) . '、';
                    }
                }
                if ($use_condition['is_all_good'] == 1) {
                    $res .= "平台自营商品";
                } else {
                    $res .= '商品:' . implode(',', Goods::uniacid()->whereIn('id', $use_condition['good_ids'])->pluck('title')->all());
                }
                return $res;
                break;
            default:
                return ('Enjoy shopping');
        }
    }

    //用户删除其拥有的优惠券
    public function delete()
    {
        $id = \YunShop::request()->id;
        if (empty($id)) {
            return $this->errorJson('缺少 ID 参数', '');
        }

        $model = MemberCoupon::find($id);
        if (!$model) {
            return $this->errorJson('找不到记录', '');
        }

        $res = $model->update(['is_member_deleted' => 1]);
        if ($res) {
            return $this->successJson('ok', '');
        } else {
            return $this->errorJson('删除优惠券失败', '');
        }
    }

    /**
     * 在"优惠券中心"点击领取优惠券
     * @return \Illuminate\Http\JsonResponse
     * @throws AppException
     * @throws \app\common\exceptions\MemberNotLoginException
     */
    public function getCoupon()
    {
        $this->validate([
            'coupon_id' => 'required|integer'

        ]);
        $memberId = \YunShop::app()->getMemberId();

        $couponId = request('coupon_id');
        $total = request('exchange_total') ?: 1;
        /**
         * @var $couponModel Coupon
         */
        $couponModel = Coupon::find($couponId);

        $memberCoupon = (new PreMemberCoupon);
        $memberCoupon->init(Member::current(), $couponModel, $total);
        $memberCoupon->generate();

        $member = Member::current()->yzMember;
        //按前端要求, 需要返回和 couponsForMember() 方法完全一致的数据
        $coupon = Coupon::centerCouponsForMember($memberId, $member->level_id, $couponId)->get()->toArray();
        foreach ($coupon as &$item) {
            $item['has_many_member_coupon_count'] = MemberCoupon::uniacid()->select('uid')->where('coupon_id', $item['id'])->distinct()->get()->count();
        }
        $res = self::getCouponData(['data' => $coupon], $member->level_id);
        $res['data'][0]['coupon_id'] = $res['data'][0]['id'];
        return $this->successJson('ok', $res['data'][0]);
    }

    /**
     * 获取指定商品的优惠券详情
     */
    public function getGoodsCouponByCouponId()
    {
        $coupon_id = $id = \YunShop::request()->coupon_id;
        $coupon = \app\common\models\Coupon::getCouponById($coupon_id);
        if ($coupon['use_type'] == !2 && !\request()->bind_coupon_sn) {
            throw new AppException('优惠券类型错误');
        }
        //优惠券标识
        if (($coupon['total'] != self::NO_LIMIT) && ($coupon['has_many_member_coupon_count'] >= $coupon['total'])) {
            $coupon['api_availability'] = self::EXHAUST;
        } elseif ($coupon['member_got_count'] > 0) {
            $coupon['api_availability'] = self::ALREADY_GOT;
        } else {
            $coupon['api_availability'] = self::IS_AVAILABLE;
        }

        $coupon['goods'] = Goods::getGoodsByIds($coupon['goods_ids']);
        foreach ($coupon['goods'] as &$value) {
            $value['thumb'] = replace_yunshop(yz_tomedia($value['thumb']));
            if ($coupon['coupon_method'] == 1) {
                $value['deduct_price'] = $value['price'] - $coupon['deduct'];
                $value['deduct_price'] = $value['deduct_price'] >= 0 ? $value['deduct_price'] : 0;
            } elseif ($coupon['coupon_method'] == 2) {
                $value['deduct_price'] = $value['price'] * $coupon['discount'] / 10;
            }
        }
        $shop = \Setting::get('shop.shop');
        $coupon['icon'] = replace_yunshop(yz_tomedia($shop['logo']));  //商城logo
        $coupon['description'] = htmlspecialchars_decode(\Setting::getByGroup('coupon')['description']); //领券说明
        if (!$coupon['description']) {
            $coupon['description'] = $coupon->content;
        }
        if ($coupon['time_limit'] != 1) {
            $coupon['time_start'] = date('Y-m-d H:i:s', time());
            if ($coupon['time_days'] == 0) {
                $times = 86400 * 36500;
                $coupon['time_end'] = date('Y-m-d H:i:s', time() + $times);
            } else {
                $times = 86400 * $coupon['time_days'];
                $coupon['time_end'] = date('Y-m-d H:i:s', time() + $times);
            }
        }
        $coupon['bind_coupon_wait_get'] = 0;
        if (\request()->bind_coupon_sn && app('plugins')->isEnabled('bind-coupon')) {
            $bind_coupon = \Yunshop\BindCoupon\models\BindCouponLog::uniacid()
                ->where('coupon_sn', \request()->bind_coupon_sn)->first();
            if (!$bind_coupon) {
                return $this->errorJson('绑定优惠券不存在');
            }
            $coupon['bind_coupon_wait_get'] = $bind_coupon->status == 0 ? 1 : -1;
        }

        return $this->successJson('', $coupon);

    }

    public function getDetail()
    {
        $id = \YunShop::request()->record_id;
        $record = MemberCoupon::uniacid()->with(['member', 'belongsToCommonCoupon' => function ($query) {
            return $query->select(['id', 'name', 'coupon_method', 'deduct', 'discount', 'enough', 'use_type', 'category_ids', 'categorynames',
                'goods_ids', 'goods_names', 'storeids', 'storenames', 'time_limit', 'time_days', 'time_start', 'time_end', 'total',
                'money', 'credit', 'plugin_id', 'content', 'is_complex']);
        }])->find($id);
        if (!$record) {
            throw new AppException('无此条记录');
        }
        $coupon_set = \Setting::getByGroup('coupon');
        if ($coupon_set['is_singleton'] == 1) {
            $rule = '每个订单仅限使用一张优惠券，不可与其它优惠券叠加使用';
        }
        if ($coupon_set['is_singleton'] == 0) {
            if ($record->belongsToCommonCoupon->is_complex == 0) {
                $rule = '每个订单本张优惠券仅限使用一张，若还满足其他优惠券使用条件，可叠加使用';
            } else {
                $rule = '每个订单本张优惠券可使用多张，若还满足其他优惠券使用条件，可叠加使用';
            }
        }

        $coupons_num = MemberCoupon::uniacid()
            ->where(['used' => 0, 'is_member_deleted' => 0, 'is_expired' => 0, 'uid' => $record->uid, 'coupon_id' => $record->coupon_id])
            ->count();

        $data = [
            'img_url' => $record->member->avatar_image ?: '',
            'rule' => $rule,
            'coupon' => $record->belongsToCommonCoupon,
            'is_complex' => $record->belongsToCommonCoupon->is_complex,
            'multiple_use' => ['is_open' => $coupon_set['transfer_num'] == 1 ? true : false, 'nums' => $coupons_num]
        ];
        $data["coupon_fee_plugin"] = \app\common\modules\shop\ShopConfig::current()
            ->get('couponFee_plugin');
        if (app('plugins')->isEnabled('share-coupons')) {
            $data['share_coupon'] = \Setting::get('share-coupons.is_open') == 1 ? true : false;
        }
        if (app('plugins')->isEnabled('write-off-coupons')) {
            $data['write_off_coupon'] = \Setting::get('write-off-coupons.is_open') == 1 ? true : false;
        }
        $data = array_merge($data, [
            'transfer' => Setting::get('coupon.transfer') ? true : false,
            'combine_show' => $coupon_set['coupon_show'] == 1 ? true : false,
        ]);
        $member_coupon = $record ? $record->toArray() : [];
        if ($member_coupon) {
            $data['coupon'] = $data['coupon'] ? $data['coupon']->toArray() : [];
            if ($data['coupon']) {
                $data['coupon']['time_start'] = $member_coupon['time_start'];
                $data['coupon']['time_end'] = $member_coupon['time_end'];
            }
        }

        $data['bind_coupon_url'] = '';
        if (app('plugins')->isEnabled('bind-coupon') && $data['coupon']['id'] && ($coupons_num > 0)) {
            if ($bind_coupon = \Yunshop\BindCoupon\models\BindCoupon::uniacid()
                ->where('coupon_id', $data['coupon']['id'])
                ->where('status', 0)
                ->whereHas('hasOneCoupon', function ($query) {
                    $query->where('time_limit', 0)->orWhere('time_end', '>', time());
                })->first()) {
                $data['bind_coupon_url'] = \Yunshop\BindCoupon\services\SettingService::shareUrl($bind_coupon->id, \YunShop::app()->getMemberId());
            }
        }

        return $this->successJson('ok', $data);
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

    /**
     * 获取优惠卷的指定的商品和门店
     * @param CouponConditionService $conditionService
     * @return array
     * @throws AppException
     */
    public function getStoreAndGoods(CouponConditionService $conditionService)
    {
        $this->validate([
            'coupon_id' => 'required|integer|exists:app\frontend\modules\coupon\models\Coupon,id',
        ]);
        $coupon_id = request()->coupon_id;
        $coupon = Coupon::select(['storeids', 'use_type', 'use_conditions', 'plugin_id'])->find($coupon_id);
        $condition_list = $conditionService->getCondition($coupon);
        return $this->successJson('成功', $condition_list);
    }

}
