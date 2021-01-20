<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnToYzCategory extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('yz_category')) {
            Schema::table('yz_category', function (Blueprint $table) {
                if (!Schema::hasColumn('yz_category', 'small_adv_url')) {
                    $table->string('small_adv_url')->nullable()->comment('小程序链接');
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
