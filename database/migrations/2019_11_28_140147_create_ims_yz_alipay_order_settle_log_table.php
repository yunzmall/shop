<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateImsYzAlipayOrderSettleLogTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        if (!Schema::hasTable('yz_alipay_order_settle_log')) {
            Schema::create('yz_alipay_order_settle_log', function (Blueprint $table) {
                $table->integer('id', true);
                $table->integer('uniacid')->nullable();
                $table->integer('order_id')->nullable()->comment('订单ID');
                $table->string('app_id')->nullable()->comment('支付宝APPid');
                $table->string('app_auth_token')->nullable()->comment('子商户授权token');
                $table->string('royalty_type')->nullable()->comment('分账类型');
                $table->string('trans_out_type')->nullable()->comment('支出方账户类型');
                $table->string('trans_in_type')->nullable()->comment('收入方账户类型');
                $table->string('trans_out')->nullable()->comment('支出方账户');
                $table->string('trans_in')->nullable()->comment('收入方账户');
                $table->string('trade_no')->nullable()->comment('支付宝订单号');
                $table->string('out_request_no')->nullable()->comment('结算请求流水号');
                $table->string('message')->nullable()->comment('回调信息');
                $table->decimal('amount',10 ,2)->nullable()->comment('分账的金额');
                $table->integer('status')->nullable()->comment('分账状态');
                $table->integer('created_at')->nullable();
                $table->integer('updated_at')->nullable();
                $table->integer('deleted_at')->nullable();
            });
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE " . app('db')->getTablePrefix() . "yz_alipay_order_settle_log comment '支付宝服务商分账记录'");//表注释

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
