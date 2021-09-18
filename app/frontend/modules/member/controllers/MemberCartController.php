<?php

namespace app\frontend\modules\member\controllers;

use app\common\components\ApiController;
use app\common\exceptions\AppException;
use app\framework\Http\Request;
use app\frontend\models\Member;
use \app\frontend\models\MemberCart;
use app\frontend\modules\member\services\MemberCartService;
use app\frontend\modules\member\services\MemberService;
use Yunshop\JdSupply\services\JdOrderValidate;
use Yunshop\YzSupply\services\YzOrderValidate;

/**
 * Created by PhpStorm.
 * Author: 芸众商城 www.yunzshop.com
 * Date: 2017/2/23
 * Time: 上午10:17
 */
class MemberCartController extends ApiController
{
    public $transactionActions = ['store'];

    /**
     * @return \Illuminate\Http\JsonResponse
     * @throws AppException
     */
    public function index(Request $request, $integrated = null)
    {

        $member_id = Member::current()->uid;

        $cartList = app('OrderManager')->make('MemberCart')->carts()->where('member_id', Member::current()->uid)
            ->pluginId()
            ->with(["hasManyAddress" => function ($query) use ($member_id) {
                return $query->where("uid", $member_id)->where("isdefault", 1);
            }])
            ->with(["hasManyMemberAddress" => function ($query) use ($member_id) {
                return $query->where("uid", $member_id)->where("isdefault", 1);
            }])
            ->orderBy('created_at', 'desc')
            ->get()
            ->toArray();

        foreach ($cartList as $key => $cart) {
            $cartList[$key]['option_str'] = '';
            $cartList[$key]['goods']['thumb'] = yz_tomedia($cart['goods']['thumb']);
            $cartList[$key]['goods']['stock_status'] = 0; //正常
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

                if ($cart['goods']['plugin_id'] != 44 && $cart['goods']['plugin_id'] != 45 && $cart['goods_option']['stock'] <= 0) {
                    $cartList[$key]['goods']['stock_status'] = 1; //库存不足
                }

            } else {
                if ($cart['goods']['plugin_id'] != 44 && $cart['goods']['plugin_id'] != 45 && $cart['goods']['stock'] <= 0) {
                    $cartList[$key]['goods']['stock_status'] = 1; //库存不足
                }
            }

            if ($cart['goods']['plugin_id'] != 44 && $cart['goods']['plugin_id'] != 45 && $cart['goods']['status'] != 1) {
                $cartList[$key]['goods']['stock_status'] = 2; //已下架
            }

            if ($cart['goods']['plugin_id'] != 44 && $cart['goods']['plugin_id'] != 45 && !empty($cart['goods']['deleted_at'])) {
                $cartList[$key]['goods']['stock_status'] = 3; //已删除
            }

            if ($cart['goods']['plugin_id'] == 44 && app('plugins')->isEnabled('jd-supply')) {

                if (!empty($cart['goods_option']) && !empty($cart['has_many_address'])) {
                    $cart['has_many_address'][0]['street'] = "";
                    $is_street = \Setting::get("shop.trade")['is_street'];
                    $member_address = ($is_street == 1) ? $cart['has_many_member_address'][0] : $cart['has_many_address'][0];
                    $data = [
                        "jd_order_goods" => [
                            "goods_id" => $cart['goods']['id'],
                            "goods_option_id" => $cart['goods_option']['id'],
                            "total" => $cart['total']
                        ],
                        "orderAddress" => $member_address
                    ];

                    $jd_res = JdOrderValidate::orderValidate2($data);

                    if ($jd_res != 1) {
                        $cartList[$key]['goods']['stock_status'] = 4; //不存在
                    }
                }
            }

            //芸众供应链
            if ($cart['goods']['plugin_id'] == 120 && app('plugins')->isEnabled('yz-supply')) {
                if (!empty($cart['goods_option']) && !empty($cart['has_many_address'])) {
                    $cart['has_many_address'][0]['street'] = "";
                    $is_street = \Setting::get("shop.trade")['is_street'];
                    $member_address = ($is_street == 1) ? $cart['has_many_member_address'][0] : $cart['has_many_address'][0];
                    $data = [
                        "yz_order_goods" => [
                            "goods_id" => $cart['goods']['id'],
                            "goods_option_id" => $cart['goods_option']['id'],
                            "total" => $cart['total']
                        ],
                        "orderAddress" => $member_address
                    ];

                    $yz_res = YzOrderValidate::orderValidate2($data);;

                    if ($yz_res != 1) {
                        $cartList[$key]['goods']['stock_status'] = 4; //不存在
                    }
                }
            }

            //unset ($cartList[$key]['goods_option']);
        }

        //todo 0414 目前先这样改，有人做着购物车优化，优化后在进行修改合并
        if (app('plugins')->isEnabled('point-mall')) {
            $cartList = \Yunshop\PointMall\api\models\PointMallGoodsModel::setCartPointGoods($cartList);
        }

