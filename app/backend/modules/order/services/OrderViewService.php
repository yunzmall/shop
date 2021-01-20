<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/12/3
 * Time: 14:13
 */

namespace app\backend\modules\order\services;


use app\common\models\PayType;
use app\common\models\PayTypeGroup;

class OrderViewService
{
    //自定义添加搜索项
    public static function searchTerm()
    {
        return (new OrderViewService())->getSearchTermConfig();
    }

    public function getSearchTermConfig()
    {
        $searchTerm = [];

        if (app('plugins')->isEnabled('package-deliver')) {

            $package_deliver = [
                ['name' => 'package_deliver_id', 'placeholder' => '自提点ID', 'type' => 'text'],
                ['name' => 'package_deliver_name', 'placeholder' => '自提点名称', 'type' => 'text']
            ];
            $searchTerm = array_merge($searchTerm, $package_deliver);
        }

        return $searchTerm;
    }

    /**
     * 可以搜索的支付方式
     */
    public static function searchablePayType()
    {
        $payType = [
            ['name' => '微信小程序支付', 'value' => 55],
            ['name' => '微信H5', 'value' => 50],
            ['name' => '确认支付', 'value' => 54],
            ['name' => '货到付款', 'value' => 17],
            ['name' => '汇聚快捷支付', 'value' => 59],
            ['name' => '汇聚微信', 'value' => 28],
        ];

        return $payType;
    }


    public static function payTypeGroup()
    {
        return PayTypeGroup::select('id', 'name')->get()->toArray();
    }
}