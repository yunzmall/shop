<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateImsYzMemberAddressTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        if (!Schema::hasTable('yz_member_address')) {
            Schema::create('yz_member_address', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('uniacid')->unsigned()->index('idx_uinacid');
                $table->integer('uid')->unsigned()->index('idx_uid');
                $table->string('username', 20)->comment('用户名称');
                $table->string('mobile', 11)->comment('手机号');
                $table->string('zipcode', 6)->comment('邮政编码');
                $table->string('province', 32)->comment('省');
                $table->string('city', 32)->comment('市');
                $table->string('district', 32)->comment('区');
                $table->string('street', 32)->comment('街道');
                $table->string('address', 512)->comment('详细地址');
                $table->boolean('isdefault')->comment('是否默认');
            });
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE `" . app('db')->getTablePrefix() . "yz_member_address` comment '会员--地址表'");
        }
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
        if (Schema::hasTable('ims_yz_member_address')) {
            Schema::drop('ims_yz_member_address');
        }
	}
}
