<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateImsYzPayLogTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        if (!Schema::hasTable('yz_pay_log')) {
            Schema::create('yz_pay_log', function (Blueprint $table) {
                $table->integer('id', true);
                $table->integer('uniacid');
                $table->integer('member_id');
                $table->tinyInteger('type')->comment('支付操作类型');
                $table->string('third_type', 255)->comment('支付方式');
                $table->decimal('price', 14, 2)->comment('支付金额');
                $table->text('operation', 65535)->comment('支付操作详情');
                $table->string('ip', 135);
                $table->integer('created_at')->default(0);
                $table->integer('updated_at')->nullable()->default(0);
            });
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE " . app('db')->getTablePrefix() . "yz_pay_log comment '支付--支付记录表'");//表注释
        }
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('yz_pay_log');
	}

}
