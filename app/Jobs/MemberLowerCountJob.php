<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/10/31
 * Time: 16:05
 */

namespace app\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use app\backend\modules\charts\modules\member\models\MemberLowerCount;
use Illuminate\Support\Facades\DB;

class MemberLowerCountJob
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public function __construct()
    {

    }

    public function handle()
    {
        $this->memberCount();
    }

    /**
     *
     */
    public function memberCount(){
        \Log::debug('下线人数排行定时任务开始执行,公众号id为', \YunShop::app()->uniacid);

        $list = DB::select('select count(*) as team_total, sum(if(level=1,1,0)) as first_total, sum(if(level = 2, 1, 0)) AS second_total, sum(if(level = 3, 1, 0)) AS third_total, member_id as uid, uniacid from '. DB::getTablePrefix() . 'yz_member_children where uniacid =' . \YunShop::app()->uniacid . ' group by member_id');

        if (empty($list)) {
            return;
        }
        $insert_data = array_chunk($list,2000);
        foreach ($insert_data as $k=>$v){
            MemberLowerCount::insert($v);
        }
        \Log::debug('下线人数排行定时任务执行结束,公众号id为', \YunShop::app()->uniacid);
    }
}