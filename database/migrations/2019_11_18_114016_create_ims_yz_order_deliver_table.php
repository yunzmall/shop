<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateImsYzOrderDeliverTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        if (!Schema::hasTable('yz_order_deliver')) {
            Schema::create('yz_order_deliver',
                function (Blueprint $table) {
                    $table->increments('id');
                    $table->integer('order_id')->nullable()->comment('订单ID');
                    $table->integer('deliver_id')->nullable()->comment('自提点ID');
                    $table->integer('clerk_id')->nullable()->comment('核销员ID');
                    $table->string('deliver_name', 255)->nullable()->comment('自提点名称');
                    $table->integer('created_at')
                        ->nullable();
                    $table->integer('updated_at')
                        ->nullable();
                    $table->integer('deleted_at')
                        ->nullable();
                });
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE " . app('db')->getTablePrefix() . "yz_order_deliver comment '订单自提点自提记录表'");//表注释
        }
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('yz_order_deliver');
	}

}
