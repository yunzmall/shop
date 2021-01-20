<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddVolumeToGoodsOption extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('yz_goods_option')) {
            Schema::table('yz_goods_option', function (Blueprint $table) {
                if (!Schema::hasColumn('yz_goods_option', 'volume')) {
                    $table->decimal('volume',14,3)->nullable()->comment("体积");
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
