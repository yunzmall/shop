<?php

namespace app\frontend\modules\coupon\models;

/**
 * Class Coupon
 * @package app\frontend\modules\coupon\models
 * @property int status
 * @property int get_type
 * @property int level_limit
 */
class Coupon extends \app\common\models\Coupon
{
    public $table = 'yz_coupon';

    protected $casts = [
        'goods_ids' => 'json',
        'category_ids' => 'json',
        'goods_names' => 'json',
        'categorynames' => 'json',
        'time_start' => 'date',
//        'time_end' => 'date',
        'member_tags_ids' => 'json',
        'member_tags_names' => 'json',
    ];

    const TYPE_ALL = 0;//全部
    const TYPE_SHOP = 1;//平台
    const TYPE_STORE = 2;//门店
    const TYPE_HOTEL = 3;//酒店
    const TYPE_GOODS = 4;//指定商品
    const TYPE_CATE = 5;//指定分类
    const TYPE_EXCHANGE = 6;//兑换券
    const TYPE_MONEY_OFF = 7;//满减券
    const TYPE_DISCOUNT = 8;//折扣券
    const TYPE_OVERDUE = 9;//快过期
    const TYPE_GOOD_AND_STORE = 10;//平台商品和门店

    /**
     * @var array
     */
    public static $typeComment = [
        self::TYPE_ALL => '全部',
        self::TYPE_SHOP => '平台',
        self::TYPE_STORE => '门店',
        self::TYPE_HOTEL => '酒店',
        self::TYPE_GOODS => '指定商品',
        self::TYPE_CATE => '分类商品',
        self::TYPE_EXCHANGE => '兑换券',
        self::TYPE_MONEY_OFF => '满减券',
        self::TYPE_DISCOUNT => '折扣券',
        self::TYPE_GOOD_AND_STORE => '平台商品和门店',
    ];


    //前台需要整数的"立减值"
//    public function getDeductAttribute($value)
//    {
//        return intval($value);
//    }

    //前台需要整数的"折扣值", 即"打几折"
//    public function getDiscountAttribute($value)
//    {
//        return intval($value);
//    }

