<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateImsYzOrderOperationLogTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        if (!Schema::hasTable('yz_order_operation_log')) {
            Schema::create('yz_order_operation_log', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('order_id')->nullable()->default(0)->comment('订单ID');
                $table->boolean('before_operation_status')->nullable()->default(0)->comment('操作前状态');
                $table->boolean('after_operation_status')->nullable()->default(0)->comment('操作后状态');
                $table->string('operator', 50)->nullable()->default('')->comment('操作员');
                $table->integer('operation_time')->nullable()->default(0)->comment('操作时间');
                $table->integer('created_at')->nullable()->default(0);
                $table->integer('updated_at')->nullable();
                $table->string('type', 10)->nullable()->default('0');
            });
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE `".app('db')->getTablePrefix()."yz_order_operation_log` comment'订单--状态操作记录'");//表注释

        }
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('yz_order_operation_log');
	}

}
