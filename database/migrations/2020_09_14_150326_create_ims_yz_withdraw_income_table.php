<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateImsYzWithdrawIncomeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('yz_withdraw_income')) {
            Schema::create('yz_withdraw_income', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('uniacid')->comment('公众号id');
                $table->integer('member_id')->comment('会员id');
                $table->integer('withdraw_id')->comment('提现id');
                $table->integer('income_id')->comment('收入id');
                $table->integer('updated_at')->nullable();
                $table->integer('created_at')->nullable();
                $table->unique('income_id');
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
