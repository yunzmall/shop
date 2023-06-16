<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateImsYzOrderCountTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //ps：这张表没有看见有使用了
        if (!Schema::hasTable('yz_order_count')) {
            Schema::create('yz_order_count', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('uniacid')->default(0);
                $table->integer('member_id')->default(0)->comment('会员ID');
                $table->integer('parent_id')->default(0)->comment('父ID');
                $table->integer('total_quantity')->default(0)->comment('订单总数量');
                $table->decimal('total_amount', 10)->nullable()->default(0.00)->comment('订单总金额');
                $table->integer('total_pay_quantity')->default(0)->comment('已支付订单总数量');
                $table->decimal('total_pay_amount', 10)->nullable()->default(0.00)->comment('已支付订单总金额');
                $table->integer('total_complete_quantity')->default(0)->comment('已完成订单总数量');
                $table->decimal('total_complete_amount', 10)->nullable()->default(0.00)->comment('已完成订单总金额');
                $table->integer('created_at')->nullable();
                $table->integer('updated_at')->nullable();
            });
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE `".app('db')->getTablePrefix()."yz_order_count` comment'订单--会员统计记录'");//表注释
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('yz_order_count');
    }
}