        if (is_null($integrated)) {
            return $this->successJson('获取列表成功', $cartList);
        } else {
            return show_json(1, $cartList);
        }

    }

    /**
     * Add member cart
     */
    public function store()
    {
        $this->validate([
            'goods_id' => 'required|integer|min:0',
            'total' => 'required|integer|min:0',
            'option_id' => 'integer|min:0',
        ]);
        $data = array(
            'member_id' => \YunShop::app()->getMemberId(),
            'uniacid' => \YunShop::app()->uniacid,
            'goods_id' => request()->input('goods_id'),
            'total' => request()->input('total'),
            'option_id' => (int)request()->input('option_id', 0),
        );
        /**
         * @var MemberCart $cartModel
         */
        $cartModel = app('OrderManager')->make('MemberCart', $data);
//        dd($cartModel);
        //验证商品是否存在购物车,存在则修改数量
        $hasGoodsModel = app('OrderManager')->make('MemberCart')->hasGoodsToMemberCart($data);
        $cart_id = $hasGoodsModel['id'];
//dd($cart_id);


        if ($hasGoodsModel) {
            $num = intval(request()->input('total')) ?: 1;
            $hasGoodsModel->total = $hasGoodsModel->total + $num;

            $hasGoodsModel->validate();

            if ($hasGoodsModel->update()) {
                return $this->successJson('添加购物车成功', [
                    'cart_id' => $cart_id,
                    'cart_num' => \app\frontend\models\MemberCart::getCartNum(\YunShop::app()->getMemberId()),
                ]);
            }
            return $this->errorJson('数据更新失败，请重试！');
        }
        $cartModel->validate();

        $validator = $cartModel->validator($cartModel->getAttributes());
        event(new \app\common\events\cart\AddCartEvent($cartModel->getAttributes()));
        if ($validator->fails()) {
            return $this->errorJson("数据验证失败，添加购物车失败！！！");
        } else {
            if ($cartModel->save()) {
                event(new \app\common\events\cart\AddCartEvent($cartModel));
                return $this->successJson("添加购物车成功", [
                    'cart_num' => \app\frontend\models\MemberCart::getCartNum(\YunShop::app()->getMemberId()),
                ]);
            } else {
                return $this->errorJson("写入出错，添加购物车失败！！！");
            }
        }
        return $this->errorJson("接收数据出错，添加购物车失败!");
    }

    /*
     * 修改购物车商品数量
     * */
    public function updateNum()
    {
        $cartId = request()->input('id');
        $num = request()->input('num');

        if (is_null($cartId)) {
            $cartId = $this->getMemberCarId();
        }

        if ($cartId && $num) {
            $cartModel = app('OrderManager')->make('MemberCart')->find($cartId);
            if ($cartModel) {
                $cartModel->total = $cartModel->total + $num;

                if ($cartModel->total < 1) {
                    $result = MemberCartService::clearCartByIds([$cartModel->id]);
                    if ($result) {
                        return $this->successJson('移除购物车成功。');
                    }
                }
                $cartModel->validate();
                if ($cartModel->update()) {
                    return $this->successJson('修改数量成功');
                }
            }
        }

        return $this->errorJson('未获取到数据，请重试！');
    }

    /*
     * 修改购物车商品数量
     * */
    public function updateNumV2()
    {
        $cartId = request()->input('id');
        $num = intval(request()->input('num'));

        if (is_null($cartId)) {
            $cartId = $this->getMemberCarId();
        }

        if ($cartId && $num) {
            $cartModel = app('OrderManager')->make('MemberCart')->find($cartId);
            if ($cartModel) {
                $cartModel->total = $num;

                if ($cartModel->total < 1) {
                    $result = MemberCartService::clearCartByIds([$cartModel->id]);
                    if ($result) {
                        return $this->successJson('移除购物车成功。');
                    }
                }
                $cartModel->validate();
                if ($cartModel->update()) {
                    return $this->successJson('修改数量成功');
                }
            }
        }

        return $this->errorJson('未获取到数据，请重试！');
    }

    /*
     * Delete member cart
     **/
    public function destroy()
    {

        $ids = explode(',', request()->input('ids'));

        if (is_null(request()->input('ids'))) {
            $ids = $this->getMemberCarId();
        }
        $result = MemberCartService::clearCartByIds($ids);

        if ($result) {
            return $this->successJson('移除购物车成功。');
        }
        throw new AppException('写入出错，移除购物车失败！');


    }

    public function getMemberCart()
    {
        $request = Request();
        $this->dataIntegrated($this->index($request, true), 'cart');
        if (app('plugins')->isEnabled('lease-toy')) {
            $this->dataIntegrated((new \Yunshop\LeaseToy\api\LeaseToyController())->whetherEnabled($request, true), 'is_lease');
            $this->dataIntegrated((new \Yunshop\LeaseToy\api\MemberCartController())->index($request, true), 'lease_cart');
            $this->dataIntegrated((new \Yunshop\LeaseToy\api\HeatRentController())->index($request, true), 'hent_rent');
        }
        return $this->successJson('', $this->apiData);
    }

    private function getMemberCarId()
    {
        $cartId = null;
        $memberId = \YunShop::app()->getMemberId();
        $goods_id = request()->input('goods_id');

        if (!is_null($memberId) && !is_null($goods_id)) {
            $cartList = app('OrderManager')->make('MemberCart')->carts()->where('member_id', $memberId)
                ->orderBy('created_at', 'desc')
                ->get();

            if (!$cartList->isEmpty()) {
                collect($cartList)->map(function ($item, $key) use ($goods_id, &$cartId) {

                    if ($item->goods_id == $goods_id) {
                        $cartId = $item->id;
                    }
                });
            }
        }

        return $cartId;
    }
}
