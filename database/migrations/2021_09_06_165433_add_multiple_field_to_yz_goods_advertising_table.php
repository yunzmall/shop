<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMultipleFieldToYzGoodsAdvertisingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('yz_goods_advertising')) {
            Schema::table('yz_goods_advertising', function (Blueprint $table) {
                if (!Schema::hasColumn('yz_goods_advertising','font_size')) {
                    $table->integer('font_size')->default(0)->comment('字体大小');
                }
                if (!Schema::hasColumn('yz_goods_advertising','font_color')) {
                    $table->string('font_color')->default('')->comment('字体颜色');
                }
                if (!Schema::hasColumn('yz_goods_advertising','link')) {
                    $table->string('link')->default('')->comment('链接');
                }
                if (!Schema::hasColumn('yz_goods_advertising','font_size')) {
                    $table->string('min_link')->default('')->comment('小程序链接');
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
        Schema::table('yz_goods_advertising', function (Blueprint $table) {
            //
        });
    }
}
