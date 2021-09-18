<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDisplayNameToLimitbuy extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('yz_goods_limitbuy')) {
            Schema::table('yz_goods_limitbuy', function (Blueprint $table) {
                if (!Schema::hasColumn('yz_goods_limitbuy','display_name')) {
                    $table->string('display_name')->default("限时购")->comment('自定义前端命名');
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