    //获取该用户可领取的优惠券的状态
    public static function getCouponsForMember($memberId, $memberLevel, $couponId = null, $time = null, $coupon_type = '')
    {
        // 通过id找到会员等级
        $memberLevel = \app\common\models\MemberLevel::find($memberLevel)->level;

        $res = static::uniacid()
            ->select(['yz_coupon.id', 'yz_coupon.name', 'yz_coupon.coupon_method', 'yz_coupon.deduct', 'yz_coupon.discount', 'yz_coupon.enough', 'yz_coupon.use_type', 'yz_coupon.category_ids',
                'yz_coupon.categorynames', 'yz_coupon.goods_ids', 'yz_coupon.goods_names', 'yz_coupon.time_limit', 'yz_coupon.time_days', 'yz_coupon.time_start', 'yz_coupon.time_end', 'yz_coupon.get_max', 'yz_coupon.total',
                'yz_coupon.money', 'yz_coupon.credit', 'yz_coupon.updated_at', 'use_conditions']);
        if ($coupon_type) {
            switch ($coupon_type) {
                case Coupon::TYPE_SHOP:
                    $res->where('yz_coupon.use_type', Coupon::COUPON_SHOP_USE);
                    break;
                case Coupon::TYPE_STORE:
                    if (app('plugins')->isEnabled('store-cashier')) {
                        $res->whereIn('yz_coupon.use_type', [Coupon::COUPON_STORE_USE, Coupon::COUPON_SINGLE_STORE_USE]);
                    }
                    break;
                case Coupon::TYPE_HOTEL:
                    if (app('plugins')->isEnabled('hotel')) {
                        $res->whereIn('yz_coupon.use_type', [Coupon::COUPON_ONE_HOTEL_USE, Coupon::COUPON_MORE_HOTEL_USE]);
                    }
                    break;
                case Coupon::TYPE_GOODS:
                    $res->where('yz_coupon.use_type', Coupon::COUPON_GOODS_USE);
                    break;
                case Coupon::TYPE_CATE:
                    $res->where('yz_coupon.use_type', Coupon::COUPON_CATEGORY_USE);
                    break;
                case Coupon::TYPE_EXCHANGE:
                    $res->where('yz_coupon.use_type', Coupon::COUPON_EXCHANGE_USE);
                    break;
                case Coupon::TYPE_GOOD_AND_STORE:
                    if (app('plugins')->isEnabled('store-cashier')) {
                        $res->where('yz_coupon.use_type', Coupon::COUPON_GOODS_AND_STORE_USE);
                    }
                    break;
                case Coupon::TYPE_MONEY_OFF:
                    $res->where('yz_coupon.coupon_method', Coupon::COUPON_MONEY_OFF);
                    break;
                case Coupon::TYPE_DISCOUNT:
                    $res->where('yz_coupon.coupon_method', Coupon::COUPON_DISCOUNT);
                    break;
            }
        };
        if (!app('plugins')->isEnabled('store-cashier')) {
            $res->whereNotIn('yz_coupon.use_type', [Coupon::COUPON_STORE_USE, Coupon::COUPON_SINGLE_STORE_USE]);
        }
        if (!app('plugins')->isEnabled('hotel')) {
            $res->whereNotIn('yz_coupon.use_type', [Coupon::COUPON_ONE_HOTEL_USE, Coupon::COUPON_MORE_HOTEL_USE]);
        }
        $res->where('yz_coupon.get_type', '=', 1)
            ->where('yz_coupon.status', '=', 1)
            ->where('yz_coupon.get_max', '!=', 0)
            // 优惠券的level_limit改为存储yz_member_level表的id，所以要关联yz_member_level表
            //->memberLevel($memberLevel);
            ->leftjoin('yz_member_level', 'yz_coupon.level_limit', '=', 'yz_member_level.id')
            ->where(function ($query) use ($memberLevel) {
                $query->where('yz_member_level.level', '<=', !empty($memberLevel) ? $memberLevel : 0)//如果会员等级为空，也就是会员表等级默认的0，则默认为0，等级肯定大于等于1
                ->orWhere('yz_coupon.level_limit', '=', -1);
            });

        if (!is_null($couponId)) {
            $res = $res->where('yz_coupon.id', '=', $couponId);
        }

        if (!is_null($time)) {
            $res = $res->unexpired($time);
        }

        return $res->withCount(['hasManyMemberCoupon as member_got_count' => function ($query) use ($memberId) {
                return $query->where('uid', '=', $memberId);
            }]);
    }

