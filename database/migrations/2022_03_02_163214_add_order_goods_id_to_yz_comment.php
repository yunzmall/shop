<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOrderGoodsIdToYzComment extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('yz_comment')) {
            Schema::table('yz_comment', function (Blueprint $table) {
                if (!Schema::hasColumn('yz_comment', 'order_goods_id')) {
                    $table->integer('order_goods_id')->default(0)->comment('订单商品表ID')->index('idx_order_goods_id');
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
        Schema::table('yz_comment', function (Blueprint $table) {
            $table->dropColumn('order_goods_id');
        });
    }
}
