<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateImsYzOrderDeductionTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        if (!\Schema::hasTable('yz_order_deduction')) {

            Schema::create('yz_order_deduction', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('uid')->default(0);
                $table->integer('order_id')->comment('订单ID');
                $table->string('code')->default('')->comment('抵扣标识');
                $table->string('name', 100)->default('')->comment('抵扣名称');
                $table->decimal('amount', 10)->default(0.00)->comment('抵扣金额');
                $table->decimal('coin',14,2)->default(0)->comment('抵扣数值');
                $table->integer('updated_at')->nullable();
                $table->integer('created_at')->nullable();
                $table->integer('deleted_at')->nullable();
            });

            \Illuminate\Support\Facades\DB::statement("ALTER TABLE `".app('db')->getTablePrefix()."yz_order_deduction` comment'订单--抵扣记录'");//表注释

        }
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
        if (\Schema::hasTable('yz_order_deduction')) {

            Schema::drop('yz_order_deduction');
        }
	}

}
