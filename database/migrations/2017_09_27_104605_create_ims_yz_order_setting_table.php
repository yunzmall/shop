<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateImsYzOrderSettingTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        if (!Schema::hasTable('yz_order_setting')) {
            Schema::create('yz_order_setting', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('order_id')->default(0);
                $table->string('key', 50)->default('')->comment('键');
                $table->text('value', 65535)->comment('值');
                $table->integer('created_at')->nullable();
                $table->integer('updated_at')->nullable();
                $table->integer('deleted_at')->nullable();
            });

            \Illuminate\Support\Facades\DB::statement("ALTER TABLE `".app('db')->getTablePrefix()."yz_order_setting` comment'订单--创建时设置记录'");//表注释

        }
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
        if (Schema::hasTable('yz_order_setting')) {
            Schema::drop('yz_order_setting');
        }
	}

}
