<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateImsYzOrderCoinExchangeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('yz_order_coin_exchange')) {
            Schema::create('yz_order_coin_exchange',
                function (Blueprint $table) {
                    $table->increments('id');
                    $table->integer('order_id')->nullable()->comment('订单ID');
                    $table->integer('uid')->nullable()->comment('会员ID');
                    $table->string('code')->nullable()->comment('抵扣标识');
                    $table->string('name')->nullable()->comment('抵扣名称');
                    $table->decimal('amount',14,2)->nullable()->comment('抵扣金额');
                    $table->decimal('coin',14,2)->nullable()->comment('抵扣值');
                    $table->integer('created_at')->nullable();
                    $table->integer('updated_at')->nullable();
                    $table->integer('deleted_at')->nullable();
                    $table->index(['order_id']);
                });
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE `".app('db')->getTablePrefix()."yz_order_coin_exchange` comment'订单--全额抵扣记录'");//表注释
        }
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('yz_order_coin_exchange');
    }
}
