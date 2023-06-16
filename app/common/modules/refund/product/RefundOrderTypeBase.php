<?php
/**
 * Created by PhpStorm.
 * User: blank
 * Date: 2022/11/3
 * Time: 15:51
 */

namespace app\common\modules\refund\product;

use app\backend\modules\goods\models\GoodsTradeSet;
use app\common\models\Order;
use app\common\models\refund\RefundApply;
use app\common\models\refund\RefundGoodsLog;
use Illuminate\Support\Carbon;

abstract class RefundOrderTypeBase
{
    /**
     * @var Order
     */
    protected $order;


    /**
     * @var \app\framework\Database\Eloquent\Collection
     */
    protected $refundedCollect;

    /**
     * @var string 区别前端还是后端
     */
    protected $port;

    public function __construct(Order $order, $port = 'frontend')
    {
        $this->order = $order;

        $this->port = $port;
    }


    final public function frontendFormatArray()
    {

        $order = $this->formatOrder();
        $order['order_goods'] = $this->canApplyRefundGoods();

        return $order;
    }


    /**
     * 创建售后申请,让对应的插件进行保存前最后的业务处理
     * todo 需要注意：在这里对 $refundApply 的属性进行修改是会影响到最终保存的数据的
     * @param RefundApply $refundApply
     */
    public function handleAfterSales(RefundApply $refundApply)
    {

    }


    /**
     * 创建售后申请，部分退款标识
     * @param $applyRefundGoods ['id', 'total', 'refund_price']
     * @param $request \Illuminate\Http\Request|string
     * @return int
     */
    public function applyPratRefundStatus($applyRefundGoods,$request)
    {

        if ($request->input('refund_type') == RefundApply::REFUND_TYPE_EXCHANGE_GOODS) {
            return 0;
        }

        if (empty($applyRefundGoods)) {
            return 0;
        }

        //订单商品总数量
        $orderGoodsNum = $this->order->orderGoods->sum('total');

        //本次申请售后商品总数量
        $currentApplyNum = array_sum(array_column($applyRefundGoods, 'total'));


        //一次性申请全部商品退款
        if ($orderGoodsNum == $currentApplyNum) {
            return 3; //申请全额退款
        }

        //todo 问题：已经换过一次货的商品，是否需要过滤掉换货售后记录的退款数量？
        //订单已售后总数量，这里要不要过滤换货售后
        $refundedNum = RefundGoodsLog::where('order_id',$this->order->id)
            ->where('refund_type', '!=', RefundApply::REFUND_TYPE_EXCHANGE_GOODS)
            ->sum('refund_total');

        if ($orderGoodsNum == ($currentApplyNum + $refundedNum)) {
            return 2; //最后一次退款
        }

        return 1; //部分退款
    }

    //后端订单列表是否显示部分退款按钮
    public function orderListDisplayButton()
    {
        return $this->multipleRefund();
    }

    //订单所属类型
    abstract public function isBelongTo();

    /**
     * 订单售后次数限制
     * @return int|bool false不限制次数直到没有可退数量
     */
    abstract public function applyNumberLimit();

    /**
     * 是否支持部分退款
     * @return bool true 支持 false 不支持
     */
    abstract public function multipleRefund();



    //订单是否可申请售后验证
    abstract public function applyBeforeValidate();



    //本次可进行售后申请的商品数据处理
    public function canApplyRefundGoods()
    {
        //处理订单可退款商品数量
        $orderGoods = $this->order->orderGoods->map(function ($orderGoods) {
            $orderGoods->refundable_total = $orderGoods->total - $orderGoods->after_sales['refunded_total'];
            $orderGoods->unit_price = bankerRounding($orderGoods->payment_amount / $orderGoods->total);
            $goods_trade = GoodsTradeSet::where('goods_id', $orderGoods->goods_id)->first();
            if ($goods_trade && $goods_trade->hide_status) {
                $begin_hide_day = $goods_trade->begin_hide_day;
                if ($begin_hide_day > 1) {
                    $begin_hide_day -= 1;
                    $begin_time = $this->order->pay_time->addDays($begin_hide_day)->format('Y-m-d');
                } else {
                    $begin_time = $this->order->pay_time->format('Y-m-d');
                }
                $begin_time .= " {$goods_trade->begin_hide_time}:00";
                $begin_timestamp = strtotime($begin_time);
                $end_hide_day = $goods_trade->end_hide_day;
                if ($end_hide_day) {
                    $end_time = Carbon::createFromTimestamp($begin_timestamp)->addDays(1)->format('Y-m-d');
                } else {
                    $end_time = Carbon::createFromTimestamp($begin_timestamp)->format('Y-m-d');
                }
                $end_time .= " {$goods_trade->end_hide_time}:00";
                $end_timestamp = strtotime($end_time);
                if ($begin_timestamp < time() && $end_timestamp > time()) {
                    return false;
                }
            }
            return $orderGoods;
        })->filter()->values();

        return $orderGoods->toArray();
    }

    /**
     * 格式化订单数据
     * @return array
     */
    public function formatOrder()
    {
        return  $this->order->makeHidden(['orderGoods'])->toArray();
    }



    //订单的运费金额
    public function getOrderFreightPrice()
    {
        return $this->order->dispatch_price;
    }


    //订单其他费用退款
    public function getOrderOtherPrice()
    {
        //预约商品服务费不退
        if (!is_null(\app\common\modules\shop\ShopConfig::current()->get('store_reserve_refund_price')) && $this->order->status == Order::COMPLETE) {
            $class = array_get(\app\common\modules\shop\ShopConfig::current()->get('store_reserve_refund_price'), 'class');
            $function = array_get(\app\common\modules\shop\ShopConfig::current()->get('store_reserve_refund_price'), 'function');
            $plugin_res = $class::$function($this->order);
            if($plugin_res['res']) {
                return $this->order->fee_amount;
            }
        }

        return $this->order->fee_amount + $this->order->service_fee_amount;
    }


    /**
     * 订单已退款完成记录
     * @return \app\framework\Database\Eloquent\Collection
     */
    public function getAfterSales()
    {
        if (!isset($this->refundedCollect)) {
             $this->refundedCollect = \app\common\models\refund\RefundApply::getAfterSales($this->order->id)->get();
        }

        return  $this->refundedCollect;
    }
}