<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateYzGoodsTradeLog extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('yz_goods_trade_log')) {
            Schema::create('yz_goods_trade_log', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('order_id')->comment('订单id');
                $table->integer('goods_id')->comment('商品id');
                $table->string('show_time_word')->comment('展示时间');
                $table->integer('created_at')->nullable();
                $table->integer('updated_at')->nullable();
                $table->integer('deleted_at')->nullable();
            });
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE `" . app('db')->getTablePrefix() . "yz_goods_trade_log` comment'商品--商品交易记录'");//表注释
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('yz_goods_trade_log');
    }
}
