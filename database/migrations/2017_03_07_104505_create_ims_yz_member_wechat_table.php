<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateImsYzMemberWechatTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        if (!Schema::hasTable('yz_member_wechat')) {
            Schema::create('yz_member_wechat', function (Blueprint $table) {
                $table->integer('wechat_id', true);
                $table->integer('uniacid');
                $table->integer('member_id')->index('idx_member_id')->comment('会员id');
                $table->string('openid', 50)->comment('唯一标识');
                $table->string('nickname', 20)->comment('昵称');
                $table->boolean('gender')->default(0)->comment('性别0未知1男2女');
                $table->string('avatar')->comment('头像');
                $table->string('province', 4)->comment('省');
                $table->string('city', 25)->comment('市');
                $table->string('country', 10)->comment('国家');
                $table->integer('created_at')->unsigned()->default(0);
                $table->integer('updated_at')->unsigned()->default(0);
                $table->integer('deleted_at')->nullable();
            });
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE `" . app('db')->getTablePrefix() . "yz_member_wechat` comment '会员--app端辅助表'");
        }
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('yz_member_wechat');
	}

}
