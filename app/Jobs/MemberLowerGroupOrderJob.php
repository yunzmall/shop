<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/10/31
 * Time: 14:55
 */

namespace app\Jobs;

use app\backend\modules\charts\modules\member\models\MemberLowerGroupOrder;
use app\common\models\MemberShopInfo;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Yunshop\ShareChain\common\model\ShopMemberLevel;

class MemberLowerGroupOrderJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public $uniacid;
    public $insert_data;
    public function __construct()
    {
    }

    public function handle()
    {
        $this->memberOrder();
    }

    public function memberOrder()
    {
        $orders = [];
        $order_data = DB::select('select uid, SUM(price) as amount, SUM(goods_total) as goods_count from '. DB::getTablePrefix() . 'yz_order where status > 0 and uniacid =' . \YunShop::app()->uniacid .' group by uid');
        foreach ($order_data as $order) {
            $orders[$order['uid']] = $order;
        }
        unset($order_data);
        $members = DB::select('select member_id, parent_id from '. DB::getTablePrefix() . 'yz_member where uniacid =' . \YunShop::app()->uniacid );
        $tree = $this->tree($members);
        unset($members);
        $this->getTreeData($tree,$orders);
        unset($tree[0]);

        $insert_data = array_chunk($this->insert_data,2000);
        foreach ($insert_data as $k=>$v){
            MemberLowerGroupOrder::insert($v);
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
        return isset($items[0]['son']) ? $items[0]['son'] : [];
    }

    function getTreeData($tree, $orders)
    {
        $data = [];
        foreach($tree as $kk => $t){
            $this_insert_data = [
                'uid'=>$t['member_id'],
                'uniacid'=>\YunShop::app()->uniacid,
                'amount'=>0,
                'goods_count'=>0,
                'pay_count'=>0,
                'team_count'=>0,
                'created_at'=>time()
            ];
            $this_order_data = [];
            if(isset($t['son'])){
                $this_order_data = $this->getTreeData($t['son'], $orders);
                $this_insert_data['amount'] = $this_order_data['amount'];
                $this_insert_data['goods_count'] = $this_order_data['goods_count'];
                $this_insert_data['pay_count'] = $this_order_data['pay_count'];
                $this_insert_data['team_count'] = $this_order_data['team_count'];
            }
            $data['amount'] += $orders[$t['member_id']]['amount'] + $this_order_data['amount'];
            $data['goods_count'] += $orders[$t['member_id']]['goods_count'] + $this_order_data['goods_count'];
            $pay_count = isset($orders[$t['member_id']]) ? 1 : 0;
            $data['pay_count'] += $this_order_data['pay_count'] + $pay_count;
            $data['team_count'] += $this_order_data['team_count'] + 1;


            $this->insert_data[$kk] = $this_insert_data;
        }
        return $data;
    }




    /**
     * Execute the job.
     *
     * @return void
     */
    public function memberOrder2(){

        $time = time();
        \Log::debug('团队支付订单排行定时任务开始执行,公众号id为', \YunShop::app()->uniacid);
        $t1 = microtime(true);
        $insert_data = [];
        //查询归类所有订单开始
        $order_data = DB::table('yz_order')
            ->selectRaw('uid, SUM(price) as amount, COUNT(id) as count, SUM(goods_total) as goods_count')
            ->where('status','>',0)
            ->where('uniacid', \YunShop::app()->uniacid)
            ->groupBy('uid')
            ->get();

        $members = DB::select('select member_id from '. DB::getTablePrefix() . 'yz_member where uniacid =' . \YunShop::app()->uniacid );

        
        $child = DB::table('yz_member_children')
            ->select(['child_id','member_id'])
            ->where('uniacid', \YunShop::app()->uniacid)
            ->get();

        foreach ($members as $member){
            $memberID = $member['member_id'];
            $this_insert_data = [
                'uid' => $member['member_id'],
                'uniacid'=>\YunShop::app()->uniacid,
                'amount'=>0,
                'goods_count'=>0,
                'pay_count'=>0,
                'team_count'=>$child->where('member_id', $member['member_id'])->count(),
//                'team_count'=>$child->filter(function($user) use ($memberID) {
//                    return $user['member_id'] === $memberID;
//                })->count(),
                'created_at'=>$time
            ];

//            $total_member = DB::table('yz_member_children')->where('member_id', $member['member_id'])->pluck('child_id');
            $total_member = $child->where('member_id', $member['member_id'])->pluck('child_id');
//            $total_member = $child->filter(function($user) use ($memberID) {
//                return $user['member_id'] === $memberID;
//            })->pluck('child_id');
//            dd($total_member, $total_member2);
            if (!$total_member->isEmpty()) {
                $child_member = $order_data->whereIn('uid', $total_member);
//                $child_member = $order_data->filter(function($user) use ($total_member) {
//                    return in_array($user['uid'],$total_member->toArray());
//                });
                $this_insert_data['pay_count'] = $child_member->sum('count');
                $this_insert_data['amount'] = $child_member->sum('amount');
                $this_insert_data['goods_count'] = $child_member->sum('goods_count');
            }

            $insert_data[] = $this_insert_data;
            unset($total_member);
            unset($child_member);
            unset($this_insert_data);
        }

        $insert_data = array_chunk($insert_data,2000);
        foreach ($insert_data as $k=>$v){
            MemberLowerGroupOrder::insert($v);
        }

        \Log::debug('团队支付订单排行定时任务执行结束,公众号id为', \YunShop::app()->uniacid);

    }

    public function memberOrder1(){
        $time = time();
        $insert_data = [];
        $order_data = DB::select('select uid, SUM(price) as amount, COUNT(id) as count ,SUM(goods_total) as goods_count from '. DB::getTablePrefix() . 'yz_order where status > 0 and uniacid =' . \YunShop::app()->uniacid .' group by uid');
        $order_data = array_column($order_data, null, 'uid');
        $members = DB::select('select member_id from '. DB::getTablePrefix() . 'yz_member where uniacid =' . \YunShop::app()->uniacid );

        foreach ($members as $v){
            $child = DB::select('select child_id, level from '. DB::getTablePrefix() . 'yz_member_children where member_id = '.$v['member_id'].' and uniacid =' . \YunShop::app()->uniacid );
            $this_insert_data = [
                'uid'=>$v['member_id'],
                'uniacid'=>\YunShop::app()->uniacid,
                'amount'=>0,
                'goods_count'=>0,
                'pay_count'=>0,
                'team_count'=>count($child),
                'created_at'=>$time
            ];

            if (!empty($child)) {
                $this_child = array_column($child, 'level', 'child_id');
                foreach ($this_child as $kk=>$vv){
                    if (!isset($order_data[$kk])) continue;
                    $this_order_data = $order_data[$kk];
                    $this_insert_data['pay_count']++;
                    $this_insert_data['goods_count'] += $this_order_data['goods_count'];
                    $this_insert_data['amount'] += $this_order_data['amount'];
                }
            }

            $insert_data[] = $this_insert_data;
        }

        $insert_data = array_chunk($insert_data,2000);
        foreach ($insert_data as $k=>$v){
            MemberLowerGroupOrder::insert($v);
        }
    }
}