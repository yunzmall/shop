<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateImsYzWithdrawTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        if (!Schema::hasTable('yz_withdraw')) {
            Schema::create('yz_withdraw', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('uniacid')->nullable();
                $table->integer('member_id')->nullable()->comment('会员id');
                $table->string('type', 60)->nullable()->comment('收入模型');
                $table->string('type_id', 60)->nullable()->comment('收入id');
                $table->decimal('amounts', 14)->nullable()->comment('审核金额');
                $table->decimal('poundage', 14)->nullable()->comment('手续费');
                $table->decimal('poundage_rate')->nullable()->comment('手续费比例');
                $table->string('pay_way', 100)->nullable()->comment('提现类型');
                $table->boolean('status')->nullable()->comment('审核状态');
                $table->integer('created_at')->nullable();
                $table->integer('updated_at')->nullable();
                $table->integer('deleted_at')->nullable();
            });
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE " . app('db')->getTablePrefix()
                . "yz_withdraw comment '财务--提现表'");//表注释
        }
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('yz_withdraw');
	}

}
