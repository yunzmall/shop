<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TruncateMiniAppTemplate extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('yz_mini_app_template_message')) {
            \Illuminate\Support\Facades\DB::table('yz_mini_app_template_message')->truncate();
        }

        if (Schema::hasTable('yz_mini_template_corresponding')) {
            \Illuminate\Support\Facades\DB::table('yz_mini_template_corresponding')->where("template_name","退款成功通知")->update(["template_name"=>"订单退款通知"]);

            \Illuminate\Support\Facades\DB::table('yz_mini_template_corresponding')->where("template_name","提现到账通知")->update(["template_name"=>"提现成功通知"]);

            \Illuminate\Support\Facades\DB::table('yz_mini_template_corresponding')->where("template_name","直播审核结果通知")->update(["template_name"=>"审核结果通知"]);
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
