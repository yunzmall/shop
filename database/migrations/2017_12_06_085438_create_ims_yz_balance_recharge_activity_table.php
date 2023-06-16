<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateImsYzBalanceRechargeActivityTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        if (!Schema::hasTable('yz_balance_recharge_activity')) {
            Schema::create('yz_balance_recharge_activity', function (Blueprint $table) {
                $table->integer('id', true);
                $table->integer('uniacid');
                $table->integer('member_id')->comment('参与会员ID');
                $table->integer('activity_id')->comment('活动ID');
                $table->integer('partake_count')->comment('参与次数');;
                $table->integer('created_at');
                $table->integer('updated_at');
            });
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE " . app('db')->getTablePrefix() . "yz_balance_recharge_activity comment '财务--充值活动记录表'");//表注释

        }
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('yz_balance_recharge_activity');
	}

}
