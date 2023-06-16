<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateImsYzMemberUniqueTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        if (!Schema::hasTable('yz_member_unique')) {
            Schema::create('yz_member_unique', function (Blueprint $table) {
                $table->integer('unique_id', true);
                $table->integer('uniacid')->nullable()->index('idx_uniacid')->comment('公众号');
                $table->string('unionid', 64)->unique('idx_unionid')->comment('开放平台唯一标识');
                $table->integer('member_id')->index('idx_member_id')->comment('会员id');
                $table->string('type')->nullable()->comment('登陆类型');
                $table->integer('created_at')->unsigned()->default(0);
            });
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE `" . app('db')->getTablePrefix() . "yz_member_unique` comment '会员--开放平台同步辅助表'");
        }
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('yz_member_unique');
	}

}
