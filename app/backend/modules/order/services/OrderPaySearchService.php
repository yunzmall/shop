<?php
/**
 * Created by PhpStorm.
 * 
 *
 *
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