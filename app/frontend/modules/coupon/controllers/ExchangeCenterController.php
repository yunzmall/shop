<?php


namespace app\frontend\modules\coupon\controllers;


use app\common\components\ApiController;
use app\common\models\Goods;
use app\frontend\modules\coupon\models\Coupon;
use app\frontend\modules\coupon\models\MemberCoupon;
use app\frontend\modules\member\services\MemberCartService;
use app\frontend\modules\memberCart\MemberCartCollection;
use Carbon\Carbon;

class ExchangeCenterController extends ApiController
{
    /**
     * 兑换中心接口
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $pluginId = request()->input('platform_id', 0);
        $uid = \YunShop::app()->getMemberId();
        $coupons = MemberCoupon::getExchange($uid, $pluginId)->get();
        //过滤过期优惠券
        $coupons = $coupons->filter(function ($coupon) {
            return strtotime($coupon->time_end) > strtotime(date('Y-m-d')) || $coupon->time_end == '不限时间';
        });
        $coupons = $coupons->groupBy('coupon_id');
        $res = [];
        $list = [];
        foreach ($coupons as $key => $item) {
            if (empty($item)) {
                continue;
            }
            $list[$key] = $item[0]->toArray();
            $list[$key]['total'] = $item->count();
            $list[$key]['goods_id'] = array_unique($list[$key]['belongs_to_coupon']['goods_ids']);
        }
        if (!empty($list)) {
            $list = array_values(collect($list)->sortByDesc('get_time')->toArray());
            foreach ($list as &$re) {
                if ($pluginId == 32) {
                    $store_goods = \Yunshop\StoreCashier\common\models\StoreGoods::select('goods_id','store_id')
                        ->where('goods_id', $re['goods_id'][0])
                        ->with(['store' => function ($query) {
                            $query->select('id','store_name');
                        }])->first();
                    if ($store_goods) {
                        $re['store_id'] = $store_goods->store_id;
                        $re['store_name'] = $store_goods->store->store_name;
                    }
                }
                if ($pluginId == 33) {
                    $hotel_goods = \Yunshop\Hotel\common\models\HotelGoods::select('goods_id','hotel_id')
                        ->where('goods_id', $re['goods_id'][0])
                        ->with(['hotel' => function ($query) {
                            $query->select('id','hotel_name');
                        }])->first();
                    if ($hotel_goods) {
                        $re['hotel_id'] = $hotel_goods->hotel_id;
                        $re['hotel_name'] = $hotel_goods->hotel->hotel_name;
                    }
                }
            }
        }
        $res['list'] = $list;
        $res['navigation'][0] = [
            'id' => 0,
            'name' => '商城'
        ];
        if (app('plugins')->isEnabled('store-cashier')) {
            $res['navigation'][1] = [
                'id' => 32,
                'name'=>'门店'
            ];
        }
        if (app('plugins')->isEnabled('hotel')) {
            $res['navigation'][2] = [
                'id' => 33,
                'name' => HOTEL_NAME,
            ];
        }
        return $this->successJson('ok', $res);
    }


    /**
     * @return MemberCartCollection
     * @throws \app\common\exceptions\MemberNotLoginException
     */
    protected function getMemberCarts()
    {
        $data = request()->input('data');
        $couponCount = array_column($data,'coupon_id');
        //获取可以兑换的优惠券Id
        $memberCoupon = MemberCoupon::getExchange(\YunShop::app()->getMemberId(),0)
            ->whereIn('coupon_id',$couponCount)
            ->get()
            ->toArray();
        foreach ($memberCoupon as $key => $v) {
            $goodsIds[] = $v['belongs_to_coupon']['goods_ids'][0];
            if (strtotime($v['time_end']) < strtotime(date('Y-m-d')) && $v['time_end'] != '不限时间') {
                unset($memberCoupon[$key]);
                continue;
            }
        }
        $data = array_column($data,null,'coupon_id');
        $member_coupon_ids = [];
        foreach ($memberCoupon as $key => $value) {
            if ($data[$value['coupon_id']]) {
                if (count($member_coupon_ids[$value['coupon_id']]) == $data[$value['coupon_id']]['total']) {
                    continue;
                }
                $member_coupon_ids [$value['coupon_id']][] = $value['id'];
            }
        }
        $member_coupon_id = array();
        foreach ($member_coupon_ids as $value) {
            foreach ($value as $v) {
                $member_coupon_id[] = $v;
            }
        }
        $member_coupon_ids = implode(',',$member_coupon_id);
        if (request()->input('is_exchange') == 1) {
            request()->offsetSet('member_coupon_ids', $member_coupon_ids);
        }
        $result = new MemberCartCollection();
        foreach ($data as $key => $value) {
            unset($value['coupon_id']);
            $good = Goods::with('hasManyOptions')->find($value['goods_id']);
            if ($good && $option = $good->hasManyOptions()->first()) { //兑换券使用商品有规格时取第一个
                $value['option_id'] = $option->id;
            }
            $result->push(MemberCartService::newMemberCart($value));
        }
        return $result;
    }

    /**
     * 验证
     */
    private function validateParam()
    {
        $this->validate([
            'data' => 'required',
            'data.0.goods_id' => 'required | min:1 |integer',
            'data.0.total' => 'required | min:1 | integer',
            'data.0.coupon_id' => 'required | min:1 |integer',
        ]);
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     * @throws \app\common\exceptions\MemberNotLoginException
     */
    public function exchangeBuy()
    {
        $this->validateParam();
        $trade = $this->getMemberCarts()->getTrade();
        return $this->successJson('成功', $trade);
    }

}

