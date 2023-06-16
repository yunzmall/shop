<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPriceDescToYzGoodsSettingTable extends Migration
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
                if (!Schema::hasColumns('yz_goods_setting', ['is_price_desc', 'title', 'explain'])) {
                    $table->tinyInteger('is_price_desc')->default(0)->comment('价格说明开关：1-开启，0-关闭');
                    $table->string('title')->default('')->comment('自定义表题');
                    $table->text('explain')->default('')->comment('说明内容');
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
