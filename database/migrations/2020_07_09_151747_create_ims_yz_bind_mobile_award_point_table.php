<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateImsYzBindMobileAwardPointTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        if (!Schema::hasTable('yz_bind_mobile_award_point')) {
            Schema::create('yz_bind_mobile_award_point', function (Blueprint $table) {
                $table->integer('id', true)->comment('主键ID');
                $table->integer('uniacid')->comment('平台ID');
                $table->integer('member_id')->comment('会员ID');
                $table->decimal('point', 14)->comment('奖励积分');
                $table->integer('created_at')->comment('创建时间');
                $table->integer('updated_at')->comment('修改时间');
            });
        }
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		//Schema::drop('ims_yz_bind_mobile_award_point');
	}

}
