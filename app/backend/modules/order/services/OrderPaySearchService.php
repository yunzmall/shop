<?php
/**
 * Created by PhpStorm.
 * Name: 芸众商城系统
 * Author: 广州市芸众信息科技有限公司
 * Profile: 广州市芸众信息科技有限公司位于国际商贸中心的广州，专注于移动电子商务生态系统打造，拥有芸众社交电商系统、区块链数字资产管理系统、供应链管理系统、电子合同等产品/服务。官网 ：www.yunzmall.com  www.yunzshop.com
 * Date: 2021/4/28
 * Time: 15:50
 */

namespace app\backend\modules\order\services;


use app\common\models\PayType;

/**
 * 需要隐藏的支付方式
 * Class OrderPaySearchService
 * @package app\backend\modules\order\services
 */
class OrderPaySearchService
{
    /**
     * @param $payType
     */
    public static function hiddenPay($payTypes)
    {
        $result = collect([]);
        foreach ($payTypes as $payType) {
            if (!in_array($payType['id'],self::hiddenPayType())) {
                $result->push($payType);
            }
        }

        return $result;
    }


    //订单支付类型搜索项
    public static function searchPayTerm()
    {
        $payTypes = \app\common\models\PayType::select('id','name')->get();

        $payTypes = self::hiddenPay($payTypes);


        return $payTypes;
    }

    public static function hiddenPayType()
    {
        return [4,6,7,12,15,18,19,22,23,27,42,43];
    }
}