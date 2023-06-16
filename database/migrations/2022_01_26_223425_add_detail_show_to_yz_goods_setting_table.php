<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDetailShowToYzGoodsSettingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('yz_goods_setting')) {
            Schema::table('yz_goods_setting', function (Blueprint $table) {
                if (!Schema::hasColumn('yz_goods_setting', 'detail_show')) {
                    $table->tinyInteger('detail_show')->default(0)->comment('商品详情页-商品详情：1-默认显示，0-默认不显示');
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
        Schema::table('yz_goods_setting', function (Blueprint $table) {
            //
        });
    }
}
