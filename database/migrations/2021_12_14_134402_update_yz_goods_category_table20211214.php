<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateYzGoodsCategoryTable20211214 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('yz_goods_category')) {
            Schema::table('yz_goods_category', function (Blueprint $table) {
                if (!Schema::hasColumn('yz_goods_category', 'goods_option_id')) {
                    $table->integer('goods_option_id')->nullable()->comment('分类组关联商品规格ID');
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
        if (Schema::hasTable('yz_goods_category')) {
            Schema::table('yz_goods_category', function (Blueprint $table) {
                if (Schema::hasColumn('yz_goods_category', 'goods_option_id')) {
                    $table->dropColumn('goods_option_id');
                }
            });
        }
    }
}
