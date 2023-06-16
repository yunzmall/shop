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
        'coupon_show'         => '0'
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

        $this->coupon_show = $this->couponShow();
        $this->deduction_lang = $this->deductionLang();
        $this->default_deduction = $this->defaultDeduction();//添加开启默认积分抵扣按钮

        return $this;
    }

    private function defaultDeduction()
    {
        return \Setting::get('point.set')['default_deduction'] ?: 0;
    }

    private function couponShow()
    {
        return \Setting::getByGroup('coupon')['coupon_show'];
    }

    private function deductionLang()
    {
        $langSetting = \Setting::get('shop.lang');

        return $langSetting[$langSetting['lang']]['order']['deduction_lang'] ?: "抵扣";
    }

    protected function getCoupons()
    {
        return $this->trade->orders->getMemberCoupons();
    }

}