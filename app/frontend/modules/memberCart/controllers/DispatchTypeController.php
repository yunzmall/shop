<?php
/**
 * Created by PhpStorm.
 * 
 * 
 *
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
        //todo 分时预约商品不需要配送方式，但它是实体商品，目前放这里处理
        $need_address = $groupCollection->contains(function (DispatchTypeOrder $order) {
            if ($order->plugin_id != 130) {
                return $order->isVirtual() === false;
            } else {
                return $order->plugin_id != 130;
            }
        });

        if (!$need_address) {
            return [];
        }

        foreach ($groupCollection as $key => $item) {

            $dispatchTypeManager = new DispatchTypeMenuService($enableDispatchType, $item);
            $dispatchTypes = $dispatchTypeManager->getOrderDispatchType();
            $parameter = $dispatchTypes->map(function (DispatchTypeMenu $dispatchType) use ($item) {
                $name = $dispatchType->getName();
                switch ($dispatchType->getId()) {
                    case DispatchType::STORE_PACKAGE_DELIVER :
                        $name = $dispatchType->getStoreDeliverName(request('store_id'));
                        break;
                    case DispatchType::EXPRESS :
                        $name = \Setting::get('shop.lang.zh_cn.order.express')?:'快递';
                        break;
                }
                return [
                    'dispatch_type_id' => $dispatchType->getId(),
                    'name' => $name,
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

            //优化允许配置购物车类自定义条件和使用参数，使用匿名函数
            if ($memberCart instanceof \Closure) {
                $goods_ids = call_user_func($memberCart,request())->whereIn('id', $cart_ids)->pluck('goods_id')->toArray();
            } else {
                $goods_ids =  $memberCart::whereIn('id', $cart_ids)->pluck('goods_id')->toArray();
            }
            return $goods_ids;
        }

        $goods_ids = json_decode(request()->input('goods_ids'), true);

        return $goods_ids;
    }

    protected function getGoods()
    {

        $goods_ids = $this->getParam();

        $goodsList = Goods::whereIn('id',$goods_ids)
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

        $goodsList = Goods::whereIn('id',$goods_ids)
            ->select(['id','id as goods_id', 'uniacid', 'brand_id', 'type', 'status', 'title', 'thumb', 'sku', 'market_price', 'price', 'cost_price', 'stock', 'reduce_stock_method', 'show_sales', 'real_sales', 'weight', 'has_option', 'is_deleted', 'comment_num', 'is_plugin', 'plugin_id', 'virtual_sales', 'no_refund', 'need_address', 'type2'])
            ->get();

        $memberCarts = new DispatchTypeGoodsCollection($goodsList);
        $memberCarts->loadRelations();

        $goodsCollection = $memberCarts;

        $groupCollection = $this->getOrderGoodsCollection($goodsCollection,$member, $request);


        return $this->getDispatchType($groupCollection);
    }
}