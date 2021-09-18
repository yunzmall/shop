<?php
/**
 * Created by PhpStorm.
 * User: yunzhong
 * Date: 2020/7/13
 * Time: 17:29
 */

namespace app\frontend\modules\order\controllers;

use app\common\components\ApiController;
use app\common\events\order\AfterOrderExpeditingDeliveryEvent;
use app\common\models\ExpeditingDelivery;

class OrderExpeditingDeliveryController extends ApiController
{
    public function index()
    {
        $order_id = \YunShop::request()->get('order_id');
        $order_sn = \YunShop::request()->get('order_sn');

        $order = ExpeditingDelivery::uniacid()->where("order_id",$order_id)->first();

        if ($order) {
            return $this->errorJson("已经催发货，不用重复催发货");
        }
        
        $data = [
            'uniacid' => \YunShop::app()->uniacid,
            'order_id' => $order_id,
            'order_sn' => $order_sn,
            'created_at' => time(),
            'updated_at' => time()
        ];

        $order_expediting = new ExpeditingDelivery();

        $order_expediting->fill($data);

        if ($order_expediting->save()) {
            event((new AfterOrderExpeditingDeliveryEvent($order_id)));
            return $this->successJson("催发货成功");
        }

        return $this->errorJson("催发货失败");
    }
}