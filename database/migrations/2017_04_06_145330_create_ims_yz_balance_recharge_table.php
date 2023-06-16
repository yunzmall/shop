<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateImsYzBalanceRechargeTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        if (!Schema::hasTable('yz_balance_recharge')) {
            Schema::create('yz_balance_recharge', function (Blueprint $table) {
                $table->integer('id', true);
                $table->integer('uniacid')->nullable();
                $table->integer('member_id')->nullable()->comment('充值会员ID');;
                $table->decimal('old_money', 14)->nullable()->comment('修改前额度');;
                $table->decimal('money', 14)->nullable()->comment('修改额度');;
                $table->decimal('new_money', 14)->nullable()->comment('修改后额度');;
                $table->integer('type')->nullable()->comment('充值类型');;
                $table->integer('created_at')->nullable();
                $table->integer('updated_at')->nullable();
                $table->string('ordersn', 50)->nullable()->comment('充值订单号');;
                $table->boolean('status')->nullable()->default(0)->comment('状态');;
            });
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE `" . app('db')->getTablePrefix()
                . "yz_balance_recharge` comment '财务--余额充值表'");
        }
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('yz_balance_recharge');
	}

}
