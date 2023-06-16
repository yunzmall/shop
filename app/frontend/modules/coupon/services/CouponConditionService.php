<?php

namespace app\frontend\modules\coupon\services;

use app\frontend\modules\coupon\models\Coupon;
use app\frontend\modules\coupon\models\Goods;
use app\frontend\modules\coupon\models\Store;

class CouponConditionService
{

    private $latitude = null;
    private $longitude = null;

    /**
     * 获取优惠卷条件中的门店和商品
     * @param Coupon $coupon
     * @return array
     */
    public function getCondition (Coupon $coupon)
    {
        $this->latitude = request()->latitude;
        $this->longitude = request()->longitude;

        /**
         * @var $storeMo Store
         */
        $storeMo = app(Store::class);

        $data = [];
        switch ($coupon['use_type']){
            case 4 : $data = $this->getStore($coupon, $storeMo); break;
            case 9 : $data = $this->getStoreAndGoods($coupon, $storeMo); break;
            case 5 : $data = $this->getIndependentStore($coupon, $storeMo); break;
            default : break;
        }
        //判断是否为独立后台标识
        $data['is_shop'] = $coupon['plugin_id'] ? true : false;
        return $data;
    }

    /**
     * 优惠卷是独立门店和使用类型是5
     * 获取独立门店数据
     * @param Store $storeMo
     * @param Coupon $coupon
     * @return array
     */
    private function getIndependentStore(Coupon $coupon, Store $storeMo) : array
    {
        $data = [];
        if($coupon['plugin_id']){
            $store = $storeMo->getStoreList([$coupon['storeids']], $this->latitude, $this->longitude)->toArray();
            $data['store_list'] = $store['data'];
        }
        return $data;
    }

    /**
     * <h1>指定门店</h1>
     * 获取门店
     * @param Coupon $coupon
     * @param Store $storeMo
     * @return array
     */
    private function getStore($coupon, $storeMo) : array
    {
        $store_ids = json_decode($coupon['storeids']);
        $data = [];
        $store_list = [];
        if($store_ids != null){
            $store_list = $storeMo->getStoreList($store_ids, $this->latitude, $this->longitude);
        }
        $data['condition_type'] = 1;
        $data['store_list'] = $store_list;
        return $data;
    }

    /**
     * <h1>选择商品+选择门店</h1>
     * 获取商品和门店
     * @param Coupon $coupon
     * @param Store $storeMo
     * @return array
     */
    private function getStoreAndGoods($coupon, $storeMo) : array
    {

        /*门店*/
        $use_conditions = unserialize($coupon['use_conditions']);
        $store_list = [];
        if($use_conditions['is_all_store'] == 1){ // 全选门店
            $store_list = $storeMo->getStoreList([], $this->latitude, $this->longitude);
        }elseif ($use_conditions['store_ids'] != null){ //指定门店
            $store_list = $storeMo->getStoreList($use_conditions['store_ids'], $this->latitude, $this->longitude);
        }
        /*门店*/

        /*商品*/
        $goods_list = [];
        $goods_mo = Goods::select(['title', 'price', 'thumb', 'id'])->uniacid();
        if($use_conditions['is_all_good'] == 1){ // 全选商品
            $goods_list = $goods_mo->limit(10)->get();
        }elseif ($use_conditions['good_ids'] != null){ //指定商品
            $goods_list = $goods_mo->whereIn('id', $use_conditions['good_ids']?:[])->paginate(16,[],'goods_page');
        }
        /*商品*/
        $data['goods_list'] = $goods_list;
        $data['store_list'] = $store_list;
        $data['condition_type'] = 2;
        $data['is_all_good'] = $use_conditions['is_all_good'];
        $data['is_all_store'] = $use_conditions['is_all_store'];

        return $data;
    }

}