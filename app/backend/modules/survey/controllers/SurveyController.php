<?php
/**
 * Created by PhpStorm.
 * User: yunzhong
 * Date: 2019/7/1
 * Time: 14:55
 */

namespace app\backend\modules\survey\controllers;


use app\backend\modules\member\models\MemberShopInfo;
use app\backend\modules\menu\Menu;
use app\common\components\BaseController;
use app\common\models\Goods;
use app\common\models\Member;
use app\common\models\Order;
use app\common\services\System;
use app\host\HostManager;
use Carbon\Carbon;

use Illuminate\Support\Facades\DB;
use mysql_xdevapi\Exception;
use Predis\Connection\ConnectionException;
use Yunshop\Love\Common\Models\MemberShop;


class SurveyController extends BaseController
{
    public function index()
    {
        return view('survey.index', ['data' => json_encode($this->_allData())]);
    }

    private function _allData()
    {
        $menu = Menu::current()->getPluginMenus();

        //常用功能
        $plugins = [];
        foreach ($menu as $key => $itme) {
            if (isset($itme['menu']) && $itme['menu'] == 1 && can($key) && ($itme['top_show'] == 1 || app('plugins')->isTopShow($key))) {
                $plugins[$key] = $itme;
                if (!file_exists(base_path('static/yunshop/plugins/list-icon/plugins_img/' . $itme['list_icon'] . '.png'))) {
                    $plugins[$key]['icon_url'] = static_url("yunshop/plugins/list-icon/img/default.png");
                } else {
                    $plugins[$key]['icon_url'] = static_url("yunshop/plugins/list-icon/plugins_img/{$itme['list_icon']}.png");
                }
                $plugins[$key]['url'] = yzWebFullUrl($itme['url']);
            }
        }

        //销售量前三条数据
        $goods = Goods::uniacid()->orderBy('real_sales', 'desc')->offset(0)
            ->take(3)->select('id', 'title', 'real_sales', 'created_at')->get();

        //订单数据
        $start_today = strtotime(Carbon::now()->startOfDay()->format('Y-m-d H:i:s'));
        $end_today = strtotime(Carbon::now()->endOfDay()->format('Y-m-d H:i:s'));

        //待支付订单
        $to_be_paid = Order::uniacid()->getQuery()->selectRaw('count(*) as to_be_paid ')->where('status', 0)->first();
        //待发货订单
        $to_be_shipped = Order::uniacid()->getQuery()->selectRaw('count(*) as to_be_shipped ')->where('status', 1)->first();
        //今日订单数据

        $today_order = Order::uniacid()->getQuery()->selectRaw('sum(price) as money , count(id) as total')->whereBetween('created_at', [$start_today, $end_today])->whereIn('status', [1, 2, 3])->first();

        //会员总数
        $member = DB::table('mc_members')
            ->select([DB::raw('count(1) as total')])
            ->where('mc_members.uniacid', \YunShop::app()->uniacid)
            ->leftJoin('yz_member_del_log', 'mc_members.uid', '=', 'yz_member_del_log.member_id')
            ->whereNull('yz_member_del_log.member_id')
            ->join('yz_member', 'mc_members.uid', '=', 'yz_member.member_id')
            ->whereNull('yz_member.deleted_at')
            ->first();

        //=============获取图表数据

        //dd($this->getRedisStatus());
        //队列运行情况
        $current_time = time();
        $queue_hearteat = [
            'daemon' => $this->daemonStatus(),
            'cron' => \app\backend\modules\survey\models\CronHeartbeat::getLog($current_time),
            'job' => \app\backend\modules\survey\models\JobHeartbeat::getLog($current_time),
            'redis' => $this->getRedisStatus()
        ];

        $all_data = [
            'queue_hearteat' => $queue_hearteat,
            'plugins' => $plugins,
            'goods' => $goods,
            'member_count' => $member['total'],
            'chart_data' => $this->getOrderData(),
            'system' => $this->getSystemStatus(),
            'order' => [
                'to_be_paid' => $to_be_paid['to_be_paid'] ?: 0,
                'to_be_shipped' => $to_be_shipped['to_be_shipped'] ?: 0,
                'today_order_money' => $today_order['money'] ?: 0,
                'today_order_count' => $today_order['total'] ?: 0
            ]
        ];
        return $all_data;
    }

    public function survey()
    {
        return $this->successJson('成功', $this->_allData());
    }

    private function getOrderData()
    {
        $times = $this->timeRangeItems();
        $result = [];
        foreach ($times as $time) {
            $item['total'] = $this->orderTotals(null, 'create_time', $time) ?: 0;
            $item['complete'] = $this->orderTotals(3, 'finish_time', $time) ?: 0;
            $item['deliver_goods'] = $this->orderTotals(2, 'send_time', $time) ?: 0;
            $item['date'] = $time;
            $result[] = $item;
        }
        return $result;
    }

    /**
     * 获取一星期的时间
     * @return array
     */
    public function timeRangeItems()
    {
        $result = [];
        for ($i = 6; $i > -1; $i--) {
            Carbon::now()->subDay($i)->format('Y-m-d');
            $result[] = Carbon::now()->subDay($i)->format('Y-m-d');
        }
        return $result;
    }

    private $orderTotals;

    private function orderTotals($status, $timeField, $date)
    {
        if (!isset($this->orderTotals[$timeField])) {
            $allDate = Order::uniacid()->getQuery()
                ->select(DB::raw("count(1) as total, FROM_UNIXTIME(" . $timeField . ",'%Y-%m-%d') as date_str"))
                ->whereBetween($timeField, [Carbon::now()->subDay(6)->startOfDay()->timestamp, Carbon::now()->endOfDay()->timestamp])
                ->groupBy(DB::raw('YEAR(date_str), MONTH(date_str), DAY(date_str)'));
            if (isset($status)) {
                $allDate->where('status', $status);
            }
            $allDate = $allDate->get();
            $this->orderTotals[$timeField] = [];
            foreach ($allDate as $item) {
                $this->orderTotals[$timeField][$item['date_str']] = $item['total'];
            }
        }
        return $this->orderTotals[$timeField][$date];
    }

    /**
     * @return array
     *   uninstall redis未安装
     *   unexecute redis未执行
     */
    private function getRedisStatus()
    {
        try {
            if (!class_exists('Predis\Client')) {
                return array('queue_status' => 'uninstall', 'msg' => '未安装');
            }

            $res = \Illuminate\Support\Facades\Redis::ping() == 'PONG';

            if ($res) {
                return array('queue_status' => 'green', 'msg' => '正常');
            }
        } catch (ConnectionException $exception) {
            return array('queue_status' => 'unconnection', 'msg' => '连接失败');
        } catch (\Exception $exception) {
            return array('queue_status' => 'unexecute', 'msg' => '无法使用');
        }

    }

    private function daemonStatus()
    {
        $all_status = app('supervisor')->getState();
        $queue_status = 'green';
        $msg = '正常';
        $title = '';
        if(!function_exists('stream_socket_server')){
            return array('queue_status' => 'yellow', 'msg' => '请解禁stream_socket_server函数');
        }
        foreach ($all_status as $hostname=>$status) {
            $code = '正常';
            if ($status->val['statecode'] != 1) {
                $queue_status = 'not_running';
                $msg = $code = '异常';
            }
            $title .= '服务器'.$hostname."：$code\r\n";
        }
        if (count($all_status) == 1) {
            $title = $msg;
        }
        return array('queue_status' => $queue_status, 'msg' => $msg,'title'=>$title);
    }

    private function getSystemStatus()
    {
        return (new System())->index();
    }
}