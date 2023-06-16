<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateImsYzOrderStatistics extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        if (!Schema::hasTable('yz_order_statistics')) {
            Schema::create('yz_order_statistics', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('uid')->default(0);
                $table->integer('uniacid')->default(0);
                $table->integer('total_quantity')->default(0)->nullable()->comment('商城总订单数');
                $table->integer('total_amount')->default(0)->nullable()->comment('商城总订单金额');
                $table->integer('total_pay_quantity')->default(0)->nullable()->comment('商城已支付订单数');
                $table->integer('total_pay_amount')->default(0)->nullable()->comment('商城已支付订单金额');
                $table->integer('total_complete_quantity')->default(0)->nullable()->comment('商城已完成订单数');
                $table->integer('total_complete_amount')->default(0)->nullable()->comment('商城已完成订单金额');
                $table->integer('created_at')->nullable();
                $table->integer('updated_at')->nullable();
                $table->integer('deleted_at')->nullable();
            });
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE " . app('db')->getTablePrefix() . "yz_order_statistics comment '订单--商城订单统计表'");//表注释
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
        Schema::dropIfExists('yz_order_statistics');
    }
}
