<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateYzGoodsTradeSetTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('yz_goods_trade_set')) {
            Schema::create('yz_goods_trade_set', function (Blueprint $table) {
                    $table->increments('id');
                    $table->integer('uniacid')->comment('公众号')->index('uniacid_idx');
                    $table->integer('goods_id')->comment('商品id')->index('goods_idx');
                    $table->tinyInteger('hide_status')->nullable()->comment('隐藏售后按钮时间段开关');
                    $table->string('begin_hide_day')->nullable()->comment('多少天后开始隐藏');
                    $table->string('begin_hide_time')->nullable()->comment('多少天后的什么时间开始隐藏');
                    $table->string('end_hide_day')->nullable()->comment('隐藏当日/次日结束隐藏');
                    $table->string('end_hide_time')->nullable()->comment('结束隐藏时间');
                    $table->string('auto_send')->nullable()->comment('自动发货开关');
                    $table->string('auto_send_day')->nullable()->comment('多少天后自动发货');
                    $table->string('auto_send_time')->nullable()->comment('自动发货时间');
                    $table->string('arrived_day')->nullable()->comment('多少天后送达');
                    $table->string('arrived_time')->nullable()->comment('送达时间');
                    $table->string('arrived_word')->nullable()->comment('送达时间自定义文字');
                    $table->integer('created_at')->nullable();
                    $table->integer('updated_at')->nullable();
                    $table->integer('deleted_at')->nullable();
                });
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE `" . app('db')->getTablePrefix() . "yz_goods_trade_set` comment'商品--交易设置'");//表注释
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('yz_goods_trade_set');
    }
}
