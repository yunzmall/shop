<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTypeToYzOrderGoodsTable extends Migration
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
                if (!Schema::hasColumn('yz_order_goods', 'type')) {
                    $table->integer('type')->nullable()->comment('商品类型');
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

    }
}
