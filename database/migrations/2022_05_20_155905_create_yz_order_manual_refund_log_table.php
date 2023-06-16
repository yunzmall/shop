<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateYzOrderManualRefundLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        if (!Schema::hasTable('yz_order_manual_refund_log')) {
            Schema::create('yz_order_manual_refund_log', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->integer('order_id')->comment('订单ID')->index('order_idx');
                $table->integer('operator_id')->nullable()->comment('操作员ID');
                $table->integer('operator')->default(0)->nullable()->comment('操作员类型');
                $table->integer('created_at')->nullable();
                $table->integer('updated_at')->nullable();
            });
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE " . app('db')->getTablePrefix()."yz_order_manual_refund_log comment '订单--退款并关闭记录'");//表注释
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('yz_order_manual_refund_log');
    }
}
