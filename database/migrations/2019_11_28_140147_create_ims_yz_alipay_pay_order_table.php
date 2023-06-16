<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateImsYzAlipayPayOrderTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        if (!Schema::hasTable('yz_alipay_pay_order')) {
            Schema::create('yz_alipay_pay_order', function (Blueprint $table) {
                $table->integer('id', true);
                $table->integer('uniacid')->nullable();
                $table->integer('order_id')->nullable()->comment('订单ID');
                $table->integer('member_id')->nullable()->comment('会员ID');
                $table->integer('account_id')->nullable()->comment('门店ID');
                $table->string('pay_sn')->nullable()->comment('支付单号');
                $table->string('order_sn')->nullable()->comment('订单编号');
                $table->string('trade_no')->nullable()->comment('支付宝支付单号');
                $table->decimal('total_amount', 14)->nullable()->comment('支付金额');
                $table->boolean('royalty')->default(0)->nullable()->comment('是否开启分账');
                $table->integer('created_at')->nullable();
                $table->integer('updated_at')->nullable();
                $table->integer('deleted_at')->nullable();
            });
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE " . app('db')->getTablePrefix() . "yz_alipay_pay_order comment '支付宝服务商支付记录'");//表注释

        }
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('ims_yz_excel_recharge_detail');
	}

}
