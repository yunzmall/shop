<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateYzBalanceRechargeCheckTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('yz_balance_recharge_check')) {
            Schema::create('yz_balance_recharge_check', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->integer('uniacid');
                $table->integer('member_id')->comment('会员ID');
                $table->decimal('money',12,2)->comment('充值余额');
                $table->integer('type')->comment('充值类型');
                $table->integer('operator_id')->nullable()->comment('操作者ID');
                $table->integer('operator')->nullable()->comment('操作者');
                $table->integer('source')->comment('来源');
                $table->string('enclosure')->nullable()->comment('附件');
                $table->string('recharge_remark')->nullable()->comment('充值时填写的备注');
                $table->string('remark')->nullable()->comment('备注');
                $table->string('explain')->nullable()->comment('说明');
                $table->tinyInteger('status')->default(0)->comment('状态：0待审核,1通过,2驳回');
                $table->integer('created_at')->nullable();
                $table->integer('updated_at')->nullable();

                $table->index('uniacid');
            });
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE `" . app('db')->getTablePrefix()
                . "yz_balance_recharge_check` comment '余额充值审核表'");
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('yz_balance_recharge_check');
    }
}
