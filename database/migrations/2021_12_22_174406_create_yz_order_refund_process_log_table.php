<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateYzOrderRefundProcessLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('yz_order_refund_process_log')) {
            Schema::create('yz_order_refund_process_log', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('order_id')->index('order_idx')->comment('订单ID');
                $table->integer('refund_id')->index('refund_idx')->comment('退款ID');
                $table->integer('operator')->default(0)->comment('操作员类型');
                $table->integer('operator_id')->default(0)->comment('操作员ID');
                $table->integer('operate_type')->nullable()->comment('操作类型');
                $table->text('detail')->nullable()->comment('详情描述');
                $table->text('remark')->nullable()->comment('备用字段');
                $table->integer('created_at')->nullable();
                $table->integer('updated_at')->nullable();
            });

            \Illuminate\Support\Facades\DB::statement("ALTER TABLE `".app('db')->getTablePrefix()."yz_order_refund_process_log` comment'订单--售后协商记录'");//表注释
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('yz_order_refund_process_log');
    }
}
