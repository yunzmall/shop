<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/12/31
 * Time: 10:57
 */

namespace app\frontend\modules\payment\controllers;


use app\common\components\ApiController;
use app\common\exceptions\AppException;
use app\common\models\Order;

class PcScanController extends ApiController
{
    public function index()
    {
        // 验证
        $this->validate([
            'order_ids' => 'required'
        ]);

        $orderIds = request()->input('order_ids');

        if (!is_array($orderIds)) {
            $orderIds = explode(',', $orderIds);
        }
        array_walk($orderIds, function ($orderId) {
            if (!is_numeric($orderId)) {
                throw new AppException('(ID:' . $orderId . ')订单号id必须为数字');
            }
        });

        $orders = Order::select(['status', 'id', 'order_sn', 'price', 'uid', 'plugin_id'])
            ->whereIn('id', $orderIds)
            ->get();


        if ($orders->where('status','>', '0')->count() > 0) {
            $data['pay_status'] = 1;
        } else {
            $data['pay_status'] = 0;
        }


        $trade = \Setting::get('shop.trade');
        $data['redirect'] = '';

        if (!is_null($trade) && isset($trade['redirect_url']) && !empty($trade['redirect_url'])) {
            $data['redirect'] = $trade['redirect_url'];
        }


        return $this->successJson('pay',$data);
    }
}