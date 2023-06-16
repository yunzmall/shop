<?php
/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 2021/9/9
 * Time: 17:39
 */

namespace app\backend\modules\goods\widget;

use app\common\models\goods\GoodsCoupon;
use app\backend\modules\coupon\models\Coupon;

/**
 * 优惠券(非插件)
 */
class CouponWidget extends BaseGoodsWidget
{
    public $group = 'marketing';

    public $widget_key = 'coupon';

    public $code = 'coupon';

    public function pluginFileName()
    {
        return 'goods';
    }


    public function getData()
    {
        $couponModel = GoodsCoupon::ofGoodsId($this->goods->id)->first();

        $data = [
            'is_give' => 0,   //订单完成赠送优惠券 1开启 0关闭
            'send_type' => 0, // 1.订单完成后的1分钟后发放 0.每月1号 0:00发放
            'send_num' => '', //连续发放 n 月
            'coupon' => [
               // ['coupon_several' => 0, //多少张优惠券
               // 'coupon_id' =>0, //优惠券id
               // 'coupon_name' =>'',] //优惠券名称
            ], //优惠券列表
            'shopping_share' => 0, //购买商品分享优惠券： 1开启 0关闭
            'share_coupon' => [
                // ['coupon_several' => 0, //多少张优惠券
                // 'coupon_id' =>0, //优惠券id
                // 'coupon_name' =>'',] //优惠券名称
            ], //购买商品分享优惠券列表
            'no_use' => 0, //禁止使用优惠券 1开启 0关闭
            'is_use_num' => 0, //可使用优惠券数量限制 1开启 0关闭
            'use_num' => '', //可使用数量
        ];

        if ($couponModel) {
            $data = $couponModel->toArray();
        }

        return $data;
    }

    public function pagePath()
    {
        return $this->getPath('resources/views/goods/assets/js/components/');
    }
}