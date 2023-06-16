<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateYzOrderGoodsAmdYzOrderRefundGoodsLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('yz_order_goods')) {
            Schema::table('yz_order_goods', function (Blueprint $table) {
                if (!Schema::hasColumn('yz_order_goods', 'refund_id')) {
                    $table->integer('refund_id')->default(0)->comment('进行中的售后记录ID')->index('refund_idx');
                }

                if (Schema::hasColumn('yz_order_goods', 'is_refund')) {
                    $table->integer('is_refund')->default(0)->change();
                }
            });
        }

        if (Schema::hasTable('yz_order_refund_goods_log')) {
            Schema::table('yz_order_refund_goods_log', function (Blueprint $table) {
                if (!Schema::hasColumn('yz_order_refund_goods_log', 'refund_type')) {
                    $table->tinyInteger('refund_type')->nullable()->comment('售后类型，用于过滤非退款售后');
                }
            });

            Schema::table('yz_order_refund_goods_log', function (Blueprint $table) {
                if (!Schema::hasColumn('yz_order_refund_goods_log', 'status')) {
                    $table->tinyInteger('status')->default(0)->nullable()->comment('售后商品状态');
                }
            });
        }

        if (Schema::hasTable('yz_point_log')) {
            Schema::table('yz_point_log', function (Blueprint $table) {
                if (!Schema::hasColumn('yz_point_log', 'order_goods_id')) {
                    $table->integer('order_goods_id')->default(0)->comment( '订单商品id,退款返还积分使用');
                } else {
                    $table->integer('order_goods_id')->default(0)->change();
                }
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
