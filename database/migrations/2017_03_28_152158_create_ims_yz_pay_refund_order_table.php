<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateImsYzPayRefundOrderTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        if (!Schema::hasTable('yz_pay_refund_order')) {
            Schema::create('yz_pay_refund_order', function (Blueprint $table) {
                $table->integer('id')->primary();
                $table->integer('uniacid');
                $table->integer('member_id');
                $table->string('int_order_no', 32)->comment('支付单号');
                $table->string('out_order_no', 32)->comment('订单单号');
                $table->string('trade_no', 255)->comment('支付批次号');
                $table->decimal('price', 14,2)->comment('金额');
                $table->string('type', 255)->comment('支付操作类型');
                $table->tinyInteger('status')->comment('状态');
                $table->integer('created_at')->default(0);
                $table->integer('updated_at')->default(0);
                $table->integer('deleted_at')->nullable();
            });
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE " . app('db')->getTablePrefix() . "yz_pay_refund_order comment '支付--退款记录表'");//表注释
        }
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('yz_pay_refund_order');
	}

}
