<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTimeToYzGoodsService extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('yz_goods_service')) {
            Schema::table('yz_goods_service', function (Blueprint $table) {
                if (!Schema::hasColumn('yz_goods_service', 'time_type')) {
                    $table->integer("time_type")->default(0)->nullable()->comment("时间方式0固定1循环");
                }
                if (!Schema::hasColumn('yz_goods_service', 'loop_date_start')) {
                    $table->integer("loop_date_start")->nullable()->comment("循环开始日期");
                }
                if (!Schema::hasColumn('yz_goods_service', 'loop_date_end')) {
                    $table->integer("loop_date_end")->nullable()->comment("循环结束日期");
                }
                if (!Schema::hasColumn('yz_goods_service', 'loop_time_up')) {
                    $table->string("loop_time_up")->nullable()->comment("循环上架时间");
                }
                if (!Schema::hasColumn('yz_goods_service', 'loop_time_down')) {
                    $table->string("loop_time_down")->nullable()->comment("循环下架时间");
                }
                if (!Schema::hasColumn('yz_goods_service', 'auth_refresh_stock')) {
                    $table->integer("auth_refresh_stock")->default(1)->nullable()->comment("循环刷新库存");
                }
                if (!Schema::hasColumn('yz_goods_service', 'original_stock')) {
                    $table->integer("original_stock")->nullable()->comment("循环库存");
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

    }
}
