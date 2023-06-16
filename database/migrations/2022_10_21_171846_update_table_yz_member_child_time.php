<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use app\backend\modules\member\models\MemberRecord;
use app\common\models\MemberShopInfo;

class UpdateTableYzMemberChildTime extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('yz_member')) {
            try {
               $list=MemberShopInfo::where([['parent_id','<>',0],['child_time','=',0]])->pluck('member_id');
                $record=MemberRecord::whereIn('uid',$list)->where([['deleted_at','=',NULL],['status','=',1]])->selectRaw('uid,max(created_at) as max_time')->groupBy('uid')->get();
               $record->each(function($item){
                   MemberShopInfo::where('member_id',$item->uid)->update(['child_time'=>$item->max_time]);
               });
            } catch (\Exception $e) {
                \Log::debug('fill_error',$e->getMessage());
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
