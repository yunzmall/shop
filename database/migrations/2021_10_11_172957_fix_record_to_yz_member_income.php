<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class FixRecordToYzMemberIncome extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        if (Schema::hasTable('yz_member_income')) {
            DB::table('yz_member_income')->where('type_name', '直播授权码')->update(['dividend_code' => 127, 'incometable_type' => 'Yunshop\Room\models\CodeUsed']);
            DB::table('yz_member_income')->where('type_name', '抢团')->update(['dividend_code' => 86, 'incometable_type' => 'Yunshop\SnatchRegiment\widgets\WithdrawWidget']);
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
