<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateYzGoodsOptionTitleLengthTable extends Migration
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
                if (Schema::hasColumn('yz_goods_option', 'title')) {
                    $table->string('title',255)->default('')->change();
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
