<?php

namespace app\common\services\member\center;

use app\backend\modules\order\models\Order;
use app\common\helpers\Cache;
use app\common\models\Income;
use app\common\models\Member;
use app\common\models\MemberShopInfo;
use app\common\services\popularize\PortType;
use app\frontend\models\Goods;
use app\frontend\models\Order as FrontendOrder;
use app\frontend\modules\coupon\models\MemberCoupon;
use app\frontend\modules\member\models\MemberFavorite;
use app\frontend\modules\member\models\MemberHistory;

class MemberCenterService extends BaseMemberCenterService
{
    public function getData(): array
    {
        $data[] = [
            'name' => 'member_code',
            'title' => '会员卡号 ',
            'class' => 'icon-member_posvip_cardnum',
            'url' => 'uidCode',
            'image' => 'member_a(83).png',
            'mini_url' => '',
            'type_1' => 'essential_tool',
            'type_2' => 'tool',
            'weight_1' => 600,
            'weight_2' => 300,
            'default_weight' => 4
        ];
        $data[] = [
            'name' => 'member_pay_code',
            'title' => '动态验证码',
            'class' => 'icon-member_pospay_validation',
            'url' => 'codePage',
            'image' => 'member_a(82).png',
            'mini_url' => '/packageI/dynamic_code/code/code',
            'type_1' => 'essential_tool',
            'type_2' => 'tool',
            'weight_1' => 700,
            'weight_2' => 400,
            'default_weight' => 5
        ];
        $data[] = [
            'name' => 'findpwd',
            'title' => '忘记密码',
            'class' => 'icon-fontclass-wangjimima2',
            'url' => 'findpwd',
            'image' => 'member_a(124).png',
            'mini_url' => '/packageE/findpwd/findpwd',
            'type_1' => 'essential_tool',
            'type_2' => 'tool',
            'weight_1' => 500,
            'weight_2' => 900,
        ];
        //推广中心
        if (PortType::popularizeShow(request()->input('type'))) {
            $data[] = [
                'name' => 'extension',
                'title' => '推广中心',
                'class' => 'icon-member-extension1',
                'url' => 'extension',
                'image' => 'member_a(38).png',
                'mini_url' => '/packageG/pages/member/extension/extension',
                'type_1' => 'my_assets',
                'type_2' => 'asset_equity',
                'weight_1' => 12100,
                'weight_2' => 3200,
            ];
        }
        if (\Setting::getByGroup('coupon')['exchange_center'] == 1) {
            $data[] = [
                'name' => 'exchange',
                'title' => '兑换中心',
                'class' => 'icon-member_changer_centre',
                'url' => 'CouponExchange',
                'image' => 'member_a(74).png',
                'mini_url' => '/packageC/CouponExchange/index',
                'type_1' => 'service',
                'type_2' => 'market',
                'weight_1' => 2600,
                'weight_2' => 10700,
                'default_weight' => 28
            ];
        }
        $data[] = [
            'name' => "m-pinglun",
            'title' => "评论",
            'class' => 'icon-fontclass-pinglun',
            'url' => "myEvaluation",
            'image' => 'tool_a(4).png',
            'mini_url' => "/packageD/member/myEvaluation/myEvaluation",
            'type_1' => 'interactive',
            'type_2' => 'market',
            'weight_1' => 4100,
            'weight_2' => 20,
            'default_weight' => 2
        ];
        $data[] = [
            'name' => "m-guanxi",
            'title' => "客户",
            'class' => 'icon-fontclass-kehu',
            'url' => "myRelationship",
            'image' => 'tool_a(3).png',
            'mini_url' => "/packageD/member/myRelationship/myRelationship",
            'type_1' => 'essential_tool',
            'type_2' => 'market',
            'weight_1' => 800,
            'weight_2' => 30,
            'default_weight' => 3
        ];
        $data[] = [
            'name' => "m-collection",
            'title' => "收藏",
            'class' => 'icon-fontclass-1',
            'url' => "collection",
            'image' => 'tool_a(6).png',
            'mini_url' => "/packageD/member/collection/collection",
            'type_1' => 'essential_tool',
            'type_2' => 'tool',
            'weight_1' => 100,
            'weight_2' => 10,
        ];
        $data[] = [
            'name' => "m-footprint",
            'title' => "足迹",
            'class' => 'icon-fontclass-zuji2',
            'url' => "footprint",
            'image' => 'tool_a(8).png',
            'mini_url' => "/packageD/member/footprint/footprint",
            'type_1' => 'essential_tool',
            'type_2' => 'tool',
            'weight_1' => 200,
            'weight_2' => 20,
        ];
        $data[] = [
            'name' => "m-address",
            'title' => "地址管理",
            'class' => 'icon-fontclass-dizhi',
            'url' => "address",
            'image' => 'tool_a(1).png',
            'mini_url' => "/packageD/member/addressList/addressList",
            'type_1' => 'essential_tool',
            'type_2' => 'tool',
            'weight_1' => 300,
            'weight_2' => 30,
        ];
        $data[] = [
            'name' => "m-info",
            'title' => "设置",
            'class' => 'icon-fontclass-shezhi',
            'url' => "info",
            'image' => 'tool_a(5).png',
            'mini_url' => "/packageA/member/info/info",
            'type_1' => 'essential_tool',
            'type_2' => 'tool',
            'weight_1' => 400,
            'weight_2' => 40,
        ];
        $data[] = [
            'name' => "m-coupon",
            'title' => "优惠券",
            'class' => 'icon-fontclass-youhuiquan',
            'image' => 'tool_a(7).png',
            'url' => "coupon",
            'mini_url' => "/packageA/member/coupon_v2/coupon_v2",
            'type_1' => 'essential_tool',
            'type_2' => 'market',
            'weight_1' => 1100,
            'weight_2' => 40,
        ];
        $is_agent = MemberShopInfo::uniacid()->where('member_id', \YunShop::app()->getMemberId())->value('is_agent');
        if ($this->isBackendRoute() || $is_agent == 1) {
            $data[] = [
                'name' => "m-erweima",
                'title' => "二维码",
                'class' => 'icon-fontclass-erweima',
                'url' => 'm-erweima',
                'mini_url' => 'm-erweima',
                'image' => 'tool_a(2).png',
                'type_1' => 'interactive',
                'type_2' => 'market',
                'weight_1' => 4000,
                'weight_2' => 10,
                'default_weight' => 1,
            ];
        }

        return $data;
    }

