<?php
/**
 * Created by PhpStorm.
 * User: shenyang
 * Date: 2018/11/23
 * Time: 5:11 PM
 */

namespace app\common\modules\trade\models;

use app\common\models\DispatchType;
use app\common\models\Goods;
use app\common\facades\Setting;
use app\common\models\BaseModel;
use app\frontend\modules\memberCart\controllers\DispatchTypeController;
use app\frontend\modules\order\dispatch\DispatchTypeMenu;
use app\frontend\modules\order\models\PreOrder;


class TradeDispatch extends BaseModel
{

    protected $appends = ['delivery_method', 'recommend_goods', 'use_wechat_address', 'custom_data'];


    /**
     * @var Trade
     */
    private $trade;

    public function init(Trade $trade)
    {
        $this->trade = $trade;
        $this->setRelation('default_member_address', $this->getMemberAddress());
        return $this;
    }

    /**
     * @return mixed
     */
    private function getMemberAddress()
    {
        return $this->trade->orders->first()->orderAddress->getMemberAddress();
    }

    /**
     * 交易中所有商品配送方式的交集
     * @return array
     */
    protected function _gteDeliveryMethod()
    {

        //todo 虚拟订单不需要配送方式
        $need_address = $this->trade->orders->contains(function (PreOrder $order) {
            return $order->isVirtual() === false;
        });

        if (!$need_address) {
            return [];
        }

        $orders = $this->trade->orders;

        // 遍历获取订单的有效配送方式
        foreach ($this->trade->orders as $order) {
            $dispatchTypeManager = new \app\frontend\modules\order\dispatch\DispatchTypeMenuManager($order, $orders);
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


    public function getDeliveryMethodAttribute()
    {
        if (!miniVersionCompare('1.1.105') || !versionCompare('1.1.105')) {
            return $this->tempDeliveryMethod();
        }
        return [];
    }

    protected function tempDeliveryMethod()
    {

        $goods_id = $this->trade->orders->map(function (PreOrder $order) {
            return $order->orderGoods->pluck('goods_id');
        })->collapse()->values()->toArray();


        $dispatch = (new DispatchTypeController())->getOrderDispatch($goods_id);

        return $dispatch;
    }

    // 只有预下单里面的订单全部是自营, 才计算他们的运费, 再是否显示推荐商品
    public function getRecommendGoodsAttribute()
    {
        $is_display = true;

        foreach ($this->trade->orders as $model) {
            $pass = [0, 92, 44];
            if (!in_array($model->plugin_id, $pass)) {
                $is_display = false;
                break;
            }
        }

        if (!$is_display || \Setting::get('shop.order.order_apart')) {
            return [];
        }

        $dispatch_price = $this->trade->orders->sum('dispatch_price');
        $enoughReduce = Setting::get('enoughReduce.freeFreight');
        if ($enoughReduce['open']
            && $enoughReduce['postage_included_category_open']
            && $dispatch_price > 0) {
            $goods = Goods::select('id', 'title', 'price', 'thumb')->whereStatus(1)
                ->whereHas('hasManyPostageIncluded', function ($query) {
                    $query->where('is_display', 1);
                })
                ->inRandomOrder()->take(4)->get();
            $goods->each(function (&$model) {
                $model->thumb = yz_tomedia($model->thumb);
            });
            return $goods;
        }
        
        return [];
    }

    public function getUseWechatAddressAttribute()
    {
        return (bool)Setting::get('shop.order.use_wechat_address');
    }

    // 配送方式的一些需要的数据放到这里处理然后返回. custom_data
    public function getCustomDataAttribute()
    {
        $data = '';

        if (request()->input('dispatch_type_id') == DispatchType::PACKAGE_DELIVER) {

            $data = [
                'custom_consignee' => Setting::get('plugin.package_deliver.custom_consignee') ?: '提货人姓名',
                'custom_phone' => Setting::get('plugin.package_deliver.custom_phone') ?: '提货人手机'
            ];
        }

        return $data;
    }

}
