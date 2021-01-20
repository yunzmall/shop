<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnToYzSlide extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('yz_slide')) {
            Schema::table('yz_slide', function (Blueprint $table) {
                if (!Schema::hasColumn('yz_slide', 'small_link')) {
                    $table->string('small_link')->nullable()->comment('小程序链接');
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
