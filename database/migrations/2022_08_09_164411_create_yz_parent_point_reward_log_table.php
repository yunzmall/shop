<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateYzParentPointRewardLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('yz_parent_point_reward_log')) {
            Schema::create('yz_parent_point_reward_log', function (Blueprint $table) {
                $table->integer('id', true);
                $table->integer('uniacid')->comment('公众号ID');
                $table->integer('uid')->comment('会员ID');
                $table->integer('order_id')->comment('订单ID');
                $table->integer('order_goods_id')->comment('订单商品ID');
                $table->decimal('point', 14, 2)->comment('赠送积分');
                $table->integer('expect_reward_time')->nullable()->comment('预计奖励时间');
                $table->integer('actual_reward_time')->nullable()->comment('实际奖励时间');
                $table->tinyInteger('status')->comment('0待发放 1已发放 -1已失效');
                $table->tinyInteger('level')->comment('x级上级');
                $table->integer('created_at');
                $table->integer('updated_at');
            });
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE `" . app('db')->getTablePrefix() . "yz_parent_point_reward_log` comment '上级积分赠送记录表'");
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

    }
}
