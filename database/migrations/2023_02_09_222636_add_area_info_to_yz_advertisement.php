<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAreaInfoToYzAdvertisement extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('yz_advertisement')) {
            if (!Schema::hasColumn('yz_advertisement', 'area_open')) {
                Schema::table('yz_advertisement', function (Blueprint $table) {
                    $table->integer('area_open')->default(0)->comment('区域开启');
                });
            }
            if (!Schema::hasColumn('yz_advertisement', 'longitude')) {
                Schema::table('yz_advertisement', function (Blueprint $table) {
                    $table->string('longitude', 15)->default('');
                    $table->string('latitude', 15)->default('');
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

    }
}
