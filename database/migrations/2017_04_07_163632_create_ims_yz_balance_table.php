<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateImsYzBalanceTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        if (!Schema::hasTable('yz_balance')) {
            Schema::create('yz_balance', function (Blueprint $table) {
                $table->integer('id', true);
                $table->integer('uniacid')->nullable();
                $table->integer('member_id')->nullable()->comment('会员id');
                $table->decimal('old_money', 14)->nullable()->comment('修改前额度');
                $table->decimal('change_money', 14)->comment('修改额度');
                $table->decimal('new_money', 14)->comment('修改后额度');
                $table->boolean('type')->comment('变动类型，1收入，2支出');
                $table->boolean('service_type');
                $table->string('serial_number', 45)->default('')->comment('订单编号');
                $table->integer('operator')->comment('操作类型');
                $table->string('operator_id', 45)->default('')->comment('操作者id');
                $table->string('remark', 200)->default('')->comment('余额变更备注');
                $table->integer('created_at');
            });
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE " . app('db')->getTablePrefix() . "yz_balance comment '财务--余额变更记录表'");//表注释
        }
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('yz_balance');
	}

}
