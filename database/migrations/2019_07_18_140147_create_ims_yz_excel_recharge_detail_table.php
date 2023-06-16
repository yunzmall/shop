<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateImsYzExcelRechargeDetailTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        if (!Schema::hasTable('yz_excel_recharge_detail')) {
            Schema::create('yz_excel_recharge_detail', function (Blueprint $table) {
                $table->integer('id', true);
                $table->integer('uniacid');
                $table->integer('recharge_id')->comment('批量充值记录ID');
                $table->integer('member_id')->comment('会员ID');
                $table->decimal('amount', 14)->comment('充值总金额');
                $table->string('remark', 100)->nullable()->comment('充值说明');
                $table->boolean('status')->default(0)->comment('充值状态1成功0失败');
                $table->integer('created_at')->nullable();
                $table->integer('updated_at')->nullable();
            });
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE " . app('db')->getTablePrefix() . "yz_excel_recharge_detail comment '批量充值记录详情表'");//表注释
        }
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('ims_yz_excel_recharge_detail');
	}

}
