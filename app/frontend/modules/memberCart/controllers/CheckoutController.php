<?php


namespace app\frontend\modules\memberCart\controllers;


use app\common\components\ApiController;
use app\common\exceptions\AppException;
use app\common\models\MemberCart;
use app\frontend\modules\memberCart\MemberCartCollection;

class CheckoutController extends ApiController
{
    public function index()
    {
        $cartIds = [];
        if (!is_array($_GET['cart_ids'])) {
            $cartIds = explode(',', $_GET['cart_ids']);
        }
        $memberCarts = app('OrderManager')->make('MemberCart')->whereIn('id', $cartIds)->get();

        $memberCarts = new MemberCartCollection($memberCarts);
        $memberCarts->loadRelations();
        $memberCarts->validate();
        if ($memberCarts->isEmpty()) {
            throw new AppException('未找到购物车信息');
        }

        $needChoose = $memberCarts->contains(function (MemberCart $memberCart) use($memberCarts){
            return  $memberCart->goods->goodsDispatchTypeIds() != $memberCarts->first()->goods->goodsDispatchTypeIds();
        });
        if(!$needChoose){
            return $this->successJson('成功', [
                'need_choose' => 0,
                'dispatch_types' => []
            ]);
        }

        $dispatchTypes = $memberCarts->groupByDispatchType();
        foreach ($dispatchTypes as $key => $dispatchType) {
            $memberCarts = [];
            foreach ($dispatchType['member_carts'] as $memberCart) {
                $item['id'] = $memberCart['id'];
                $item['goods_id'] = $memberCart['goods_id'];
                $item['option_id'] = $memberCart['option_id'];
                $item['total'] = $memberCart['total'];
                $item['title'] = $memberCart['goods']['title'];
                $item['option_title'] = $memberCart['goodsOption']['title'];
                $item['thumb'] = $memberCart['goodsOption']['thumb'] ? yz_tomedia($memberCart['goodsOption']['thumb']) : yz_tomedia($memberCart['goods']['thumb']);
                $memberCarts[] = $item;
            }
            $dispatchTypes[$key]['member_carts'] = $memberCarts;
        }

        return $this->successJson('成功', [
            'need_choose' => 1,
            'dispatch_types' => $dispatchTypes
        ]);
    }
}