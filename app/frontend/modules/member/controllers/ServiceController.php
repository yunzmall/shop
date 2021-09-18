<?php
/**
 * Created by PhpStorm.
 * User: 17812
 * Date: 2020/4/28
 * Time: 10:07
 */

namespace app\frontend\modules\member\controllers;

use app\common\components\ApiController;

class ServiceController extends ApiController
{
    public function index()
    {
        $res = [];
        if (app('plugins')->isEnabled('customer-service')) {
            $set = array_pluck(\Setting::getAllByGroup('customer-service')->toArray(), 'value', 'key');
            if ($set['is_open'] == 1) {
                if (request()->type == 2) {
                    $arr = $this->miniSetting($set);
                }else{
                    $arr = $this->getSetting($set);
                }
                $res = $arr;
            }
        }
        return $res;
    }

    public function supplier_set($uid,$type)
    {
        $res = ['mark'=>false];
        if (app('plugins')->isEnabled('customer-service')) {
            $set = array_pluck(\Setting::getAllByGroup('customer-service')->toArray(), 'value', 'key');
            if ($set['is_open'] == 1) {
                $supplierSet = \Setting::get('plugin.supplier.customer[' . $uid . ']');
                if($supplierSet['is_open'] == 1)
                {
                    if ($type == 2) {
                        $arr = $this->miniSetting($supplierSet);
                    }else{
                        $arr = $this->getSetting($supplierSet);
                    }
                }else{
                    if ($type == 2) {
                        $arr = $this->miniSetting($set);
                    }else{
                        $arr = $this->getSetting($set);
                    }
                }
                $res = array_merge($arr,['mark'=>true]);
            }
        }
        return $res;
    }

    public function store_set($store_ID,$type)
    {
        $res = ['mark'=>false];
        if (app('plugins')->isEnabled('customer-service')) {
            $set = array_pluck(\Setting::getAllByGroup('customer-service')->toArray(), 'value', 'key');
            if ($set['is_open'] == 1) {
                $storeSet = \Setting::get('plugin.store.customer[' . $store_ID . ']');
                if($storeSet['is_open'] == 1)
                {
                    if ($type == 2) {
                        $arr = $this->miniSetting($storeSet);
                    }else{
                        $arr = $this->getSetting($storeSet);
                    }
                }else{
                    if ($type == 2) {
                        $arr = $this->miniSetting($set);
                    }else{
                        $arr = $this->getSetting($set);
                    }
                }
                $res = array_merge($arr,['mark'=>true]);
            }
        }
        return $res;
    }
    
    public function getSetting($set)
    {
         return [
             'cservice'=>$set['link'],
             'service_QRcode' => yz_tomedia($set['QRcode']),
             'service_mobile' => $set['mobile']
         ];
    }

    public function miniSetting($set)
    {
        return [
            'cservice'=>$set['mini_link'],
            'customer_open'=>$set['mini_open'],
            'service_QRcode' => yz_tomedia($set['mini_QRcode']),
            'service_mobile' => $set['mini_mobile']
        ];
    }
}