    public function getOrderData(): array
    {
        $status_list = [Order::WAIT_PAY, Order::WAIT_SEND, Order::WAIT_RECEIVE, Order::REFUND];
        $list = collect(FrontendOrder::getOrderCountGroupByStatus($status_list))->sortBy('status')->values()->toArray();
        $list[] = [
            'status' => -2,
            'status_name' => '全部订单',
            'class' => 'icon-fontclass-quanbudingdan',
            'total' => 0
        ];
        $data[] = [
            'key' => 'shop',
            'name' => '商城订单',
            'weight' => 0,
            'diy_key' => 'U_memberorder',
            'data' => $list,
        ];
        return $data;
    }

    public function getAssetsData(): array
    {
        $data = [];
        //余额
        if (!\Setting::get('shop.member.show_balance')) {
            $data[] = [
                'key' => 'balance',
                'name' => \Setting::get('shop.shop.credit') ?: '余额',
                'value' => Member::current()['credit2'],
                'unit' => '¥',
                'url' => 'balance',
                'min_url' => '/packageA/member/balance/balance/balance',
                'diy_key' => 'balance',
                'weight' => 60
            ];
        }
        //积分
        if (!\Setting::get('shop.member.show_point')) {
            $data[] = [
                'key' => 'point',
                'name' => \Setting::get('shop.shop.credit1') ?: '积分',
                'value' => Member::current()['credit1'],
                'unit' => '',
                'url' => 'integral_v2',
                'min_url' => '/packageB/member/integral/integral',
                'diy_key' => 'integral_v2',
                'weight' => 70
            ];
        }
        //优惠券
        if (!app('MemberCenter')->isDiy()) {
            $data[] = [
                'key' => 'coupon',
                'name' => '优惠券',
                'value' => MemberCoupon::getCouponsOfMember(\YunShop::app()->getMemberId())
                    ->where('used', '=', 0)
                    ->where('is_member_deleted', 0)
                    ->where('is_expired', 0)
                    ->count(),
                'unit' => '',
                'url' => 'coupon',
                'min_url' => '/packageA/member/coupon_v2/coupon_v2',
                'weight' => 80,
            ];

        }

        if (app('MemberCenter')->isDiy()) {
            $data[] = [
                'key' => 'extension',
                'name' => '提现',
                'value' => function ($item) {
                    if ($item['extension_radio'] == 1) {
                        $amount = Income::getIncomes()->where('member_id', \YunShop::app()->getMemberId())->sum('amount') ?? 0;
                    } else {
                        $amount = Income::getIncomes()->where('member_id', \YunShop::app()->getMemberId())->where('status', 0)->sum('amount') ?? 0;
                    }
                    return number_format($amount, 2);
                },
                'unit' => '¥',
                'url' => 'extension',
                'min_url' => '/packageG/pages/member/extension/extension',
                'diy_key' => 'extension',
                'weight' => 110
            ];
        }
        return $data;
    }

