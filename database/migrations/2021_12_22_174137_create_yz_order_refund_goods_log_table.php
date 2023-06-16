<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateYzOrderRefundGoodsLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('yz_order_refund_goods_log')) {
            Schema::create('yz_order_refund_goods_log', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('order_id')->index('order_idx')->comment('订单ID');
                $table->integer('refund_id')->index('refund_idx')->comment('退款ID');
                $table->integer('order_goods_id')->index('order_goods_idx')->comment('订单商品ID');
                $table->integer('send_num')->default(0)->comment('发货数量');
                $table->integer('refund_total')->default(0)->comment('退款商品数量');
                $table->decimal('refund_price',14,2)->default(0)->comment('退款商品金额');
                $table->tinyInteger('refund_type')->nullable()->comment('售后类型，用于过滤非退款售后');
                $table->tinyInteger('status')->default(0)->nullable()->comment('售后商品状态');
                $table->integer('created_at')->nullable();
                $table->integer('updated_at')->nullable();
                $table->integer('deleted_at')->nullable();
            });

            \Illuminate\Support\Facades\DB::statement("ALTER TABLE `".app('db')->getTablePrefix()."yz_order_refund_goods_log` comment'订单--已退款订单商品记录'");//表注释
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('yz_order_refund_goods_log');
    }
}
