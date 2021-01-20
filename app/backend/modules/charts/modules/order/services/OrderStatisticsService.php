<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/1
 * Time: 19:00
 */

namespace app\backend\modules\charts\modules\order\services;


use app\backend\modules\charts\models\OrderStatistics;
use app\common\models\Order;
use app\common\models\UniAccount;
use Illuminate\Support\Facades\DB;

class OrderStatisticsService
{
    public function orderStatistics()
    {
        $uniAccount = UniAccount::getEnable();
        foreach ($uniAccount as $u) {
            \YunShop::app()->uniacid = $u->uniacid;
            \Setting::$uniqueAccountId = $u->uniacid;

            //定义空数组
            $order_alls = [];
            $order_pays = [];
            $order_completes = [];

            //全部
            $order_all = DB::table('yz_order')->select('uid','uniacid', DB::raw('count(1) as total_quantity', 'sum(price) as total_amount'), DB::raw('sum(price) as total_amount'))
                ->where('uniacid', \YunShop::app()->uniacid)
                ->groupBy('uid')
                ->orderBy('uid')
                ->get();

            //处理数组 uid作为键
            foreach ($order_all as $item1) {
                $order_alls[$item1['uid']] = $item1;
            }

            //已支付
            $order_pay = DB::table('yz_order')->select('uid','uniacid', DB::raw('count(1) as total_pay_quantity'), DB::raw('sum(price) as total_pay_amount'))
                ->where('uniacid', \YunShop::app()->uniacid)
                ->whereIn('status', [1,2,3])
                ->groupBy('uid')
                ->orderBy('uid')
                ->get();

            //处理数组 uid作为键
            foreach ($order_pay as $item2) {
                $order_pays[$item2['uid']] = $item2;
            }

            //已完成
            $order_complete = DB::table('yz_order')->select('uid','uniacid', DB::raw('count(1) as total_complete_quantity'), DB::raw('sum(price) as total_complete_amount'))
                ->where('status', 3)
                ->where('uniacid', \YunShop::app()->uniacid)
                ->groupBy('uid')
                ->orderBy('uid')
                ->get();

            //处理数组 uid作为键
            foreach ($order_complete as $item3) {
                $order_completes[$item3['uid']] = $item3;
            }

            $result = [];
            //处理数组 取相同键里有值的
            foreach ($order_alls as $key => $value) {
                $result[$key] = $value;
                if ($order_pays[$key]) {
                    $result[$key]['total_pay_quantity'] = $order_pays[$key]['total_pay_quantity'] ?: 0;
                    $result[$key]['total_pay_amount'] = $order_pays[$key]['total_pay_amount'] ?: 0;
                } else {
                    $result[$key]['total_pay_quantity'] = 0;
                    $result[$key]['total_pay_amount'] = 0;
                }

                if ($order_completes[$key]) {
                    $result[$key]['total_complete_quantity'] = $order_completes[$key]['total_complete_quantity'] ?: 0;
                    $result[$key]['total_complete_amount'] = $order_completes[$key]['total_complete_amount'] ?: 0;
                } else {
                    $result[$key]['total_complete_quantity'] = 0;
                    $result[$key]['total_complete_amount'] = 0;
                }
            }

            OrderStatistics::uniacid()->delete();

            $results = collect($result)->chunk(5000);

            foreach ($results as $result) {
                OrderStatistics::insert($result->toArray());
            }
        }
    }
}