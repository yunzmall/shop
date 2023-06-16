<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateImsYzMemberHistoryTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        if (!Schema::hasTable('yz_member_history')) {
            Schema::create('yz_member_history', function (Blueprint $table) {
                $table->integer('id', true);
                $table->integer('member_id')->comment('会员id');
                $table->integer('uniacid');
                $table->integer('goods_id')->comment('商品id');
                $table->integer('created_at');
                $table->integer('updated_at');
                $table->integer('deleted_at')->nullable();
            });
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE `" . app('db')->getTablePrefix() . "yz_member_history` comment '会员--浏览历史'");
        }
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('yz_member_history');
	}

}
