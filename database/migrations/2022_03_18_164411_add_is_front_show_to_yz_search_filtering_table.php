<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsFrontShowToYzSearchFilteringTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('yz_search_filtering')) {
            Schema::table('yz_search_filtering', function (Blueprint $table) {
                if (!Schema::hasColumn('yz_search_filtering', 'is_front_show')) {
                    $table->tinyInteger('is_front_show')->default(1)->comment('前端是否显示');
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
