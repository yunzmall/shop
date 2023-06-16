<?php
/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 2022/1/12
 * Time: 16:29
 */

namespace app\outside\modules\order\models;


use app\common\models\order\OrderMergeCreate;
use app\common\modules\memberCart\MemberCartCollection;
use app\frontend\modules\order\models\PreOrder;
use Illuminate\Support\Facades\DB;

class BuyTrade extends \app\common\modules\trade\models\Trade
{

    public function generate()
    {
        DB::transaction(function () {
            $this->orders->map(function (PreOrder $order) {
                /**
                 * @var $order
                 */
                $order->generate();
                $order->fireCreatedEvent();
            });
            OrderMergeCreate::saveData($this->orders->pluck('id')->implode(','));

            //设置订单与第三方请求下单关系
            $preOutsideOrder = new PreOutsideOrder();
            $preOutsideOrder->setOrders($this->orders);
            $preOutsideOrder->store();
            $this->setRelation('outsideOrder', $preOutsideOrder);
            return $this->orders;
        });
    }
}