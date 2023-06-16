<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateImsYzPayTypeTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        if (!Schema::hasTable('yz_pay_type')) {
            Schema::create('yz_pay_type', function (Blueprint $table) {
                $table->increments('id')->comment('支付类型ID');
                $table->string('name', 50)->default('')->comment('支付类型名称');
                $table->integer('plugin_id');
            });
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE `".app('db')->getTablePrefix()."yz_pay_type` comment'系统--支付类型'");//表注释

        }
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('yz_pay_type');
	}

}
