<?php
/**
 * Created by PhpStorm.
 * User: shenyang
 * Date: 2018/10/12
 * Time: 下午4:51
 */

namespace app\backend\modules\order\controllers;


use app\backend\modules\order\models\Order;
use app\common\components\BaseController;

class JobController extends BaseController
{
    public function index(){
        \YunShop::app()->uniacid = null;
        $order = Order::find(request('id'));
        if($order->orderCreatedJob) {
            dump('下单',$order->orderCreatedJob->toArray());
        }
        if($order->orderPaidJob){
            dump('支付',$order->orderPaidJob->toArray());
        }
        if($order->orderSentJob) {
            dump('发货',$order->orderSentJob->toArray());
        }
        if($order->orderReceivedJob) {
            dump('完成',$order->orderReceivedJob->toArray());
        }
    }
}