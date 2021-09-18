<?php
/**
 * Created by PhpStorm.
 * Name: 芸众商城系统
 * Author: 广州市芸众信息科技有限公司
 * Profile: 广州市芸众信息科技有限公司位于国际商贸中心的广州，专注于移动电子商务生态系统打造，拥有芸众社交电商系统、区块链数字资产管理系统、供应链管理系统、电子合同等产品/服务。官网 ：www.yunzmall.com  www.yunzshop.com
 * Date: 2021/6/2
 * Time: 9:58
 */

namespace app\frontend\modules\memberCart\controllers;


use app\common\components\ApiController;
use app\common\models\DispatchType;
use app\frontend\models\Member;
use app\frontend\models\MemberCart;
use app\frontend\modules\memberCart\models\DispatchTypeOrder;
use app\frontend\modules\memberCart\models\Goods;
use app\frontend\modules\memberCart\services\DispatchTypeGoodsCollection;
use app\frontend\modules\memberCart\services\DispatchTypeMenuService;
use app\frontend\modules\order\dispatch\DispatchTypeMenu;

class DispatchTypeController extends ApiController
{
    public function index()
    {
        $request =  request();

        $member = Member::current();

        $goodsCollection = $this->getGoods();

        $groupCollection = $this->getOrderGoodsCollection($goodsCollection,$member, $request);

        return $this->successJson('dispatch_type',$this->getDispatchType($groupCollection));

    }


    public function getDispatchType($groupCollection)
    {

        $enableDispatchType =  DispatchType::getAllEnableDispatchType();

        //todo 虚拟订单不需要配送方式
        $need_address = $groupCollection->contains(function (DispatchTypeOrder $order) {
            return $order->isVirtual() === false;
        });

        if (!$need_address) {
            return [];
        }

        foreach ($groupCollection as $key => $item) {

            $dispatchTypeManager = new DispatchTypeMenuService($enableDispatchType, $item);
            $dispatchTypes = $dispatchTypeManager->getOrderDispatchType();
            $parameter = $dispatchTypes->map(function (DispatchTypeMenu $dispatchType) {
                return [
                    'dispatch_type_id' => $dispatchType->getId(),
                    'name' => $dispatchType->getName(),
                ];
            })->values();
            if ($parameter->isNotEmpty()) {
                $parameters[] = $parameter;
            }
        }

        if (empty($parameters)) {
            return [];
        }

        $result = $parameters[0];

        foreach ($parameters as $parameter) {

            // 与结果取差，删掉不相交的值
            $diffIds = $result->pluck('dispatch_type_id')->diff($parameter->pluck('dispatch_type_id'));

            foreach ($result as $key => $item) {
                if ($diffIds->contains($item['dispatch_type_id'])) {
                    unset($result[$key]);
                }
            }
        }

        return $result->values();

    }

    /**
     * @throws \app\common\exceptions\ShopException
     */
    protected function validateParam()
    {

    }

    protected function getMemberCart()
    {
        $model = request()->input('model');
        
        $memberCart = \app\common\modules\shop\ShopConfig::current()->get('shop-foundation.member-cart.models')[$model];
        if (!$memberCart) {
            $memberCart = \app\common\modules\shop\ShopConfig::current()->get('shop-foundation.member-cart.models.shop');
        }

        return $memberCart;
    }

    protected function getParam()
    {
        $cart_ids = request()->input('cart_ids');

        $goods_id = intval(request()->input('goods_id'));


        if ($goods_id) {
            return [$goods_id];
        }

        if (is_string($cart_ids)) {
            $cart_ids = explode(',', $cart_ids);
        }

        if ($cart_ids) {

            $memberCart = $this->getMemberCart();
            $goods_ids =  $memberCart::whereIn('id', $cart_ids)->pluck('goods_id')->toArray();
        }
        return $goods_ids;
    }

    protected function getGoods()
    {

        $goods_ids = $this->getParam();

        $goodsList = Goods::
            whereIn('id',$goods_ids)
            ->select(['id','id as goods_id', 'uniacid', 'brand_id', 'type', 'status', 'title', 'thumb', 'sku', 'market_price', 'price', 'cost_price', 'stock', 'reduce_stock_method', 'show_sales', 'real_sales', 'weight', 'has_option', 'is_deleted', 'comment_num', 'is_plugin', 'plugin_id', 'virtual_sales', 'no_refund', 'need_address', 'type2'])
            ->get();

        $memberCarts = new DispatchTypeGoodsCollection($goodsList);
        $memberCarts->loadRelations();
        return $memberCarts;
    }

    protected function getOrderGoodsCollection(DispatchTypeGoodsCollection $goodsCollection,$member = null,$request)
    {
        // 按插件分组
        $groups = $goodsCollection->groupByGroupId()->values();

        // 分组下单
        $orderCollection = $groups->map(function (DispatchTypeGoodsCollection $goodsCollection) use ($member,$request) {
            return $goodsCollection->getOrder($member, $request);
        });

        return $orderCollection;

    }

    public function getOrderDispatch($goods_ids)
    {
        $request =  request();

        $member = Member::current();

        $goodsList = Goods::
        whereIn('id',$goods_ids)
            ->select(['id','id as goods_id', 'uniacid', 'brand_id', 'type', 'status', 'title', 'thumb', 'sku', 'market_price', 'price', 'cost_price', 'stock', 'reduce_stock_method', 'show_sales', 'real_sales', 'weight', 'has_option', 'is_deleted', 'comment_num', 'is_plugin', 'plugin_id', 'virtual_sales', 'no_refund', 'need_address', 'type2'])
            ->get();

        $memberCarts = new DispatchTypeGoodsCollection($goodsList);
        $memberCarts->loadRelations();

        $goodsCollection = $memberCarts;

        $groupCollection = $this->getOrderGoodsCollection($goodsCollection,$member, $request);


        return $this->getDispatchType($groupCollection);
    }
}