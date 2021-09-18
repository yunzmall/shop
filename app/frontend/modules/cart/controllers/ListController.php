<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2021/4/6
 * Time: 15:48
 */

namespace app\frontend\modules\cart\controllers;


use app\common\components\ApiController;
use app\common\exceptions\AppException;
use app\frontend\models\Member;
use app\frontend\modules\cart\models\MemberCart;
use app\frontend\modules\cart\services\GroupManager;
use app\frontend\modules\member\services\MemberCartService;

class ListController extends ApiController
{

    public $transactionActions = ['store'];

    public function index()
    {


        $member_id =  \YunShop::app()->getMemberId();

        $cartList = app('CartContainer')->make('MemberCart')->carts()->filterFailureGoods()
            ->where('yz_member_cart.member_id', $member_id)
//            ->pluginId()
            ->with(["hasManyAddress"=>function($query) use ($member_id){
                return $query->where("uid",$member_id)->where("isdefault",1);
            }])
            ->with(["hasManyMemberAddress"=> function($query) use ($member_id){
                return $query->where("uid",$member_id)->where("isdefault",1);
            }])

            ->orderBy('yz_member_cart.created_at', 'desc')
            ->get();

        $manager = new GroupManager();
        $manager->init($cartList);
        $cartLists = $manager->cartList();
        return $this->successJson('list', $cartLists);
    }

    //已失效购物车商品
    public function failureCart()
    {
        $member_id =  \YunShop::app()->getMemberId();
        $cartList = app('CartContainer')->make('MemberCart')->carts()
            ->join('yz_goods', 'yz_goods.id', '=', 'yz_member_cart.goods_id')
            ->where(function ($where) {
                return $where->where('yz_goods.status','!=',1)->orWhereNotNull('yz_goods.deleted_at');
            })
            ->where('yz_member_cart.member_id', $member_id)
            ->orderBy('yz_member_cart.created_at', 'desc')
            ->get();

        $cartList->map(function ($cart) {
            $cart->goods_title = $cart->goods->title;
            $cart->goods_thumb = yz_tomedia($cart->goods->thumb);
            $cart->goods_price = $cart->goodsOption?$cart->goodsOption->product_price:$cart->goods->price;
            $cart->goods_option_title = $cart->goodsOption?$cart->goodsOption->title:'';
            unset($cart->goods);
            unset($cart->goodsOption);
            return $cart;
        });


        return $this->successJson('failureCart', $cartList);
    }

    //删除失效购物车
    public function delFailureCart()
    {
        $member_id =  \YunShop::app()->getMemberId();
        $cart_ids = app('CartContainer')->make('MemberCart')->select('yz_member_cart.id')
            ->join('yz_goods', 'yz_goods.id', '=', 'yz_member_cart.goods_id')
            ->where(function ($where) {
                return $where->where('yz_goods.status','!=',1)->orWhereNotNull('yz_goods.deleted_at');
            })
            ->where('yz_member_cart.member_id', $member_id)
            ->orderBy('yz_member_cart.created_at', 'desc')
            ->pluck('yz_member_cart.id')->toArray();

        $bool = MemberCart::whereIn('id', $cart_ids)->delete();

        return $this->successJson('del',$bool);
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
            $num = intval(request()->input('total'))?:1;
            $hasGoodsModel->total = $hasGoodsModel->total + $num;

            $hasGoodsModel->validate();

            if ($hasGoodsModel->update()) {
                return $this->successJson('添加购物车成功', ['cart_id' => $cart_id]);
            }
            return $this->errorJson('数据更新失败，请重试！');
        }
        $cartModel->validate();

        $validator = $cartModel->validator($cartModel->getAttributes());
        if ($validator->fails()) {
            return $this->errorJson("数据验证失败，添加购物车失败！！！");
        } else {
            if ($cartModel->save()) {
                event(new \app\common\events\cart\AddCartEvent($cartModel));
                return $this->successJson("添加购物车成功");
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