<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateImsYzMemberBankCardTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        if (!Schema::hasTable('yz_member_bank_card')) {
            Schema::create('yz_member_bank_card', function (Blueprint $table) {
                $table->integer('id', true);
                $table->integer('uniacid')->comment('所属公众号');
                $table->integer('member_id')->comment('会员ID');
                $table->string('member_name', 45)->default('')->comment('真实项目');
                $table->string('bank_name', 45)->default('')->comment('开户行');
                $table->string('bank_card', 100)->default('')->comment('银行卡号');
                $table->boolean('is_default')->comment('是不是默认卡');
                $table->integer('created_at');
                $table->integer('updated_at');
            });
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE `" . app('db')->getTablePrefix() . "yz_member_bank_card` comment '会员--银行卡信息表'");
        }
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('yz_member_bank_card');
	}

}
