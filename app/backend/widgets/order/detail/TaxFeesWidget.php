<?php
/**
 * Created by PhpStorm.
 * 
 *
 *
 * Date: 2022/10/12
 * Time: 14:47
 */

namespace app\backend\widgets\order\detail;

use app\common\components\Widget;
use app\common\models\Order;
use app\common\models\order\OrderTaxFee;

class TaxFeesWidget  extends Widget
{
    public function run()
    {

        $order = Order::select('id')->where('plugin_id',92)->find($this->order_id);

        if (!$order) {
            return false;
        }

        $taxFees = OrderTaxFee::uniacid()
            ->where('order_id', $this->order_id)
            ->get();

//        if (!$taxFees->isNotEmpty()) {
//            return;
//        }

        return view('order.detail-widgets.tax-fees', [
            'orderTaxFees' => $taxFees->toArray(),
        ])->render();
    }
}