    public function getPluginHead(): array
    {
        return [
            [
                'title' => '商品收藏',
                'class' => 'icon-fontclass-shoucang',
                'value' => MemberFavorite::getFavoriteCount(\YunShop::app()->getMemberId()) ?: 0,
                'mini_url' => "/packageD/member/collection/collection",
                'url' => "collection"
            ],
            [
                'title' => '浏览记录',
                'class' => 'icon-fontclass-liulan',
                'value' => MemberHistory::getMemberHistoryCount(\YunShop::app()->getMemberId()) ?: 0,
                'mini_url' => "/packageD/member/footprint/footprint",
                'url' => "footprint"
            ],
        ];

    }

    public function getPluginData(): array
    {
        $data = [];
        if ($this->getEnabledRecommendGoods()) {
            $data[] = [
                'code' => 'recommendGoods',
                'diy_code' => 'recommend_goods',
                'name' => '推荐商品',
                'sort' => 0,
                'class' => __CLASS__,
            ];
        }
        if ($this->getEnabledLimitBuyGoods()) {
            $data[] = [
                'code' => 'limitBuyGoods',
                'diy_code' => 'limitBuy_goods',
                'name' => '限时抢购',
                'sort' => 1,
                'class' => __CLASS__
            ];
        }
        return $data;

    }

    public function getOtherData(): array
    {
        return [
            'service' => \Setting::get('shop.shop')['cservice'] ?: '',
            'setting' => [
                'wechat_login_mode' => (bool)\Setting::get('shop.member')['wechat_login_mode'],
                'has_avatar' => Member::current()->has_avatar,
                'member_auth_pop_switch' => \Setting::get('plugin.min_app.member_auth_pop_switch') ? 1 : 0
            ]
        ];
    }

