<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class FixDataToOrderIncomeCountTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
		$data = \Illuminate\Support\Facades\DB::table('yz_order_income_count')->where('day_time',0)->get();
   		foreach ($data as $key=>$value) {
			$day_time = strtotime(date('Ymd',$value['created_at']));
			\Illuminate\Support\Facades\DB::table('yz_order_income_count')->where('id',$value['id'])->update(['day_time'=>$day_time]);
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
