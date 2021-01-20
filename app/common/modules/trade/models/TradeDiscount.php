<?php
/**
 * Created by PhpStorm.
 * User: shenyang
 * Date: 2018/11/23
 * Time: 5:11 PM
 */

namespace app\common\modules\trade\models;


use app\common\models\BaseModel;


class TradeDiscount extends BaseModel
{

    protected $attributes = [
        'whether_show_coupon' => "1",
        'coupon_show'=>'0'
    ];

    /**
     * @var Trade
     */
    private $trade;

    public function init(Trade $trade)
    {
        $this->trade = $trade;
        $this->setRelation('memberCoupons', $this->getCoupons());

        $order_coupon = \Setting::get('coupon.order_coupon');

        //设置 order_coupon = 0：显示 1:关闭显示
        if (isset($order_coupon) && $order_coupon == 1) {
            $this->whether_show_coupon = "0";
        }


        $coupon_set = \Setting::getByGroup('coupon');

        $this->coupon_show = $coupon_set["coupon_show"];

        return $this;
    }

    protected function getCoupons()
    {
        return $this->trade->orders->getMemberCoupons();

    }

}