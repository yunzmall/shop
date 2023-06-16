<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIsHideColumnsToYzGoods extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('yz_goods')) {
            Schema::table('yz_goods', function (Blueprint $table) {
                if (!Schema::hasColumn('yz_goods', 'is_hide')) {
                    $table->boolean('is_hide')->default(1)->comment('是否隐藏:1显示，2隐藏');
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
