<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateYzMemberMonthOrder extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('yz_member_month_order')) {
            Schema::create('yz_member_month_order', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('member_id')->default(0)->comment('会员id');
                $table->smallInteger('year')->default(0)->comment('年');
                $table->smallInteger('month')->default(0)->comment('月');
                $table->integer('order_num')->default(0)->comment('订单数量');
                $table->decimal('order_price', 10)->default(0.00)->comment('订单总额');
                $table->integer('created_at')->nullable();
                $table->integer('updated_at')->nullable();
            });
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE `" . app('db')->getTablePrefix() . "yz_member_month_order` comment '会员--每月订单'");
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
