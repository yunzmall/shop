<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateImsYzPayAccessLogTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        if (!Schema::hasTable('yz_pay_access_log')) {
            Schema::create('yz_pay_access_log', function (Blueprint $table) {
                $table->integer('id', true);
                $table->integer('uniacid');
                $table->integer('member_id');
                $table->text('url', 65535);
                $table->char('http_method', 7);
                $table->string('ip', 135);
                $table->integer('created_at')->default(0);
                $table->integer('updated_at')->nullable()->default(0);
            });
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE " . app('db')->getTablePrefix() . "yz_pay_access_log comment '支付--支付通道记录表'");//表注释
        }
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('yz_pay_access_log');
	}

}
