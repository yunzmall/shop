<?php
namespace app\frontend\modules\refund\services;

use app\common\exceptions\AppException;
use app\common\models\Order;
use app\common\models\refund\RefundApply;
use app\common\modules\refund\RefundOrderFactory;

/**
 * Created by PhpStorm.
 * Author:
 * Date: 2017/4/13
 * Time: 下午2:21
 */
class RefundService
{

    static public function refundApplyData(Order $order)
    {

        if ($order->refund_id) {
            throw new AppException('已存在售后申请，处理中');
        }

        //预约订单限制
        if (!is_null(\app\common\modules\shop\ShopConfig::current()->get('store_reserve_refund'))) {
            $class = array_get(\app\common\modules\shop\ShopConfig::current()->get('store_reserve_refund'), 'class');
            $function = array_get(\app\common\modules\shop\ShopConfig::current()->get('store_reserve_refund'), 'function');
            $plugin_res = $class::$function($order->id);
            if(!$plugin_res['res']) {
                throw new AppException($plugin_res['msg']);
            }
        }

        $refundOrder = RefundOrderFactory::getInstance()->getRefundOrder($order);

        $refundOrder->applyBeforeValidate();

        //0,92,120
        //支持部分退款的订单类型，平台订单，供应商订单，中台供应链
        $data['support_batch'] = $refundOrder->multipleRefund();

        $data['order'] = $refundOrder->frontendFormatArray();

        $refundedPrice = $refundOrder->getAfterSales();

        $orderOtherPrice = $refundOrder->getOrderOtherPrice();

        $orderFreightPrice = $refundOrder->getOrderFreightPrice();

        //这里减去运费和其他费用是因为前端直接拿这个字段当订单金额，但是售后现在把运费分离出来了。
        $data['order_goods_price'] = max($data['order']['price'] - $orderFreightPrice - $orderOtherPrice,0);

        //可退运费
        $data['refundable_freight'] = max(bcsub($orderFreightPrice, $refundedPrice->sum('freight_price'),2),0);
        //订单可退其他费用
        $data['refundable_other'] = max(bcsub($orderOtherPrice, $refundedPrice->sum('other_price'),2),0);


        $data['send_back_way'] = RefundService::getSendBackWay($order);

        $data['refundTypes'] = RefundService::getOptionalType($order);

        return $data;
    }

    public static function getOptionalType(Order $order)
    {
        $refundTypes = [];

        if ($order->status >= \app\common\models\Order::WAIT_SEND) {
            $refundTypes[] = [
                'name'  => '退款(仅退款不退货)',
                'value' => RefundApply::REFUND_TYPE_REFUND_MONEY,
                'desc'  => '未收到货或者不用退货只退款',
                'icon'  =>  'icon-fontclass-daizhifu',
                'reasons' => [
                    'not_received' => [
                        '拍错/多拍/不想要',
                        '货物破损',
                        '快递送货问题',
                        '差价',
                        '其他原因'
                    ],
                    'received' => [
                        '货物破损',
                        '少件、漏发',
                        '差价',
                        '商品质量问题',
                        '其他原因',
                    ],
                ],
            ];
        }

        if ($order->status >= \app\common\models\Order::WAIT_RECEIVE) {

            $refundTypes[] = [
                'name' => '退款退货',
                'value' =>  RefundApply::REFUND_TYPE_RETURN_GOODS,
                'desc'  => '已收到货，需要退款退货',
                'icon'  =>  'icon-fontclass-daishouhuo',
                'reasons' => [
                    'not_received' => [],
                    'received' => [
                        '包装或商品破损、少商品',
                        '质量问题',
                        '配送问题',
                        '拍错/多拍/不想要',
                        '其他原因（可填）',
                    ]
                ],
            ];

            $refundTypes[] = [
                'name' => '换货',
                'value' => RefundApply::REFUND_TYPE_EXCHANGE_GOODS,
                'desc'  => '已收到货，需要更换',
                'icon'  =>  'icon-fontclass-daifahuo',
                'reasons' => [
                    'not_received' => [],
                    'received' => [],
                ],
            ];
        }

        return $refundTypes;
    }

    public static function getSendBackWay($order)
    {
        return RefundBackWayService::getBackWay($order);
    }

    public static function getSendBackWayData($refundApply)
    {
        return RefundBackWayService::getBackWayClassData($refundApply);
    }

    public static function getSendBackWayDetailData($refundApply)
    {
        return RefundBackWayService::getBackWayDetailData($refundApply);
    }

    public static function createOrderRN()
    {
        $refundSN = createNo('RN', true);
        while (1) {
            if (!RefundApply::where('refund_sn', $refundSN)->first()) {
                break;
            }
            $refundSN = createNo('RN', true);
        }
        return $refundSN;
    }

}