    public function recommendGoods()
    {
        $size = 20;
        $goods = Goods::uniacid()->select('yz_goods.id', 'yz_goods.title', 'yz_goods.thumb', 'yz_goods.market_price',
            'yz_goods.show_sales', 'yz_goods.virtual_sales', 'yz_goods.price', 'yz_goods.stock', 'yz_goods.has_option', 'yz_goods.plugin_id')
            ->with(['hasManyOptions' => function ($query) {
                $query->select('goods_id', 'product_price', 'market_price');
            }])
            ->whereIn('yz_goods.plugin_id', [0, 92, 40, 57, 58, 103, 101, 113])
            ->where('yz_goods.status', 1)
            ->where('yz_goods.is_recommand', 1) //推荐商品
            ->orderBy('yz_goods.id', 'desc');

        if (app('plugins')->isEnabled('video-demand')) {
            //排除掉视频点播插件的商品
            $goods = $goods->whereNotIn('id', function ($query) {
                $query->from('yz_video_course_goods')->select('goods_id')->where('is_course', 1);
            });
        }
        $goods = $goods->paginate($size);
        foreach ($goods as &$good) {
            $good['name'] = $good->title;
            $good['img'] = yz_tomedia($good->thumb);
            $good['stock_status'] = 0;
            $good['price_level'] = $good->vip_next_price ? 1 : 0;
            $good['sales'] = $good->show_sales + $good->virtual_sales;
            $good['vip_level_status'] = $good->vip_level_status;
            if ($good->has_option) {
                $minMarketPrice = $good->hasManyOptions->sortBy('market_price')->first()['market_price'] ?: 0;
                $minPrice = $good->hasManyOptions->sortBy('product_price')->first()['product_price'] ?: 0;
                $maxMarketPrice = $good->hasManyOptions->sortByDesc('market_price')->first()['market_price'] ?: 0;
                $maxPrice = $good->hasManyOptions->sortByDesc('product_price')->first()['product_price'] ?: 0;
                if ($minMarketPrice == $maxMarketPrice) {
                    $good['priceold'] = $minMarketPrice;
                }
                $good['priceold'] = ($minMarketPrice == $maxMarketPrice) ? $minMarketPrice : ($minMarketPrice . '-' . $maxMarketPrice);
                $good['pricenow'] = ($minPrice == $maxPrice) ? $minPrice : ($minPrice . '-' . $maxPrice);
            } else {
                $good['priceold'] = $good->market_price;
                $good['pricenow'] = $good->price;
            }
        }
        $goods = $goods->toArray();
        $vip_not_shop = 0;
        if (!app('plugins')->isEnabled('member-price') || \Setting::get('plugin.member-price.is_open_micro') != 1) {
            $vip_not_shop = 1;
        }
        foreach ($goods['data'] as &$item) {
            $item['vip_next_price'] = $item['next_level_price'];
            $item['notshow'] = $vip_not_shop;
        }
        unset($item);
        return $goods;
    }