    //领券中心复制一份出来，为避免影响其他功能
    public static function centerCouponsForMember($memberId, $memberLevel, $couponId = null, $time = null, $coupon_type = '')
    {
        // 通过id找到会员等级
        $memberLevel = \app\common\models\MemberLevel::find($memberLevel)->level;

        $res = static::uniacid()
            ->select(['yz_coupon.id', 'yz_coupon.name', 'yz_coupon.coupon_method', 'yz_coupon.deduct', 'yz_coupon.discount', 'yz_coupon.enough', 'yz_coupon.use_type', 'yz_coupon.category_ids',
                'yz_coupon.categorynames', 'yz_coupon.goods_ids', 'yz_coupon.goods_names', 'yz_coupon.time_limit', 'yz_coupon.time_days', 'yz_coupon.time_start', 'yz_coupon.time_end', 'yz_coupon.get_max', 'yz_coupon.total',
                'yz_coupon.money', 'yz_coupon.credit', 'yz_coupon.updated_at', 'use_conditions', 'yz_coupon.is_integral_exchange_coupon', 'yz_coupon.exchange_coupon_integral']);
        if ($coupon_type) {
            switch ($coupon_type) {
                case Coupon::TYPE_SHOP:
                    $res->where('yz_coupon.use_type', Coupon::COUPON_SHOP_USE);
                    break;
                case Coupon::TYPE_STORE:
                    if (app('plugins')->isEnabled('store-cashier')) {
                        $res->whereIn('yz_coupon.use_type', [Coupon::COUPON_STORE_USE, Coupon::COUPON_SINGLE_STORE_USE]);
                    }
                    break;
                case Coupon::TYPE_HOTEL:
                    if (app('plugins')->isEnabled('hotel')) {
                        $res->whereIn('yz_coupon.use_type', [Coupon::COUPON_ONE_HOTEL_USE, Coupon::COUPON_MORE_HOTEL_USE]);
                    }
                    break;
                case Coupon::TYPE_GOODS:
                    $res->where('yz_coupon.use_type', Coupon::COUPON_GOODS_USE);
                    break;
                case Coupon::TYPE_CATE:
                    $res->where('yz_coupon.use_type', Coupon::COUPON_CATEGORY_USE);
                    break;
                case Coupon::TYPE_EXCHANGE:
                    $res->where('yz_coupon.use_type', Coupon::COUPON_EXCHANGE_USE);
                    break;
                case Coupon::TYPE_GOOD_AND_STORE:
                    if (app('plugins')->isEnabled('store-cashier')) {
                        $res->where('yz_coupon.use_type', Coupon::COUPON_GOODS_AND_STORE_USE);
                    }
                    break;
                case Coupon::TYPE_MONEY_OFF:
                    $res->where('yz_coupon.coupon_method', Coupon::COUPON_MONEY_OFF);
                    break;
                case Coupon::TYPE_DISCOUNT:
                    $res->where('yz_coupon.coupon_method', Coupon::COUPON_DISCOUNT);
                    break;
            }
        };
        if (!app('plugins')->isEnabled('store-cashier')) {
            $res->whereNotIn('yz_coupon.use_type', [Coupon::COUPON_STORE_USE, Coupon::COUPON_SINGLE_STORE_USE]);
        }
        if (!app('plugins')->isEnabled('hotel')) {
            $res->whereNotIn('yz_coupon.use_type', [Coupon::COUPON_ONE_HOTEL_USE, Coupon::COUPON_MORE_HOTEL_USE]);
        }

        //会员标签查询
        if (app('plugins')->isEnabled('member-tags')) {
            //存在会员标签的才进行查询限制
            $memberTags = \Yunshop\MemberTags\Common\models\MemberTagsRelationModel::uniacid()->where('member_id', $memberId)->pluck('tag_id');
            $res->where(function ($query) use ($memberTags) {
                $query->where(function ($query2) use ($memberTags) {
                    $i = 0;
                    foreach ($memberTags as $item) {
                        if ($i > 0) {
                            $query2->orWhereRaw("FIND_IN_SET(?,TRIM(TRAILING ']' FROM TRIM(LEADING '[' FROM member_tags_ids)))", $item);
                        } else {
                            $query2->whereRaw("FIND_IN_SET(?,TRIM(TRAILING ']' FROM TRIM(LEADING '[' FROM member_tags_ids)))", $item);
                        }
                        $i++;
                    }
                })->orWhere(function ($query) {
                    $query->whereNull('member_tags_ids')->orWhereRaw("LENGTH(TRIM( TRAILING ']' FROM TRIM( LEADING '[' FROM member_tags_ids ) )) = 0");
                });
            });
        }

        $res->where('yz_coupon.get_type', '=', 1)
            ->where('yz_coupon.status', '=', 1)
            ->where('yz_coupon.get_max', '!=', 0)
            // 优惠券的level_limit改为存储yz_member_level表的id，所以要关联yz_member_level表
            //->memberLevel($memberLevel);
            ->leftjoin('yz_member_level', 'yz_coupon.level_limit', '=', 'yz_member_level.id')
            ->where(function ($query) use ($memberLevel) {
                $query->where('yz_member_level.level', '<=', !empty($memberLevel) ? $memberLevel : 0)//如果会员等级为空，也就是会员表等级默认的0，则默认为0，等级肯定大于等于1
                ->orWhere('yz_coupon.level_limit', '=', -1);
            });

        if (!is_null($couponId)) {
            $res = $res->where('yz_coupon.id', '=', $couponId);
        }

        if (!is_null($time)) {
            $res = $res->unexpired($time);
        }

        return $res->withCount(['hasManyMemberCoupon as member_got_count' => function ($query) use ($memberId) {
                return $query->where(['uid' => $memberId , 'get_type' => 1]);
            }]);
    }

    //指定ID的, 在优惠券中心可领取的, 优惠券
    public static function getAvailableCouponById($couponId)
    {
        return static::uniacid()
            ->where('id', '=', $couponId)
            ->where(function ($query) {
                $query->where('total', '>', 0)
                    ->orWhere(function ($query) {
                        $query->where('total', '=', -1);
                    });
            })
            ->where('status', '=', 1)
            ->where('get_type', '=', 1)
            ->first();
    }
}
