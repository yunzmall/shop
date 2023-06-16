<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateYzOrderPluginTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('yz_order_plugin')) {
            Schema::create('yz_order_plugin', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('order_id')->comment('订单plugin_id');
                $table->integer('main_plugin_id')->comment('订单plugin_id');
                $table->integer('sub_plugin_id')->comment('订单商品plugin_id，注：不同plugin_id的商品会分单，不存在一个订单里有多个不同plugin_id的商品');
                $table->integer('created_at')->nullable();
                $table->integer('updated_at')->nullable();
                $table->integer('deleted_at')->nullable();
                $table->index('order_id');
            });
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE ".app('db')->getTablePrefix()
                ."yz_order_plugin comment '订单--订单商品插件表'");
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('yz_order_plugin');
    }
}
