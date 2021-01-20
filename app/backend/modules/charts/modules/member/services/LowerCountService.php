<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/10/31
 * Time: 11:33
 */

namespace app\backend\modules\charts\modules\member\services;


use app\backend\modules\charts\modules\member\models\MemberLowerCount;
use app\common\models\UniAccount;
use app\Jobs\MemberLowerJob;
use app\Jobs\MemberLowerOrderJob;
use Illuminate\Support\Facades\DB;

class LowerCountService
{

    public function memberCount(){
        $uniAccount = UniAccount::getEnable();
        foreach ($uniAccount as $u) {
            $time = time();
            \YunShop::app()->uniacid = $u->uniacid;
            \Setting::$uniqueAccountId = $u->uniacid;
            \Log::debug('下线人数排行定时任务开始执行,公众号id为', \YunShop::app()->uniacid);

            $insert_data = [];

            //查询关系链数据开始
//            $isset_uid = member::uniacid()->pluck('uid')->toArray();
            $child = DB::table('yz_member_children')->where('uniacid',\YunShop::app()->uniacid)->groupBy('member_id')
                ->select(DB::raw('GROUP_CONCAT(child_id) AS child_id_str'),DB::raw('GROUP_CONCAT(level) AS level_str'),'member_id')->get()->toArray();
            //查询关系链数据结束

            foreach ($child as $v) {
                $this_all = explode(',', $v['child_id_str']);
                $this_child = array_combine($this_all, explode(',', $v['level_str']));
                $this_insert_data = [
                    'uid'=>$v['member_id'],
                    'uniacid'=>\YunShop::app()->uniacid,
                    'first_total'=>0,
                    'second_total'=>0,
                    'third_total'=>0,
                    'team_total'=>count($this_child),
                    'created_at'=>$time
                ];
//                $level_count = array_count_values($this_child);
//                $this_insert_data['first_total'] = isset($level_count[1]) ? $level_count[1] : 0;
//                $this_insert_data['second_total'] = isset($level_count[2]) ? $level_count[2] : 0;
//                $this_insert_data['third_total'] = isset($level_count[3]) ? $level_count[3] : 0;
                foreach ($this_child as $kk=>$vv){
                    if ($vv > 3 ) continue;
                    switch ($vv){
                        case 1:
                            $this_insert_data['first_total']++;
                            break;
                        case 2:
                            $this_insert_data['second_total']++;
                            break;
                        case 3:
                            $this_insert_data['third_total']++;
                    }
                }
                $insert_data[] = $this_insert_data;
            }

            MemberLowerCount::uniacid()->delete();
            $insert_data = array_chunk($insert_data,2000);
            foreach ($insert_data as $k=>$v){
                MemberLowerCount::insert($v);
            }

            \Log::debug('下线人数排行定时任务执行结束,公众号id为', \YunShop::app()->uniacid);
        }
    }





//  //旧代码，已优化取代
//    public function memberCount()
//    {
//        $uniAccount = UniAccount::getEnable();
//        foreach ($uniAccount as $u) {
//            \YunShop::app()->uniacid = $u->uniacid;
//            \Setting::$uniqueAccountId = $u->uniacid;
//            $result = [];
//
//            $uniacid = \YunShop::app()->uniacid;
//            $level_member = DB::table('yz_member_children')->select('member_id', 'level', DB::raw('count(1) as total'))->where('uniacid', $uniacid)->whereIn('level', [1,2,3])->groupBy('member_id', 'level')->get();
//            $level_all_member = DB::table('yz_member_children')->select('member_id', DB::raw('count(1) as total'))->where('uniacid', $uniacid)->groupBy('member_id')->get();
//
//            foreach ($level_member as $val) {
//                if (!isset($result[$val['member_id']])) {
//                    $result[$val['member_id']] = [
//                        'uid' => $val['member_id'],
//                        'uniacid' => $uniacid,
//                        'first_total' => $val['total'],
//                        'second_total' => 0,
//                        'third_total' => 0,
//                    ];
//                } else {
//                    switch ($val['level']) {
//                        case 2:
//                            $result[$val['member_id']]['second_total'] = $val['total'];
//                            break;
//                        case 3:
//                            $result[$val['member_id']]['third_total'] = $val['total'];
//                            break;
//                    }
//                }
//            }
//
//            foreach ($level_all_member as $val) {
//                $result[$val['member_id']]['uid'] = $val['member_id'];
//                $result[$val['member_id']]['team_total'] = $val['total'];
//            }
//
//            MemberLowerCount::uniacid()->delete();
//            MemberLowerCount::insert($result);
//
////            $memberModel = new MemberLowerCount();
////            foreach ($result as $item) {
////                $memberModel->updateOrCreate(['uid' => $item['uid']], $item);
////            }
//        }
//    }
}