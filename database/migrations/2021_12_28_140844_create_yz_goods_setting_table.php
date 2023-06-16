<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateYzGoodsSettingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('yz_goods_setting')) {
            Schema::create('yz_goods_setting', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->integer('uniacid');
                $table->tinyInteger('is_month_sales')->default(0)->comment('月销售量开关：1-开启，0-关闭');
                $table->integer('created_at')->default(0);
                $table->integer('updated_at')->default(0);
                $table->integer('deleted_at')->nullable();
            });

            \Illuminate\Support\Facades\DB::statement("ALTER TABLE `" . app('db')->getTablePrefix() . "yz_goods_setting` comment '商品基础设置表'");
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('yz_goods_setting');
    }
}
