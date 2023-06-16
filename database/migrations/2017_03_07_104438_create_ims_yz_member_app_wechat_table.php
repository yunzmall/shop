<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateImsYzMemberAppWechatTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        if (!Schema::hasTable('yz_member_app_wechat')) {
            Schema::create('yz_member_app_wechat', function (Blueprint $table) {
                $table->integer('app_wechat_id')->primary();
                $table->integer('uniacid')->comment('公众号');
                $table->integer('member_id')->comment('会员id');
                $table->string('openid', 50)->comment('openid');
                $table->string('nickname', 20)->comment('昵称');
                $table->string('avatar')->comment('头像');
                $table->boolean('gender')->default(0)->comment('性别0未知1男2女');
                $table->integer('created_at')->unsigned()->default(0);
                $table->integer('updated_at')->unsigned()->default(0);
                $table->integer('deleted_at')->nullable();
            });
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE " . app('db')->getTablePrefix() . "yz_member_app_wechat comment '微信APP登录表'");//表注释
        }
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('yz_member_app_wechat');
	}

}
