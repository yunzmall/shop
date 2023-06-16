<?php
/**
 * Created by PhpStorm.
 * User: shenyang
 * Date: 2018/8/1
 * Time: 下午5:01
 */

namespace app\frontend\modules\order\operations;

use app\common\models\Order;

abstract class OrderOperation implements OrderOperationInterface
{
    const PAY = 1; // 支付
    const COMPLETE = 5; // 确认收货
    const EXPRESS = 8; // 查看物流
    const CANCEL = 9; // 取消订单
    const COMMENT = 10; // 评论
    const DELETE = 12; // 删除订单
    const REFUND = 13; // 申请退款
    const REFUND_INFO = 18; // 已退款/退款中
    const COMMENTED = 19; // 已评价
    const REMITTANCE_RECORD = 21; // 转账信息
    const CONTACT_CUSTOMER_SERVICE = 41; // 联系客服
    const CHECK_INVOICE = 50;  //查看发票
    const View_EQUITY = 51;  //查看卡券
    const COUPON = 'coupon'; // 分享优惠卷
    const BLIND_BOX_GOODS = 53; // 盲盒商品
    const BLIND_BOX_REFUND = 54; // 盲盒申请退款
    const STORE_PROJECTS_GOODS = 56;//多门店核销商品
    const GOODS_COMMENT = 57;//评价 上面为10的不清楚有没有给用到，另起一个
    const ADDITIONAL_COMMENT = 58;//追评
    const LOOK_COMMENT = 59;//查看评价


    /**
     * @var Order
     */
    protected $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function getType()
    {
        return '';
    }

}