    public function limitBuyGoods()
    {
        $size = 20;
        $goods = Goods::uniacid()->select('yz_goods.id', 'yz_goods.title', 'yz_goods.thumb', 'yz_goods.market_price',
            'yz_goods.show_sales', 'yz_goods.virtual_sales', 'yz_goods.price', 'yz_goods.stock', 'yz_goods.has_option', 'yz_goods.plugin_id', 'yz_goods_limitbuy.start_time', 'yz_goods_limitbuy.end_time')
            ->join('yz_goods_limitbuy', function ($join) {
                $join->on('yz_goods.id', '=', 'yz_goods_limitbuy.goods_id')->where(function ($where) {
                    return $where->where('yz_goods_limitbuy.status', 1)
                        ->where('yz_goods_limitbuy.start_time', '<=', time())
                        ->where('yz_goods_limitbuy.end_time', '>', time());
                });
            })
            ->with(['hasManyOptions' => function ($query) {
                $query->select('goods_id', 'product_price', 'market_price');
            }])
            ->whereIn('yz_goods.plugin_id', [0, 92, 40, 57, 58, 103, 101, 113])
            ->where('yz_goods.status', 1)
            ->orderBy('yz_goods.id', 'desc');

        if (app('plugins')->isEnabled('video-demand')) {
            //排除掉视频点播插件的商品
            $goods = $goods->whereNotIn('yz_goods.id', function ($query) {
                $query->from('yz_video_course_goods')->select('goods_id')->where('is_course', 1);
            });
        }

        $goods = $goods->paginate($size);
        foreach ($goods as &$good) {
            $good['name'] = $good->title;
            $good['img'] = yz_tomedia($good->thumb);
            $good['stock_status'] = 0;
            $good['price_level'] = $good->vip_next_price ? 1 : 0;
            $good['sales'] = $good->show_sales + $good->virtual_sales;
            $good['vip_level_status'] = $good->vip_level_status;
            if ($good->has_option) {
                $minMarketPrice = $good->hasManyOptions->sortBy('market_price')->first()['market_price'] ?: 0;
                $minPrice = $good->hasManyOptions->sortBy('product_price')->first()['product_price'] ?: 0;
                $maxMarketPrice = $good->hasManyOptions->sortByDesc('market_price')->first()['market_price'] ?: 0;
                $maxPrice = $good->hasManyOptions->sortByDesc('product_price')->first()['product_price'] ?: 0;
                if ($minMarketPrice == $maxMarketPrice) {
                    $good['priceold'] = $minMarketPrice;
                }
                $good['priceold'] = ($minMarketPrice == $maxMarketPrice) ? $minMarketPrice : ($minMarketPrice . '-' . $maxMarketPrice);
                $good['pricenow'] = ($minPrice == $maxPrice) ? $minPrice : ($minPrice . '-' . $maxPrice);
            } else {
                $good['priceold'] = $good->market_price;
                $good['pricenow'] = $good->price;
            }
        }
        $goods = $goods->toArray();
        $vip_not_shop = 0;
        if (!app('plugins')->isEnabled('member-price') || \Setting::get('plugin.member-price.is_open_micro') != 1) {
            $vip_not_shop = 1;
        }
        foreach ($goods['data'] as &$item) {
            $item['vip_next_price'] = $item['next_level_price'];
            $item['notshow'] = $vip_not_shop;
        }
        unset($item);
        return $goods;
    }

    public function getEnabledRecommendGoods(): bool
    {
        $key = 'member_center_recommend__' . \Yunshop::app()->uniacid;
        if (!Cache::has($key)) {
            $count = Goods::uniacid()
                ->whereIn('yz_goods.plugin_id', [0, 92, 40, 57, 58, 103, 101, 113])
                ->where('yz_goods.status', 1)
                ->where('yz_goods.is_recommand', 1); //推荐商品

            if (app('plugins')->isEnabled('video-demand')) {
                //排除掉视频点播插件的商品
                $count = $count->whereNotIn('yz_goods.id', function ($query) {
                    $query->from('yz_video_course_goods')->select('goods_id')->where('is_course', 1);
                });
            }
            $count = $count->count();
            Cache::put($key, ($count ?: 0), 5);
        } else {
            $count = Cache::get($key);
        }
        if ($count > 0) {
            return true;
        }
        return false;
    }

    private function getEnabledLimitBuyGoods(): bool
    {
        $key = 'member_center_limit_buy_' . \Yunshop::app()->uniacid;
        if (!Cache::has($key)) {
            $count = Goods::uniacid()->join('yz_goods_limitbuy', function ($join) {
                $join->on('yz_goods.id', '=', 'yz_goods_limitbuy.goods_id')->where(function ($where) {
                    return $where->where('yz_goods_limitbuy.status', 1)
                        ->where('yz_goods_limitbuy.start_time', '<=', time())
                        ->where('yz_goods_limitbuy.end_time', '>', time());
                });
            });

            if (app('plugins')->isEnabled('video-demand')) {
                //排除掉视频点播插件的商品
                $count = $count->whereNotIn('yz_goods.id', function ($query) {
                    $query->from('yz_video_course_goods')->select('goods_id')->where('is_course', 1);
                });
            }
            $count = $count->whereIn('yz_goods.plugin_id', [0, 92, 40, 57, 58, 103, 101, 113])
                ->where('yz_goods.status', 1)
                ->count();
            Cache::put($key, ($count ?: 0), 5);
        } else {
            $count = Cache::get($key);
        }
        if ($count > 0) {
            return true;
        }
        return false;
    }

}