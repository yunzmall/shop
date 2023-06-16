<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateYzOutsideOrderTradeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('yz_outside_order_trade')) {
            Schema::create('yz_outside_order_trade', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->integer('uniacid')->index('uniacid_idx');
                $table->text('order_ids')->nullable()->comment('商城订单id组');
                $table->string('outside_sn')->default('')->comment('第三方订单编号');
                $table->string('trade_sn')->default('')->comment('商城订单编号');
                $table->decimal('total_price',14,2)->default(0)->comment('订单实付金额');
                $table->integer('created_at')->nullable();
                $table->integer('updated_at')->nullable();
                $table->integer('deleted_at')->nullable();
            });

            \Illuminate\Support\Facades\DB::statement("ALTER TABLE `" . app('db')->getTablePrefix() . "yz_outside_order_trade` comment '第三方下单关系记录'");
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('yz_outside_order_trade');
    }
}
