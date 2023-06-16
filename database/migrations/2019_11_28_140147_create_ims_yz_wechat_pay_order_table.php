<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateImsYzWechatPayOrderTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        if (!Schema::hasTable('yz_wechat_pay_order')) {
            Schema::create('yz_wechat_pay_order', function (Blueprint $table) {
                $table->integer('id', true);
                $table->integer('uniacid');
                $table->integer('order_id')->comment('订单ID');
                $table->integer('member_id')->comment('会员ID');
                $table->integer('account_id')->comment('门店ID');
                $table->string('pay_sn')->comment('订单支付编号');
                $table->string('order_sn')->comment('订单号');
                $table->string('transaction_id')->comment('微信支付单号');
                $table->decimal('total_fee', 14)->comment('支付金额，单位分');
                $table->boolean('profit_sharing')->default(0)->comment('是否开启分账');
                $table->integer('created_at')->nullable();
                $table->integer('updated_at')->nullable();
                $table->integer('deleted_at')->nullable();
            });
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE " . app('db')->getTablePrefix() . "yz_wechat_pay_order comment '微信服务商支付记录'");//表注释

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
