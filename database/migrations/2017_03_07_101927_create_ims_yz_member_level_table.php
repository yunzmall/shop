<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateImsYzMemberLevelTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        if (!Schema::hasTable('yz_member_level')) {
            Schema::create('yz_member_level', function (Blueprint $table) {
                $table->integer('id', true);
                $table->integer('uniacid');
                $table->integer('level')->comment('等级阶级');
                $table->string('level_name', 45)->comment('等级名称');
                $table->string('order_money', 45)->nullable();
                $table->string('order_count', 45)->nullable();
                $table->integer('goods_id')->nullable()->comment('商品id');
                $table->string('discount', 45)->nullable()->comment('折扣');
                $table->integer('created_at');
                $table->integer('updated_at');
                $table->integer('deleted_at')->nullable();
            });
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE `" . app('db')->getTablePrefix() . "yz_member_level` comment '会员--等级'");
        }
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('yz_member_level');
	}

}
