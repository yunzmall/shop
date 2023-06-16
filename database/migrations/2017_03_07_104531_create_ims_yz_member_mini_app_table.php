<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateImsYzMemberMiniAppTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        if (!Schema::hasTable('yz_member_mini_app')) {
            Schema::create('yz_member_mini_app', function (Blueprint $table) {
                $table->integer('mini_app_id', true);
                $table->integer('uniacid')->comment('公众号');
                $table->integer('member_id')->comment('会员id');
                $table->string('openid', 50)->comment('唯一标识');
                $table->string('nickname', 20)->comment('昵称');
                $table->string('avatar')->comment('头像');
                $table->boolean('gender')->comment('性别0未知1男2女');
                $table->integer('created_at')->unsigned()->default(0);
                $table->integer('updated_at')->unsigned()->default(0);
                $table->integer('deleted_at')->nullable();
            });
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE `" . app('db')->getTablePrefix() . "yz_member_mini_app` comment '会员--小程序辅助表'");
        }
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('yz_member_mini_app');
	}

}
