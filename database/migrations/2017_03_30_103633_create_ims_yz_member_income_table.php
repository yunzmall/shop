<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateImsYzMemberIncomeTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        if (!Schema::hasTable('yz_member_income')) {
            Schema::create('yz_member_income', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('uniacid')->comment('所属公众号');
                $table->integer('member_id')->comment('会员id');
                $table->string('type', 60)->default('');
                $table->integer('type_id')->nullable();
                $table->string('type_name', 120)->nullable()->comment('类型名字');
                $table->decimal('amount', 14)->default(0.00)->comment('金额');
                $table->boolean('status')->default(0)->comment('是否提现');
                $table->text('detail', 65535)->nullable()->comment('详情');
                $table->string('create_month', 20)->nullable()->default('创建月份');
                $table->integer('created_at')->nullable();
                $table->integer('updated_at')->nullable();
                $table->integer('deleted_at')->nullable();
            });
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE `" . app('db')->getTablePrefix() . "yz_member_income` comment '会员--收入表'");
        }
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('yz_member_income');
	}

}
