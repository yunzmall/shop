<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddHideGoodsSalesToYzGoods extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('yz_goods')) {

            Schema::table('yz_goods', function (Blueprint $table) {
                if (!Schema::hasColumn('yz_goods', 'hide_goods_sales')) {
                    $table->tinyInteger('hide_goods_sales')->default(0)->comment('是否隐藏销量 1隐藏 0显示');
                }
            });

            Schema::table('yz_goods', function (Blueprint $table) {
                if (!Schema::hasColumn('yz_goods', 'hide_goods_sales_alone')) {
                    $table->tinyInteger('hide_goods_sales_alone')->default(0)->comment('隐藏销量独立设置 1开启 0关闭');
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
        //
    }
}
