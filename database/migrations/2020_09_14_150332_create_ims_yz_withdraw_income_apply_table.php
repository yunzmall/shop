<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateImsYzWithdrawIncomeApplyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('yz_withdraw_income_apply')) {
            Schema::create('yz_withdraw_income_apply', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('uniacid')->comment('公众号id');
                $table->integer('member_id')->comment('会员id');
                $table->integer('withdraw_id')->comment('提现id');
                $table->integer('income_id')->comment('收入id');
                $table->integer('status')->comment('提现状态');
                $table->integer('updated_at')->nullable();
                $table->integer('created_at')->nullable();
                $table->integer('deleted_at')->nullable();
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
        //
    }
}
