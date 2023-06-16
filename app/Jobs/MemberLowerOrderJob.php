<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/10/31
 * Time: 14:55
 */

namespace app\Jobs;


use app\backend\modules\charts\modules\member\models\MemberLowerOrder;
use app\backend\modules\charts\modules\member\services\LowerOrderService;
use app\common\models\MemberShopInfo;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;


class MemberLowerOrderJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public $uniacid;
    public $insert_data;
    public function __construct()
    {
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->memberOrder();
    }

    public function memberOrder()
    {
        $orders = [];
        $order_data = DB::select('select uid, SUM(price) as amount, COUNT(id) as count from '. DB::getTablePrefix() . 'yz_order where status = 3 and uniacid =' . \YunShop::app()->uniacid .' group by uid');
        foreach ($order_data as $order) {
            $orders[$order['uid']] = $order;
        }
        unset($order_data);
        $members = DB::select('select member_id, parent_id from '. DB::getTablePrefix() . 'yz_member where uniacid =' . \YunShop::app()->uniacid );
        $tree = $this->tree($members);
        unset($members);
        $this->getTreeData($tree[0]['son'],$orders);
        unset($tree[0]);
        $this->getFirst($tree,$orders);

        $insert_data = array_chunk($this->insert_data,2000);
        foreach ($insert_data as $k=>$v){
            MemberLowerOrder::insert($v);
        }
    }

    public function tree($members)
    {
        $items = [];
        foreach ($members as $member) {
            $items[$member['member_id']] = $member;
        }
        foreach ($items as $item) {
            $items[$item['parent_id']]['son'][$item['member_id']] = &$items[$item['member_id']];
        }
        return $items;
    }

    function getTreeData($tree, $orders)
    {
        $data = [];
        foreach($tree as $kk => $t){
            $this_insert_data = [
                'uid'=> $t['member_id'],
                'uniacid'=>\YunShop::app()->uniacid,
                'first_order_quantity'=>0,
                'first_order_amount'=>0,
                'second_order_quantity'=>0,
                'second_order_amount'=>0,
                'third_order_quantity'=>0,
                'third_order_amount'=>0,
                'team_order_quantity'=>0,
                'team_order_amount'=>0,
                'pay_count'=>0,
                'created_at'=>time()
            ];
            $this_order_data = [];
            if(isset($t['son'])){
                $this_order_data = $this->getTreeData($t['son'], $orders);
                $this_insert_data['team_order_quantity'] = $this_order_data['count'];
                $this_insert_data['team_order_amount'] = $this_order_data['amount'];
            }
            $data['count'] += $orders[$t['member_id']]['count'] + $this_order_data['count'];
            $data['amount'] += $orders[$t['member_id']]['amount'] + $this_order_data['amount'];

            $this->insert_data[$kk] = $this_insert_data;
        }
        return $data;
    }

    function getFirst($tree, $orders)
    {
        foreach($tree as $kk => $t){
            if(empty($t['son'])) {
                continue;
            }
            if (empty($this->insert_data[$kk])) {
                if (empty($t['member_id'])) {
                    //todo 有出现某会员在yz_member找不到（被删了），但有其他会员的parent_id还是该会员的id，导致下面uid为null，插入数据失败
                    continue;
                }
                $this->insert_data[$kk] = [
                    'uid'=> $t['member_id'],
                    'uniacid'=>\YunShop::app()->uniacid,
                    'first_order_quantity'=>0,
                    'first_order_amount'=>0,
                    'second_order_quantity'=>0,
                    'second_order_amount'=>0,
                    'third_order_quantity'=>0,
                    'third_order_amount'=>0,
                    'team_order_quantity'=>0,
                    'team_order_amount'=>0,
                    'pay_count'=>0,
                    'created_at'=>time()
                ];
            }
            foreach ($t['son'] as $first) {
                $this->insert_data[$kk]['first_order_quantity'] += $orders[$first['member_id']]['count'];
                $this->insert_data[$kk]['first_order_amount'] += $orders[$first['member_id']]['amount'];

                if(!isset($first['son'])) {
                    continue;
                }
                foreach ($first['son'] as $second) {
                    $this->insert_data[$kk]['second_order_quantity'] += $orders[$second['member_id']]['count'];
                    $this->insert_data[$kk]['second_order_amount'] += $orders[$second['member_id']]['amount'];

                    if(!isset($second['son'])) {
                        continue;
                    }
                    foreach ($second['son'] as $third) {
                        $this->insert_data[$kk]['third_order_quantity'] += $orders[$third['member_id']]['count'];
                        $this->insert_data[$kk]['third_order_amount'] += $orders[$third['member_id']]['amount'];
                    }

                }

            }

        }
    }





    public function memberOrder1(){
        $time = time();
        \Log::debug('下线订单排行定时任务开始执行,公众号id为', \YunShop::app()->uniacid);

        $insert_data = [];
        $order_data = DB::table('yz_order')
            ->selectRaw('uid, SUM(price) as amount, COUNT(id) as count')
            ->where('status', 3)
            ->where('uniacid', \YunShop::app()->uniacid)
            ->groupBy('uid')
            ->get();


        $members = DB::select('select member_id from '. DB::getTablePrefix() . 'yz_member where uniacid =' . \YunShop::app()->uniacid );

        $child = DB::table('yz_member_children')
            ->select(['child_id','member_id', 'level'])
            ->where('uniacid', \YunShop::app()->uniacid)
            ->get();
        foreach ($members as $key => $member) {
            $this_insert_data = [
                'uid'=>$member['member_id'],
                'uniacid'=>\YunShop::app()->uniacid,
                'created_at'=>$time,
                'first_order_quantity'=>0,
                'first_order_amount'=>0,
                'second_order_quantity'=>0,
                'second_order_amount'=>0,
                'third_order_quantity'=>0,
                'third_order_amount'=>0,
                'team_order_quantity'=>0,
                'team_order_amount'=>0,
                'pay_count'=>0,
            ];
            $total_member = $child->where('member_id', $member['member_id'])->pluck('child_id');
            if (!$total_member->isEmpty()) {
                $this_insert_data['team_order_quantity'] = $order_data->whereIn('uid', $total_member)->sum('count');
                $this_insert_data['team_order_amount'] = $order_data->whereIn('uid', $total_member)->sum('amount');

                $first_member = $child->where('level',1)->where('member_id', $member['member_id'])->pluck('child_id');
                if (!$first_member->isEmpty()) {
                    $this_insert_data['first_order_quantity'] = $order_data->whereIn('uid', $first_member)->sum('count');
                    $this_insert_data['first_order_amount'] = $order_data->whereIn('uid', $first_member)->sum('amount');
                }

                $second_member = $child->where('level',2)->where('member_id', $member['member_id'])->pluck('child_id');
                if (!$second_member->isEmpty()) {
                    $this_insert_data['second_order_quantity'] = $order_data->whereIn('uid', $second_member)->sum('count');
                    $this_insert_data['second_order_amount'] = $order_data->whereIn('uid', $second_member)->sum('amount');
                }

                $third_member = $child->where('level',3)->where('member_id', $member['member_id'])->pluck('child_id');
                if (!$third_member->isEmpty()) {
                    $this_insert_data['third_order_quantity'] = $order_data->whereIn('uid', $third_member)->sum('count');
                    $this_insert_data['third_order_amount'] = $order_data->whereIn('uid', $third_member)->sum('amount');
                }
            }

            $insert_data[$key] = $this_insert_data;
            unset($this_insert_data);
        }
        $insert_data = array_chunk($insert_data,2000);
        foreach ($insert_data as $k=>$v){
            MemberLowerOrder::insert($v);
        }

        \Log::debug('下线订单排行定时任务执行结束,公众号id为', \YunShop::app()->uniacid);

    }

    public function memberOrder2(){
        $time = time();
        $insert_data = [];
        $order_data = DB::select('select uid, SUM(price) as amount, COUNT(id) as count from '. DB::getTablePrefix() . 'yz_order where status = 3 and uniacid =' . \YunShop::app()->uniacid .' group by uid');
        $order_data = array_column($order_data, null, 'uid');

        $members = DB::select('select member_id from '. DB::getTablePrefix() . 'yz_member where uniacid =' . \YunShop::app()->uniacid );

        foreach ($members as $v){
            $this_insert_data = [
                'uid'=>$v['member_id'],
                'uniacid'=>\YunShop::app()->uniacid,
                'first_order_quantity'=>0,
                'first_order_amount'=>0,
                'second_order_quantity'=>0,
                'second_order_amount'=>0,
                'third_order_quantity'=>0,
                'third_order_amount'=>0,
                'team_order_quantity'=>0,
                'team_order_amount'=>0,
                'pay_count'=>0,
                'created_at'=>$time
            ];

            $child = DB::table('yz_member_children')
                ->select(['child_id','level'])
                ->where('member_id',$v['member_id'])
                ->where('uniacid', \YunShop::app()->uniacid)
                ->get()
                ->toArray();
            if (!empty($child)) {
                $this_child = array_column($child, 'level', 'child_id');
                foreach ($this_child as $kk => $vv){
                    if (!isset($order_data[$kk])) continue;
                    $this_order_data = $order_data[$kk];
                    $this_insert_data['pay_count']++;
                    $this_insert_data['team_order_quantity'] += $this_order_data['count'];
                    $this_insert_data['team_order_amount'] += $this_order_data['amount'];
                    if ($vv > 3 ) continue;
                    switch ($vv){
                        case 1:
                            $this_insert_data['first_order_quantity'] += $this_order_data['count'];
                            $this_insert_data['first_order_amount'] += $this_order_data['amount'];
                            break;
                        case 2:
                            $this_insert_data['second_order_quantity'] += $this_order_data['count'];
                            $this_insert_data['second_order_amount'] += $this_order_data['amount'];
                            break;
                        case 3:
                            $this_insert_data['third_order_quantity'] += $this_order_data['count'];
                            $this_insert_data['third_order_amount'] += $this_order_data['amount'];
                    }
                }
            }

            $insert_data[] = $this_insert_data;
        }

        $insert_data = array_chunk($insert_data,2000);
        foreach ($insert_data as $k=>$v){
            MemberLowerOrder::insert($v);
        }
    }
}