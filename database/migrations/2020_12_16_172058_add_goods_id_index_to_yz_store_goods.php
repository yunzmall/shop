<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddGoodsIdIndexToYzStoreGoods extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('yz_store_goods')) {
            if (Schema::hasColumn('yz_store_goods', 'goods_id')) {
                Schema::table('yz_store_goods', function (Blueprint $table) {
                    $table->index(['goods_id'], 'idx_goods_id');
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('yz_store_goods')) {
            if (Schema::hasColumn('yz_store_goods', 'goods_id')) {
                Schema::table('yz_store_goods', function (Blueprint $table) {
                    $table->dropIndex('idx_goods_id');
                });
            }
        }
    }
}
