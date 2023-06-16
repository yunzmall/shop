<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateImsYzMemberLowerGroupOrder extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        if (!Schema::hasTable('yz_member_lower_group_order')) {
            Schema::create('yz_member_lower_group_order', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('uid')->default(0)->comment('会员ID');
                $table->integer('uniacid')->default(0);
                $table->integer('team_count')->default(0)->comment('团队总人数');
                $table->integer('goods_count')->default(0)->comment('支付订单商品总数');
                $table->integer('pay_count')->default(0)->comment('支付下线人数');
                $table->integer('amount')->default(0)->comment('支付订单总额');
                $table->integer('created_at')->nullable();
                $table->integer('updated_at')->nullable();
            });
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE " . app('db')->getTablePrefix() . "yz_member_lower_group_order comment '会员团队支付记录表'");//表注释
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
        Schema::drop('yz_member_lower_order');
    }
}
