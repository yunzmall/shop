<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/11/30
 * Time: 13:43
 */

namespace app\frontend\modules\goods\controllers;


use app\common\components\ApiController;
use app\frontend\modules\member\controllers\ServiceController;

class CustomerServiceController extends ApiController
{
    public function index()
    {

        $goods_id = intval(request()->input('goods_id'));

        //客户端类型
        $type = intval(request()->input('type'));


        //1.商城客服设置
        $shopSet = \Setting::get('shop.shop');
        $shop_cservice = $shopSet['cservice']?:'';

        //客服插件基础设置
        $this->apiData  = (new ServiceController())->index();


        if (empty($this->apiData)) {
            $this->apiData =  [
                'cservice'=> '',
                'service_QRcode' => '',
                'service_mobile' => ''
            ];
        }


        //2.客服插件设置
        $alonge_cservice = $this->apiData['cservice'];

        if (!is_null(\app\common\modules\shop\ShopConfig::current()->get('customer_service'))) {
            $class = array_get(\app\common\modules\shop\ShopConfig::current()->get('customer_service'), 'class');
            $function = array_get(\app\common\modules\shop\ShopConfig::current()->get('customer_service'), 'function');
            $ret = $class::$function($goods_id,$type);
            if ($ret && is_array($ret)) {
                foreach ($ret as $rk => $rv) {
                    $this->apiData[$rk] = $rv;
                }
            }
        }


        $store_cservice = '';
        $rg_cservice = '';
        if (app('plugins')->isEnabled('store-cashier')) {
            //3.门店单独客服设置
            if(class_exists('\Yunshop\StoreCashier\common\services\CustomerService')) {
                $storeSet =  \Yunshop\StoreCashier\common\services\CustomerService::getCservice($goods_id,$type);
                if ($storeSet && is_array($storeSet)) {
                    foreach ($storeSet as $sk => $sv) {
                        $this->apiData[$sk] = $sv;
                    }
                    //先将门店单独客服设置的cservice取出
                    if($storeSet['cservice']) {
                        $store_cservice = $storeSet['cservice'];
                    }
                }

            }

            //门店后台单独设置客服链接
            if(class_exists('\Yunshop\StoreCashier\store\models\StoreService')){
                $store_id = \Yunshop\StoreCashier\common\models\StoreGoods::where('goods_id',$goods_id)->value('store_id');
                if ($store_id) {
                    $store_service = \Yunshop\StoreCashier\store\models\StoreService::where("store_id",$store_id)->first();
                    if($store_service) {
                        $rg_cservice = $store_service['service'];
                    }
                }
            }
        }




        //满足1.门店独立设置 2.客服插件 3.人工客服 4.商城
        if($store_cservice){
            $this->apiData['cservice'] = $store_cservice;
        }else if($alonge_cservice){
            $this->apiData['cservice'] = $alonge_cservice;
        }else if ($rg_cservice){
            $this->apiData['cservice'] = $rg_cservice;
        }else{
            $this->apiData['cservice'] = $shop_cservice;
        }


        return $this->successJson('', $this->apiData);
    }
}