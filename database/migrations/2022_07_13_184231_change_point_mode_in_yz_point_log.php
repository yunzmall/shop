<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangePointModeInYzPointLog extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('yz_point_log')) {
            Schema::table('yz_point_log', function (Blueprint $table) {
                if (Schema::hasColumn('yz_point_log', 'point_mode')) {
                    $table->integer('point_mode')->change();
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
        Schema::table('yz_point_log', function (Blueprint $table) {
            //
        });
    }
}
