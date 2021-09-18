<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateYzOrderBehalfPayRecordTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		if (!Schema::hasTable('yz_order_behalf_pay_record')) {
			Schema::create('yz_order_behalf_pay_record', function (Blueprint $table) {
				$table->increments('id');
				$table->integer('uniacid')->default(0)->nullable();
				$table->string('order_ids', 500)->default('')->comment('订单号合集');
				$table->integer('order_pay_id')->comment('订单支付表id');
				$table->string('pay_sn', 23)->default('')->comment('支付单号');
				$table->integer('member_id')->comment('订单会员id');
				$table->integer('behalf_id')->comment('代付人id');
				$table->integer('behalf_type')->default(1)->comment('1是找人代付，2是上级代付');
				$table->integer('updated_at')->nullable();
				$table->integer('created_at')->nullable();
				$table->integer('deleted_at')->nullable();
			});
		}
		\Illuminate\Support\Facades\DB::statement("ALTER TABLE ".app('db')->getTablePrefix()."yz_order_behalf_pay_record comment '代付记录表'");

	}

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('yz_order_behalf_pay_record');
    }
}
