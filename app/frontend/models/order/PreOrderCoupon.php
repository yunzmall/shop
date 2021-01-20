<?php
/**
 * Created by PhpStorm.
 * User: shenyang
 * Date: 2017/7/25
 * Time: 下午7:33
 */

namespace app\frontend\models\order;


use app\common\models\coupon\CouponUseLog;
use app\frontend\modules\order\models\PreOrder;

class PreOrderCoupon extends \app\common\models\order\OrderCoupon
{
    public $order;
    protected $hidden = ['memberCoupon'];
    public $coupon;

    public function setOrder(PreOrder $order)
    {
        $this->order = $order;
        $this->uid = $order->uid;
        $order->orderCoupons->push($this);
    }

    public function afterSaving()
    {
        $this->push();
    }

    public function save(array $options = [])
    {
        $this->saveLog();
        if (isset($this->id)) {
            return true;
        }
        return parent::save($options);
    }

    public function saveLog()
    {
        $order = $this->order->toArray();
        foreach ($order['order_coupons'] as $v)
        {
            $log_data = [
                'uniacid' => \YunShop::app()->uniacid,
                'member_id' => \YunShop::app()->getMemberId(),
                'detail' => '会员(ID为' . \YunShop::app()->getMemberId() . ')购物使用一张优惠券(ID为' . $v['coupon_id'] . ')',
                'coupon_id' => $v['coupon_id'],
                'member_coupon_id' => $v['member_coupon_id'],
                'type' => CouponUseLog::TYPE_SHOPPING
            ];
            $model = new CouponUseLog();
            $model->fill($log_data);
            $model->save();
        }
    }
}