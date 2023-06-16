<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateImsYzPayResponseDataTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        if (!Schema::hasTable('yz_pay_response_data')) {
            Schema::create('yz_pay_response_data', function (Blueprint $table) {
                $table->integer('id')->primary();
                $table->integer('uniacid');
                $table->string('out_order_no', 255)->comment('支付单号');
                $table->string('third_type', 255)->comment('支付方式');
                $table->text('params', 65535)->comment('响应参数');
                $table->integer('created_at')->default(0);
                $table->integer('updated_at');
            });
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE " . app('db')->getTablePrefix() . "yz_pay_response_data comment '支付--订单支付请求响应参数记录（一般为微信支付宝支付等）'");//表注释
        }
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('yz_pay_response_data');
	}

}
