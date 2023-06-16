<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateYzWithdrawIncomeDeductionLove extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('yz_withdraw_income_deduction_love')) {
            Schema::create('yz_withdraw_income_deduction_love', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('uniacid')->comment('公众号')->index('uniacid_idx');
                $table->integer('member_id')->comment('会员ID');
                $table->integer('withdraw_id')->comment('提现ID');
                $table->integer('income_id')->comment('收入ID');
                $table->integer('status')->comment('状态:1-已扣除，-1已退还');
                $table->decimal('need_deduction_love_rate',10,2)->comment('扣除爱心值比例');
                $table->string('need_deduction_love_type',32)->comment('扣除爱心值类型');
                $table->integer('created_at')->nullable();
                $table->integer('updated_at')->nullable();
                $table->integer('deleted_at')->nullable();
            });
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE `" . app('db')->getTablePrefix() . "yz_withdraw_income_deduction_love` comment'提现--收入提现扣除爱心值记录'");//表注释
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('yz_withdraw_income_deduction_love');
    }
}
